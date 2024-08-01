<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\Customers;
use App\Models\FeedbacksModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Feedbacks extends Controller
{


    //* Submit Feedback
    function feedback(Request $request)
    {

        try {
            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'id' =>  [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                            $decryptedId = decrypt($value);
                            if (!Customers::where('id', $decryptedId)->exists()) {
                                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                            }
                        } catch (\Exception $e) {
                            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                        }
                    },
                ],
                'feedback' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }



            //* Saving Feedback
            $feedback = new FeedbacksModel();

            $feedback->user_id = decrypt(e(trim($request->input('id'))));
            $feedback->feedback =  e(trim($request->input('feedback')));

            $feedback->save();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
