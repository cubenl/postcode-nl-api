<?php

namespace Cubenl\PostcodeNL\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Cubenl\PostcodeNL\Models\Address;
use Cubenl\PostcodeNL\PostcodeNL;
use Cubenl\PostcodeNL\PostcodeNLServiceProvider;
use Cubenl\PostcodeNL\Rules\ValidPostcode;

class PostcodeNLTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            PostcodeNLServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('postcode-nl.api_key', 'fake-api-key');
        Config::set('postcode-nl.secret_key', 'fake-secret-key');
        Config::set('postcode-nl.base_url', 'https://api.postcode.nl');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_it_fetches_from_api_and_caches_result()
    {
        $responseData = json_decode(file_get_contents(__DIR__.'/Fixtures/nl-response.json'), true);

        Http::fake([
            'https://api.postcode.nl/*' => Http::response($responseData, 200),
        ]);

        $lookup = app('postcode-nl:service')->lookup('7571CD', '2');

        $this->assertNotNull($lookup);
        $this->assertEquals('Ganzenmarkt', $lookup->street);
        $this->assertEquals('Oldenzaal', $lookup->city);
        $this->assertEquals(52.31210316, $lookup->latitude);
        $this->assertEquals(['office'], $lookup->purposes);

        $this->assertDatabaseHas('addresses', [
            'postcode' => '7571CD',
            'house_number' => 2,
            'street' => 'Ganzenmarkt',
            'city' => 'Oldenzaal',
            'province' => 'Overijssel',
            'latitude' => 52.31210316,
            'longitude' => 6.92925934,
            'bag_number_designation_id' => '0173200000175988',
            'bag_addressable_object_id' => '0173010000175989',
            'address_type' => 'building',
            'surface_area' => 598,
        ]);
    }

    public function test_it_returns_null_on_invalid_addition()
    {
        Http::fake([
            'https://api.postcode.nl/*' => Http::response([
                'houseNumberAddition' => null,
            ], 200),
        ]);

        $result = app('postcode-nl:service')->lookup('7571CD', '2A');

        $this->assertNull($result);
    }

    public function test_validation_rule_passes()
    {
        Http::fake([
            'https://api.postcode.nl/*' => Http::response(json_decode(file_get_contents(__DIR__.'/Fixtures/nl-response.json'), true), 200),
        ]);

        $validator = Validator::make([
            'postal_code' => '7571CD'
        ], [
            'postal_code' => [new ValidPostcode('2')],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_rule_fails()
    {
        Http::fake([
            'https://api.postcode.nl/*' => Http::response([], 404),
        ]);

        $validator = Validator::make([
            'postal_code' => '9999ZZ'
        ], [
            'postal_code' => [new ValidPostcode('999')],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            ['postal_code' => [__('postcode-nl::messages.no_address_found')]],
            $validator->errors()->toArray()
        );
    }

    public function test_it_throws_exception_when_credentials_missing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key and secret key must be set in the configuration.');

        config(['postcode-nl.api_key' => null, 'postcode-nl.secret_key' => null]);

        app('postcode-nl:service')->lookup('7571CD', '2');
    }

    public function test_it_returns_null_on_api_failure()
    {
        Http::fake([
            'https://api.postcode.nl/*' => Http::response([], 500),
        ]);

        $result = app('postcode-nl:service')->lookup('7571CD', '2');

        $this->assertNull($result);
        $this->assertDatabaseMissing('addresses', ['postcode' => '7571CD', 'house_number' => 2]);
    }

    public function test_it_parses_house_number_with_hyphenated_addition()
    {
        Http::fake([
            'https://api.postcode.nl/*' => Http::response([
                'street' => 'Hoofdstraat',
                'houseNumber' => 12,
                'houseNumberAddition' => '-A',
                'postcode' => '1234AB',
                'city' => 'Amsterdam',
                'province' => 'Noord-Holland'
            ], 200),
        ]);

        $result = app('postcode-nl:service')->lookup('1234AB', '12-A');

        $this->assertNotNull($result);
        $this->assertEquals('-A', $result->resource['house_number_addition']);
    }

    public function test_it_throws_on_completely_invalid_house_number()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid house number format');

        app('postcode-nl:service')->lookup('1234AB', 'foo!');
    }

    public function test_it_splits_house_number_correctly()
    {
        $this->assertEquals([12, null], PostcodeNL::splitHouseNumber('12'));
        $this->assertEquals([12, 'A'], PostcodeNL::splitHouseNumber('12A'));
        $this->assertEquals([12, '-B'], PostcodeNL::splitHouseNumber('12-B'));
        $this->assertEquals([30, null], PostcodeNL::splitHouseNumber(30));
        $this->assertEquals([45, '2'], PostcodeNL::splitHouseNumber('45 2'));
    }

    public function test_it_throws_for_invalid_house_number()
    {
        $this->expectException(\InvalidArgumentException::class);
        PostcodeNL::splitHouseNumber('invalid!');
    }

    public function test_it_encodes_postcode_with_space()
    {
        Http::fake(function ($request) {
            $expectedUrl = 'https://api.postcode.nl/nl/v1/addresses/postcode/7571%20CD/2';

            $this->assertEquals($expectedUrl, (string) $request->url());

            return Http::response([
                'street' => 'Teststraat',
                'houseNumber' => 2,
                'houseNumberAddition' => null,
                'postcode' => '7571 CD',
                'city' => 'Oldenzaal',
                'province' => 'Overijssel'
            ], 200);
        });

        $result = app('postcode-nl:service')->lookup('7571 CD', 2);

        $this->assertNotNull($result);
        $this->assertEquals('Teststraat', $result->street);
        $this->assertEquals('Oldenzaal', $result->city);
    }

}
