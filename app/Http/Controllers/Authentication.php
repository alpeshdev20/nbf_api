<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customers;
use App\Models\OtpMaster;
use Illuminate\Http\Request;
use App\Models\UsersResources;
use App\Models\PreRegistrations;
use App\Mail\OtpForCreateAccount;
use App\Mail\OtpForForgotPassword;
use App\Models\RegistrationTokens;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponseHandler;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use App\Models\UsersActiveSubscriptions;
use Illuminate\Support\Facades\Validator;



class Authentication extends Controller
{
    //* Create User Account
    function create_user_account(Request $request)
    {
        try {

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'name' => 'required|string|between:3,255',
                'email' => 'required|email:rfc,dns|unique:u_logins,email',
                'mobile' => 'required|numeric|digits:10|unique:u_logins,mobile',
                'password' => 'required|string|min:8',
                'dob' => 'required|date',
                'city' => 'required|string',
                'gender' => 'required|in:Male,Female,Others',
                'segment' => 'required|in:K12/School,Higher Education',
                'class' => 'required_if:segment,K12/School|nullable|numeric|exists:class_master,id',
                'personal_address' => 'required|string',
                'institute_address' => 'required|string',
                'registration_type' => 'required|in:0,3',
                'registration_token' => 'required_if:registration_type,3',
            ], [
                "registration_token.required_if" => "Registration token is required",
                'registration_type.in' => 'User Type is either Indiviual User or Instituional User',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            //* Checking Registraion TOken is valid or not
            // if ((int)e(trim($request->input('registration_type'))) === 3) {
            //     $isValidToken = RegistrationTokens::where('token', e(trim($request->input('registration_token'))))->where('active', 1)->first();
            //     if ($isValidToken === null) {
            //         return ApiResponseHandler::error("INVALID_TOKEN", 500);
            //     }
            // }

            //* Generating OTP
            $otp = rand(100000, 999999);

            //* Pre Regitering User
            $pre_register = new PreRegistrations();

            $pre_register->name = e(trim($request->input('name')));
            $pre_register->email = e(trim($request->input('email')));
            $pre_register->mobile = e(trim($request->input('mobile')));
            $pre_register->password = bcrypt(e(trim($request->input('password'))));
            $pre_register->city = e(trim($request->input('city')));
            $pre_register->birthday = e(trim($request->input('dob')));
            $pre_register->gender = e(trim($request->input('gender')));
            $pre_register->preferred_segment = e(trim($request->input('segment')));
            $pre_register->class = $request->has('class') && trim($request->input('class')) !== '' ? e(trim($request->input('class'))) : null;
            $pre_register->personal_address = e(trim($request->input('personal_address')));
            $pre_register->institute_address = e(trim($request->input('institute_address')));
            $pre_register->otp = $otp;

            $pre_register->registration_type = e(trim($request->input('registration_type')));
            $pre_register->registration_token = (int)e(trim($request->input('registration_type'))) === 3 ? e(trim($request->input('registration_token'))) : null;

            $pre_register->save();


            //* Sending Email
            Mail::mailer("support")->to($request->input('email'))->send(new OtpForCreateAccount($otp));

            //* Encrypting User id
            $encryptId = encrypt($pre_register->id);

            return ApiResponseHandler::successWithData(['id' => $encryptId], "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Verify user otp
    function verify_user_otp(Request $request)
    {
        try {

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'otp' => 'required|numeric',
                'id' =>  [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                            $decryptedId = decrypt($value);
                            if (!PreRegistrations::where('id', $decryptedId)->exists()) {
                                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                            }
                        } catch (\Exception $e) {
                            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                        }
                    },
                ],
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            //* Getting User Info
            $userInfo = PreRegistrations::find(decrypt($request->input('id')));
            if ($userInfo === null) {
                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
            }

            //* Checking Account is already created with same id
            if ($userInfo->is_account_created === "Yes") {
                return ApiResponseHandler::error("ACCOUNT_ALREADY_CREATED", 400);
            }


            //* Checking OTP
            if (((int)e(trim($request->input('otp'))) !== $userInfo->otp) ||  ((Carbon::parse($userInfo->created_at)->addMinutes(5))->lt(Carbon::now()))) {
                return ApiResponseHandler::error("INVALID_OTP", 401);
            }

            //* Transaction Start
            DB::beginTransaction();


            //* Registrating user to main account
            $user = new Customers();

            $user->name = $userInfo->name;
            $user->email = $userInfo->email;
            $user->password = $userInfo->password;
            $user->mobile = $userInfo->mobile;
            $user->birthday = $userInfo->birthday;
            $user->gender = $userInfo->gender;
            $user->preferred_segment = $userInfo->preferred_segment;
            $user->class = $userInfo->class;
            $user->city = $userInfo->city;
            $user->personal_address = $userInfo->personal_address;
            $user->institute_address = $userInfo->institute_address;
            $user->type ="App User";    
            $user->registration_type = $userInfo->registration_type;
            $user->registration_token = $userInfo->registration_token;


            $user->save();


            //create UsersResources
            $signUp = new UsersResources();
            $signUp->resource_type = 'Signup Page';
            $signUp->name = $userInfo->name;
            $signUp->email_address = $userInfo->email;
            // $signUp->password = $userInfo->password;
            $signUp->mobile_number = $userInfo->mobile;
            $signUp->birth_date = $userInfo->birthday;
            $signUp->gender = $userInfo->gender;
            $signUp->preferred_segment = $userInfo->preferred_segment;
            $signUp->class = $userInfo->class;
            $signUp->personal_address = $userInfo->personal_address;
            $signUp->institution_address = $userInfo->institute_address;
            $signUp->save();

            //* Markingid as created accoungt
            $userInfo->is_account_created = "Yes";
            $userInfo->save();

            //* Getting Plan Info
            $planInfo = getSubscribtionPlanInfo(1);

            //* Activating User Free Plan
            $subscribePlan = new UsersActiveSubscriptions();

            $subscribePlan->plan_name = $planInfo->name;
            $subscribePlan->plan_end_date = Carbon::now()->addDays($planInfo->validity);
            $subscribePlan->user_id = $user->id;
            $subscribePlan->subscription_id = $planInfo->id;
            $subscribePlan->auto_renew = 0;
            $subscribePlan->status = 1;

            $subscribePlan->save();


            //* Commiting the transaction
            DB::commit();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            //* Rollbacking the transaction
            DB::rollback();
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Authenticate user
    function authenticate_user(Request $request)
    {
        try {

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'email' => 'required|email|exists:u_logins,email',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            //* User info
            $user = Customers::where('email', e(trim($request->input('email'))))->first();
            if ($user === null) {
                return ApiResponseHandler::error("UNAUTHORIZED_USER", 401);
            }

            //check user is active or not 
            if ($user->is_active != 1) {
                return ApiResponseHandler::error("You are an inactive user. Please contact support.", 403);
            }
            else{
                // do nothing as of now
            }

            if (!Hash::check(e(trim($request->input('password'))), $user->password)) {
                return ApiResponseHandler::error("INVALID_CREDENTIALS", 400);
            }

            $credentials = $request->only('email', 'password');

            $token = auth('customers')->setTTL(60 * 24 * 7)->attempt($credentials);

            if (!$token) {
                return ApiResponseHandler::error("UNAUTHORIZED_USER", 401);
            }

            $user = auth('customers')->user();

            //* Creating user info
            $userInfo = [
                'user_id' =>   encrypt($user->id),
                'name' =>   $user->name,
                "token" => $token
            ];

            // return ApiResponseHandler::successWithData($userInfo, "success", 200);

            $cookie = Cookie::make('session', $token, 60 * 24 * 7)
                ->withSecure(env('COOKIE_SECURE'))
                ->withHttpOnly(true)
                ->withSameSite('Lax')
                ->withDomain(env('COOKIE_DOMAIN'));


            return response()->json([
                'status' => 200,
                'message' => "success",
                'response' => $userInfo
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Forgot password
    function forgot_password(Request $request)
    {
        try {

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'email' => 'required|email|exists:u_logins,email',
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            //* Transaction Start
            DB::beginTransaction();

            //* User info
            $user = Customers::where('email', e(trim($request->input('email'))))->first();
            if ($user === null) {
                return ApiResponseHandler::error("UNAUTHORIZED_USER", 401);
            }


            //* checking if previous otp is active
            $isExists = OtpMaster::where('user_type', 'User')->where('otp_for', 'Forgot Password')->where('email', e(trim($request->input('email'))))->where('status', 'Active')->first();

            //* If Exists deactivating the status
            if ($isExists) {
                $isExists->status = "Inactive";
                $isExists->save();
            }


            //* Generating the new otp
            $otp = new OtpMaster();

            $generated_otp = rand(10000, 99999);

            $otp->user_type = "User";
            $otp->otp_for = "Forgot Password";
            $otp->email = e(trim($request->input('email')));
            $otp->otp = $generated_otp;

            $otp->save();


            //* Sending Email
            Mail::mailer("support")->to(e(trim($request->input('email'))))->send(new OtpForForgotPassword($generated_otp));


            //* Commiting the transaction
            DB::commit();

            //* Encrypting User id
            $encryptId = encrypt($otp->id);

            return ApiResponseHandler::successWithData(['id' => $encryptId], "success", 200);
        } catch (\Exception $e) {
            //* Rollbacking the transaction
            DB::rollback();
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Verify user otp
    function verify_forgot_password_otp(Request $request)
    {
        try {

            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'otp' => 'required|numeric',
                'id' =>  [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                            $decryptedId = decrypt($value);
                            if (!PreRegistrations::where('id', $decryptedId)->exists()) {
                                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                            }
                        } catch (\Exception $e) {
                            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                        }
                    },
                ],
            ]);
            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            //* Getting OTP Info
            $otpInfo = OtpMaster::find(decrypt($request->input('id')));
            if ($otpInfo === null) {
                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
            }

            //* Checking OTP is active or not
            if ($otpInfo->status === "Inactive") {
                return ApiResponseHandler::error("INVALID_OTP", 401);
            }


            //* Checking OTP
            if (((int)e(trim($request->input('otp'))) !== $otpInfo->otp) ||  ((Carbon::parse($otpInfo->created_at)->addMinutes(5))->lt(Carbon::now()))) {
                return ApiResponseHandler::error("INVALID_OTP", 401);
            }

            //* Changing OTP Status
            $otpInfo->status = "Inactive";
            $otpInfo->save();



            //* Removing unwanted key
            unset($otpInfo->id);
            unset($otpInfo->otp);
            unset($otpInfo->created_at);
            unset($otpInfo->status);
            unset($otpInfo->updated_at);
            unset($otpInfo->otp_for);


            return ApiResponseHandler::successWithData($otpInfo, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }




    //* Change Password
    function change_password(Request $request)
    {
        try {

            $validator = Validator::make($request->json()->all(), [
                'email' => 'required|email|exists:u_logins,email',
                'password' => 'required|string|min:8|confirmed',
            ]);


            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            //* user info
            $user = Customers::where('email', e(trim($request->input('email'))))->first();
            if ($user === null) {
                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
            }

            //* updating Password
            $user->password = bcrypt(e(trim($request->input('password'))));
            $user->save();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //! Class Ends
}
