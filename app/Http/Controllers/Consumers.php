<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\AppBooksAnalytics;
use App\Models\Books;
use App\Models\Customers;
use App\Models\UsersActiveSubscriptions;
use App\Models\Wishlists;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;

class Consumers extends Controller
{


    //* User Info
    function user_info(Request $request)
    {
        try {

            $user = auth('customers')->user();

            //* Getting User Info
            $userInfo = Customers::select()->find($user->id);

            $userInfo->user_id = encrypt($userInfo->id);

            unset($userInfo->id);
            unset($userInfo->created_at);
            unset($userInfo->updated_at);
            unset($userInfo->registration_type);
            unset($userInfo->registration_token);


            //* get user Plan info

            $activePlan = UsersActiveSubscriptions::where('user_id', $user->id)->where('status', 1)->first();
            if ($activePlan === null) {
                $userInfo->plan = "";
                $userInfo->remaning_days = 0;
            } else {
                $currentDateTime = Carbon::now();
                $targetDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $activePlan->plan_end_date)->endOfDay();
                $daysLeft = $currentDateTime->diffInDays($targetDateTime);

                if ($daysLeft === 0) {
                    $activePlan->status = 0;
                    $activePlan->save();
                    $userInfo->plan = "";
                    $userInfo->remaning_days = 0;
                } else {
                    $userInfo->plan = $activePlan->plan_name;
                    $userInfo->remaning_days = $daysLeft;
                }
            }

            return ApiResponseHandler::successWithData($userInfo, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Update Profile
    function update_profile(Request $request)
    {
        try {

            $user = auth('customers')->user();

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'name' => 'required|string|between:3,255',
                'mobile' => 'required|numeric|digits:10|unique:u_logins,mobile,' . $user->id,
                'dob' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'personal_address' => 'required|string',
                'institute_address' => 'required|string',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }



            //* Getting User Info
            $userInfo = Customers::select()->find($user->id);

            $userInfo->name = e(trim($request->input('name')));
            $userInfo->mobile = e(trim($request->input('mobile')));
            $userInfo->birthday = e(trim($request->input('dob')));
            $userInfo->gender = e(trim($request->input('gender')));
            $userInfo->personal_address = e(trim($request->input('personal_address')));
            $userInfo->institute_address = e(trim($request->input('institute_address')));

            $userInfo->save();



            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Verify Session
    function verify_session()
    {
        try {
            auth('customers')->user();
            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Signout
    function sign_out()
    {
        try {
            auth('customers')->logout();


            //$cookie = Cookie::make('session', "", 0)
               // ->withSecure(env('COOKIE_SECURE'))
                //->withHttpOnly(true)
                //->withSameSite('Lax')
                //->withDomain(env('COOKIE_DOMAIN'));
                     $cookie = Cookie::forget('session') 
            ->withSecure(env('COOKIE_SECURE'))
            ->withHttpOnly(true)
            ->withSameSite('Lax')
            ->withDomain(env('COOKIE_DOMAIN'))
            ->withPath('/'); 

            return response()->json([
                'status' => 200,
                'message' => "success",
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }



    //* Change Password
    function change_password(Request $request)
    {
        try {

            $user = auth('customers')->user();

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'old_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }



            //* Getting User Info
            $userInfo = Customers::find($user->id);

            if ($user === null) {
                return ApiResponseHandler::error("UNAUTHORIZED_USER", 401);
            }

            if (!Hash::check(e(trim($request->input('old_password'))), $user->password)) {
                return ApiResponseHandler::error("INVALID_CREDENTIALS", 400);
            }

            $userInfo->password = bcrypt(e(trim($request->input('password'))));
            $userInfo->save();



            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }



    //* Wishlists
    function wishlists()
    {
        try {

            $user = auth('customers')->user();


            //* Users Wishlists
            $wishlists = Wishlists::select('book_id', 'user_id')->where('user_id', $user->id)->where('status', 'Active')->get();

            if ($wishlists->isEmpty()) {
                return ApiResponseHandler::successWithData([], "success", 200);
            }


            $finalResponse = [];

            foreach ($wishlists as $resource) {
                //* Getting Book Info
                $resourceInfo = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('id', $resource->book_id)
                    ->first();




                if ($resourceInfo !== null) {
                    $resourceInfo->resource_id = encrypt((string) $resourceInfo->id);
                    $resourceInfo->resource_name = $resourceInfo->book_name;
                    $resourceInfo->material_type = getMaterialTypeInfo($resourceInfo->material_type)['material_type'] ?? "";
                    $resourceInfo->resource_image = $resourceInfo->book_image ? env('RESOURCES_URL') . $resourceInfo->book_image : "";
                    $resourceInfo->rating = (int)$resourceInfo->ratings;
                    $resourceInfo->reviews = (int)$resourceInfo->reviews;

                    unset($resourceInfo->id);
                    unset($resourceInfo->book_name);
                    unset($resourceInfo->book_image);
                    array_push($finalResponse, $resourceInfo);
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //* User Library
    function library()
    {
        try {

            $user = auth('customers')->user();


            //* Users Library
            $library = AppBooksAnalytics::select('book_id', 'user_id')->where('user_id', $user->id)->get();

            if ($library->isEmpty()) {
                return ApiResponseHandler::successWithData([], "success", 200);
            }


            $finalResponse = [];

            foreach ($library as $resource) {
                //* Getting Book Info
                $resourceInfo = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('id', $resource->book_id)
                    ->first();




                if ($resourceInfo !== null) {
                    $resourceInfo->resource_id = encrypt((string) $resourceInfo->id);
                    $resourceInfo->resource_name = $resourceInfo->book_name;
                    $resourceInfo->material_type = getMaterialTypeInfo($resourceInfo->material_type)['material_type'] ?? "";
                    $resourceInfo->resource_image = $resourceInfo->book_image ? env('RESOURCES_URL') . $resourceInfo->book_image : "";
                    $resourceInfo->rating = $resourceInfo->rating;
                    $resourceInfo->reviews = $resourceInfo->reviews;

                    unset($resourceInfo->id);
                    unset($resourceInfo->book_name);
                    unset($resourceInfo->book_image);
                    unset($resourceInfo->ratings);
                    array_push($finalResponse, $resourceInfo);
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
