<?php

namespace Cubenl\PostcodeNL;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Cubenl\PostcodeNL\Models\Address;
use Cubenl\PostcodeNL\Resources\PostcodeResource;

class PostcodeNL
{
    /**
     * Looks up an address using the provided zip code and house number (with optional addition).
     *
     * The method first checks if the address exists in the local database.
     * If not, it queries the PostcodeNL API for the address.
     *
     * If an addition is provided but the API response indicates it's invalid (i.e., `houseNumberAddition` is null),
     * the method will return null and will not save the result.
     *
     * @param string $zip_code The Dutch postcode, e.g. "1234AB".
     * @param string|int $raw_house_number The house number, possibly including an addition (e.g., "12A").
     *
     * @return PostcodeResource|null
     *         A PostcodeResource if a valid address is found, or null if not found or addition is invalid.
     *
     * @throws \InvalidArgumentException if the house number format is invalid or API credentials are missing.
     */
    public static function lookup(string $zip_code, string|int $raw_house_number): ?PostcodeResource
    {
        [$house_number, $addition] = self::splitHouseNumber($raw_house_number);

        $address = Address::where('postcode', $zip_code)
            ->where('house_number', $house_number)
            ->when($addition !== null, fn ($query) => $query->where('house_number_addition', $addition))
            ->first();

        if ($address) {
            return new PostcodeResource($address);
        }

        $api_key = config('postcode-nl.api_key');
        $secret_key = config('postcode-nl.secret_key');

        if (empty($api_key) || empty($secret_key)) {
            throw new InvalidArgumentException('API key and secret key must be set in the configuration.');
        }

        $encodedZip = rawurlencode($zip_code);
        $url = config('postcode-nl.base_url') . "/nl/v1/addresses/postcode/{$encodedZip}/{$house_number}";

        if ($addition) {
            $url .= "/$addition";
        }

        $response = Http::withBasicAuth($api_key, $secret_key)->get($url);

        if (! $response->ok()) {
            return null;
        }

        $data = $response->json();

        // House number addition used to uniquely identify a location.
        // Null if the addition wasn't found; empty string ("") if there's no addition.
        if ($addition !== null && ($data['houseNumberAddition'] ?? null) === null) {
            return null;
        }

        $address = Address::create([
            'street' => $data['street'] ?? null,
            'street_nen' => $data['streetNen'] ?? null,
            'house_number' => $data['houseNumber'] ?? $house_number,
            'house_number_addition' => $data['houseNumberAddition'] ?? null,
            'postcode' => $data['postcode'] ?? $zip_code,
            'city' => $data['city'] ?? null,
            'city_short' => $data['cityShort'] ?? null,
            'municipality' => $data['municipality'] ?? null,
            'municipality_short' => $data['municipalityShort'] ?? null,
            'province' => $data['province'] ?? null,
            'rd_x' => $data['rdX'] ?? null,
            'rd_y' => $data['rdY'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'bag_number_designation_id' => $data['bagNumberDesignationId'] ?? null,
            'bag_addressable_object_id' => $data['bagAddressableObjectId'] ?? null,
            'address_type' => $data['addressType'] ?? null,
            'purposes' => $data['purposes'] ?? [],
            'surface_area' => $data['surfaceArea'] ?? null,
            'house_number_additions' => $data['houseNumberAdditions'] ?? [],
        ]);


        return new PostcodeResource($address);
    }


    /**
     * Splits a raw house number into its numeric and addition components.
     *
     * Examples:
     * - "12A" → [12, "A"]
     * - "12-A" → [12, "-A"]
     * - "30" → [30, null]
     *
     * @param string|int $input
     * @return array{int, string|null}
     * @throws InvalidArgumentException
     */
    public static function splitHouseNumber(string|int $input): array
    {
        if (is_int($input)) {
            return [$input, null];
        }

        if (preg_match('/^(\d+)\s*([a-zA-Z0-9\-]*)$/', $input, $matches)) {
            $number = (int) $matches[1];
            $addition = trim($matches[2]) ?: null;
            return [$number, $addition];
        }

        throw new InvalidArgumentException("Invalid house number format: $input");
    }
}


