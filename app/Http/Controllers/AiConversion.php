<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\AiConversionLog;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AiConversion extends Controller
{


    //* Resource without conversion
    function resouces_without_conversion($limit)
    {
        try {

            //* Validating response
            $validator = Validator::make(['limit' => $limit], [
                'limit' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $resources = Books::select('id', 'book_name', 'book_image', 'book_pdf')->where('ai_consversion_status', "No")
                ->limit($limit)
                ->get();

            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->id = $data->id;
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_url =  $data->book_pdf ? env('RESOURCES_URL') . $data->book_pdf : "";
                    unset($data->book_name);
                    unset($data->book_pdf);
                    $finalResponse[] = $data;
                }
            }

            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }



    //* Marked as Converted
    function marked_resource_as_converted(Request $request)
    {
        try {

            $validator = Validator::make($request->json()->all(), [
                'id' => 'required|numeric|exists:books,id',
            ]);


            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            //* Transaction Start
            DB::beginTransaction();


            //* Update Resource conversion status
            $bookInfo = Books::find($request->input('id'));
            if ($bookInfo === null) {
                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
            }

            $bookInfo->ai_consversion_status = "Yes";
            $bookInfo->save();


            //* Creating Log of converion
            $aiConversion = new AiConversionLog();
            $aiConversion->book_id = $request->input('id');

            $aiConversion->save();



            //* Commiting the transaction
            DB::commit();

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            //* Rollbacking the transaction
            DB::rollback();
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
