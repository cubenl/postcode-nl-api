<?php


namespace Cubenl\PostcodeNL\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Cubenl\PostcodeNL\Http\Requests\LookupAddressRequest;

class PostcodeNLController extends Controller
{

    public function autocompleteAddress(LookupAddressRequest $request): JsonResponse
    {
        $lookUp = app('postcode-nl:service')->lookup($request->get('zipCode'), $request->get('houseNumber'));

        if (!$lookUp) {
            return response()->json([
                'success' => false,
                'message' => __('postcode-nl::messages.no_address_found')
            ]);
        }

        return response()->json([
            'success' => true,
            'street' => $lookUp['street'],
            'city' => $lookUp['city'],
        ]);
    }
}
