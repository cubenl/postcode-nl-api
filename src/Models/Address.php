<?php

namespace Cubenl\PostcodeNL\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'street',
        'street_nen',
        'house_number',
        'house_number_addition',
        'postcode',
        'city',
        'city_short',
        'municipality',
        'municipality_short',
        'province',
        'rd_x',
        'rd_y',
        'latitude',
        'longitude',
        'bag_number_designation_id',
        'bag_addressable_object_id',
        'address_type',
        'purposes',
        'surface_area',
        'house_number_additions',
    ];

    protected $casts = [
        'purposes' => 'array',
        'house_number_additions' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'surface_area' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('postcode-nl.table_name'));
    }
}


