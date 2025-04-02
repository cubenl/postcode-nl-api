# Postcode.nl Laravel Package

A Laravel package that integrates with the [Postcode.nl API](https://www.postcode.nl/en/address-api) to look up Dutch addresses based on postcode and house number. This package provides API routing, local caching in your database, and structured responses using Laravel resources.

This package supports Laravel 10 and 11. Tested with PHPUnit 10 and PHP 8.2+.
## 📦 Installation


Add the following to your project's `composer.json`.

```
{
    "repositories": [{
        "type": "composer",
        "url": "https://satis.cube.nl"
    }]
}
```
and
```
"require": {
    "cubenl/postcode-nl": "dev-main",
}    
```

## ⚙️ Configuration

Publish the config and translation files:

```bash
php artisan vendor:publish --tag=postcode-nl-config
php artisan vendor:publish --tag=postcode-nl-translations
php artisan vendor:publish --tag=postcode-nl-translationsmigrations
```

This will create:

- config/postcode-nl.php
- resources/lang/vendor/postcode-nl/en/messages.php
- databases/migrations/2025_03_06_152822_create_addresses_table.php


### .env Setup

Add your Postcode.nl API credentials:

````php
POSTCODEAPI_API_KEY=your-api-key #The api key
POSTCODEAPI_SECRET_KEY=your-secret-key #The api secret key
POSTCODEAPI_TABLE_NAME=your-secret-key #The table name used by the package default addresses
````

## 🔁 Routes

The following route is registered automatically:

```php
GET /api/address/autocomplete?zipCode=1234AB&houseNumber=12A
```
Response (if found):

````json
{
  "success": true,
  "street": "Hoofdstraat",
  "city": "Amsterdam"
}
````

If not found an error is thrown:

```json
{
  "success": false,
  "message": "No address was found with this zip code and house number." // translated string
}
```

## 🧠 Usage in Code

You can call the lookup service directly:

````php
use Cubenl\PostcodeNL\PostcodeNL;

$address = PostcodeNL::lookup('1234AB', '12A');

if ($address) {
    echo $address->street;
}
````

Returns a PostcodeResource or null if not found.

##  📝 Validation Rule Example
You can add the ValidPostcode validation rule in a request, for example:
```php
<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Cubenl\PostcodeNL\Rules\ValidPostcode;

class ExampleRequest extends FormRequest
{
    // ... (other request code) ...

    public function rules(): array
    {
        return [
            // ... (other rules) ...
            'postal_code' => [new ValidPostcode($this->input('house_number'))], //$value is the postal_code
            // ... (other rules) ...
        ];
    }
}
```

## 🏠 House Number Parsing Explained

The lookup method supports house numbers with or without additions. For example:

- "12" → house number 12, no addition
- "12A" → house number 12, addition "A"
- "12-A" → house number 12, addition "-A"


If the format is invalid, an InvalidArgumentException will be thrown.

## 🗃️ Address Caching

When a valid address is retrieved, it will be stored locally in the `addresses or custom configured name` table. If the address already exists in the local DB, the API will not be called again.



## ✅ Running Tests

To run the test suite:

```bash
composer install
./vendor/bin/phpunit
```

## 🤝 Contributing

If you’d like to improve this package, feel free to fork it and submit a pull request. Please include tests for new features.
