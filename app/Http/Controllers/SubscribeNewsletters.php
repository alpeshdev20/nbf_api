<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\Newsletters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscribeNewsletters extends Controller
{

    //* Subscribe newsletter
    function subscribe_newsletter(Request $request)
    {
        try {


            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'email' => 'required|email:rfc,dns',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            //* Check email is already subscribed
            $isSubscribed = Newsletters::where('email', e(trim($request->input('email'))))->first();

            //* if not subscribed
            if ($isSubscribed === null) {
                $subscribeNewsletter = new Newsletters();
                $subscribeNewsletter->email = e(trim($request->input('email')));
                $subscribeNewsletter->save();
                return ApiResponseHandler::success("success", 200);
            }

            //* If status is Inactive
            if ($isSubscribed->status === "Inactive") {
                $isSubscribed->status = "Active";
                $isSubscribed->save();
                return ApiResponseHandler::success("success", 200);
            }

            return ApiResponseHandler::error("ALREADY_SUBSCRIBED", 400);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
