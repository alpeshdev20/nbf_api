<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Mail\PublisherRegistration;
use App\Models\InstitutionRegistrations;
use App\Models\PublishersLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class Institutions extends Controller
{



    //* Save Institute information
    function save_institute_info(Request $request)
    {
        try {


            //* Validating request
            $validator = Validator::make($request->json()->all(), [
                'institute_name' => 'required|string|between:3,255',
                'student_enrollment' => 'required|numeric',
                'contact_person' => 'required|string|between:3,255',
                'contact_person_email' => 'required|email:rfc,dns',
                'contact_person_mobile_no' => 'required|numeric|digits:10',
                'summary' => 'required|string|between:10,255',
            ]);


            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            //* Saving Information
            $register = new InstitutionRegistrations();

            $register->institute_name = e(trim($request->input('institute_name')));
            $register->student_enrollment = e(trim($request->input('student_enrollment')));
            $register->contact_person = e(trim($request->input('contact_person')));
            $register->contact_person_email = e(trim($request->input('contact_person_email')));
            $register->contact_person_mobile_no = e(trim($request->input('contact_person_mobile_no')));
            $register->summary = e(trim($request->input('summary')));
            $register->save();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //* Save Publisher Info
    function save_publisher_info(Request $request)
    {
        try {

            $request_data = $request->all();

            //* Validating response
            $validator = Validator::make($request_data, [
                'publisher_name' => 'required|string|max:150|min:3',
                'email' => 'required|email|max:100|min:8|email:rfc,dns',
                'mobile_number' => 'required|digits:10',
                'attachment' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv',
            ], [
                'attachment.mimetypes' => 'Attachment must be pdf,excel or csv.'
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $taskDetails = [
                'subject' => "New Publisher Registration",
                'name' => e(trim($request->input('publisher_name'))),
                'email' => e(trim($request->input('email'))),
                'contact_number' => e(trim($request->input('mobile_number'))),
                'attachment' => $request->file('attachment'),
            ];

            //* Sending Email
            Mail::mailer("support")->to('naval@netbookflix.com')->send(new PublisherRegistration($taskDetails));

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Publisher Info
    function publisher_info($start, $limit)
    {
        try {
            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit], [
                'start' => 'required|numeric|min:1',
                'limit' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = PublishersLogo::select('id', 'file_path', 'text_1', 'text_2', 'text_3')->limit($limit)
                ->orderBy('id', 'DESC')
                ->offset($start)->get();

            $finalResponse = [];

            if (!$query->isEmpty()) {
                foreach ($query as $data) {
                    $data->id = $data->id;
                    $data->text_1 = $data->text_1 ?? "";
                    $data->text_2 = $data->text_2 ?? "";
                    $data->text_3 = $data->text_3 ?? "";
                    $data->file_path = $data->file_path ? env('PUBLISHERS_RESOURCES_URL') . $data->file_path : "";

                    $finalResponse[] = $data;
                }
            }

            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
