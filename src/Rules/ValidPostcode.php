<?php


namespace Cubenl\PostcodeNL\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;


class ValidPostcode implements ValidationRule
{

    public function __construct(public readonly string $houseNumber)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute  (string):  $fail
     * @param  mixed  $value
     * @param  Closure  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lookUp =   app('postcode-nl:service')->lookup($value, $this->houseNumber);

        if(!$lookUp){
            $fail(__('postcode-nl::messages.no_address_found'));
        }
    }
}
