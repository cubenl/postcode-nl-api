<?php
return [
    'base_url' => 'https://api.postcode.eu',
    'api_key' => env('POSTCODEAPI_API_KEY'),
    'secret_key' => env('POSTCODEAPI_SECRET_KEY'),
    'table_name' => env('POSTCODEAPI_TABLE_NAME', 'addresses'),
];
