<?php

namespace Cubenl\PostcodeNL\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostcodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'street' => $this->street,
            'street_nen' => $this->street_nen,
            'house_number' => $this->house_number,
            'house_number_addition' => $this->house_number_addition,
            'postcode' => $this->postcode,
            'city' => $this->city,
            'city_short' => $this->city_short,
            'municipality' => $this->municipality,
            'municipality_short' => $this->municipality_short,
            'province' => $this->province,
            'rd_x' => $this->rd_x,
            'rd_y' => $this->rd_y,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bag_number_designation_id' => $this->bag_number_designation_id,
            'bag_addressable_object_id' => $this->bag_addressable_object_id,
            'address_type' => $this->address_type,
            'purposes' => $this->purposes,
            'surface_area' => $this->surface_area,
            'house_number_additions' => $this->house_number_additions,
        ];
    }
}
