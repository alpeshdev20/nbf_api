<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\AppBooksAnalytics;
use App\Models\Books;
use App\Models\BookStatistics;
use App\Models\HasReads;
use App\Models\RawReadLogs;
use App\Models\UserStatistics;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStats extends Controller
{

    //* Check is Book Read
    function is_book_read($id)
    {
        try {
            //* Validating request
            $validator = Validator::make(['id' => $id], [
                'id' =>  [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                             $decryptedId = decrypt($value);
                            if (!Books::where('id', $decryptedId)->exists()) {
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


            $user = auth('customers')->user();


            //* Checking if user reads the book
            $isRead = HasReads::where('user_id', $user->id)->where('book_id', decrypt(e(trim($id))))->first();
            if ($isRead === null) {
                return ApiResponseHandler::successWithData(['read' => false, 'page_number' => "0"], "success", 200);
            } else {
                return ApiResponseHandler::successWithData(['read' => true, 'page_number' => $isRead->page_number], "success", 200);
            }
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Update Book Stats
    function update_book_read_stats(Request $request)
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
                            if (!Books::where('id', $decryptedId)->exists()) {
                                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                            }
                        } catch (\Exception $e) {
                            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                        }
                    },
                ],

                'page_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            $user = auth('customers')->user();

            //* Checking if user reads the book
            $isRead = HasReads::where('user_id', $user->id)->where('book_id', decrypt(e(trim($request->input('id')))))->first();
            if ($isRead === null) {
                //* Saving Book Info
                $addReadStats = new HasReads();
                $addReadStats->user_id = $user->id;
                $addReadStats->book_id = decrypt(e(trim($request->input('id'))));
                $addReadStats->page_number = e(trim($request->input('page_number')));

                $addReadStats->save();
            } else {
                $isRead->page_number = e(trim($request->input('page_number')));
                $isRead->save();
            }

            return ApiResponseHandler::success("success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //* Updating User and book stats
    function update_book_stats(Request $request)
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
                            if (!Books::where('id', $decryptedId)->exists()) {
                                return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                            }
                        } catch (\Exception $e) {
                            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
                        }
                    },
                ],

                'read_time' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            $user = auth('customers')->user();
            $book_id = decrypt(e(trim($request->input('id'))));
            $read_time = e(trim($request->input('read_time')));

            //* Transaction Start
            DB::beginTransaction();


            //! For App book analytics
            $bookAnalytics = AppBooksAnalytics::where("user_id", $user->id)->where("book_id", $book_id)->orderBy('id', "DESC")->first();
            if ($bookAnalytics === null) {
                $appStats = new AppBooksAnalytics();

                $appStats->user_id = $user->id;
                $appStats->book_id = $book_id;
                $appStats->read_time = $read_time;

                $appStats->save();
            } else {
                $bookAnalytics->read_time = (int)$bookAnalytics->read_time +  $read_time;
                $bookAnalytics->save();
            }


            //! For Raw read log
            $rawReadLog = new RawReadLogs();

            $rawReadLog->user_id = $user->id;
            $rawReadLog->book_id = $book_id;
            $rawReadLog->read_time = $read_time;

            $rawReadLog->save();


            //! For User Statics
            $userStatistics = UserStatistics::where("user_id", $user->id)->orderBy('id', "DESC")->first();
            if ($userStatistics === null) {
                $userStats = new UserStatistics();

                $userStats->user_id = $user->id;
                $userStats->read_time = $read_time;

                $userStats->save();
            } else {
                $userStatistics->read_time = (int)$userStatistics->read_time +  $read_time;
                $userStatistics->save();
            }


            //! For Book Stats
            $bookStatistics = BookStatistics::where("book_id", $book_id)->orderBy('id', "DESC")->first();
            if ($bookStatistics === null) {
                $bookStats = new BookStatistics();
                $bookStats->book_id = $book_id;
                $bookStats->read_time = $read_time;
                $bookStats->save();
            } else {
                $bookStatistics->read_time = (int)$bookStatistics->read_time +  $read_time;
                $bookStatistics->save();
            }



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