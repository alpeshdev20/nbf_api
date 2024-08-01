<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\Queries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HelpCenter extends Controller
{


    //* Add Query
    function add_query(Request $request)
    {

        try {
            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'email' => 'required|email:rfc,dns',
                'query' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }



            //* Saving Query
            $query = new Queries();

            $query->email = e(trim($request->input('email')));
            $query->query =  e(trim($request->input('query')));

            $query->save();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //! Class Ends
}
