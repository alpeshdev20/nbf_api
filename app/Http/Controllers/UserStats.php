<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\HasReads;
use App\Models\RawReadLogs;
use Illuminate\Http\Request;
use App\Models\BookStatistics;
use App\Models\UserStatistics;
use App\Models\AppBooksAnalytics;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponseHandler;
use App\Models\UserMonthlyReadTime;
use Illuminate\Support\Facades\Validator;

class UserStats extends Controller
{

    //* Check is Book Read
    function is_book_read($slug)
    {
        try {
        //     //* Validating request
            $validator = Validator::make(['slug' => $slug], [
                'slug' =>  [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                            
                            if (!Books::where('slug', $slug)->exists()) {
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
            $getBook = Books::where('slug', $slug)->first();

            $book_id = '';
            if ($getBook) {
                $book_id = $getBook->id;
            } else {
                // Handle the case where the book is not found
                return ApiResponseHandler::error($validator->messages(), 404);
            }

            //* Checking if user reads the book
            $isRead = HasReads::where('user_id', $user->id)->where('book_id', $book_id)->first();
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
                            if (!Books::where('slug', $value)->exists()) {
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

            $getBook = Books::where('slug', trim($request->input('id')))->first();

            $book_id = '';
            if ($getBook) {
                $book_id = $getBook->id;
            } else {
                // Handle the case where the book is not found
                return ApiResponseHandler::error($validator->messages(), 404);
            }

            $user = auth('customers')->user();

            //* Checking if user reads the book
            $isRead = HasReads::where('user_id', $user->id)->where('book_id', $book_id)->first();
            if ($isRead === null) {
                //* Saving Book Info
                $addReadStats = new HasReads();
                $addReadStats->user_id = $user->id;
                $addReadStats->book_id = $book_id;
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
                            // $decryptedId = decrypt($value);
                            if (!Books::where('slug', $value)->exists()) {
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

            $getBook = Books::where('slug', trim($request->input('id')))->first();

            $book_id = '';
            if ($getBook) {
                $book_id = $getBook->id;
            } else {
                // Handle the case where the book is not found
                return ApiResponseHandler::error($validator->messages(), 404);
            }
            $user = auth('customers')->user();
            // $book_id = e(trim($request->input('id')));
            $read_time_milliseconds = e(trim($request->input('read_time')));
            $read_time_milliseconds = (int)$read_time_milliseconds;

            // Convert milliseconds to seconds
            $read_time_seconds = $read_time_milliseconds / 1000;

            // Optionally, round or format the result
            $read_time_seconds = round($read_time_seconds); // Rounds to the nearest

            $read_time = $read_time_seconds;

            //* Transaction Start
            DB::beginTransaction();


            //! For App book analytics
             $bookAnalytics = AppBooksAnalytics::where("user_id", $user->id)->where("book_id", $book_id)->orderBy('id', "DESC")->first();
             if ($bookAnalytics === null) {
                $appStats = new AppBooksAnalytics();

                $appStats->user_id = $user->id;
                $appStats->book_id = $book_id;
                $appStats->read_time = $read_time;
                // Explicitly set the created_at field if needed
                $appStats->created_at = now(); // or use a specific timestamp if required

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
            $rawReadLog->created_at = now(); // or use a specific timestamp if required

            $rawReadLog->save();


            //! For User Statics
            // $userStatistics = UserStatistics::where("user_id", $user->id)->orderBy('id', "DESC")->first();
            // if ($userStatistics === null) {
                $userStats = new UserStatistics();

                $userStats->user_id = $user->id;
                $userStats->read_time = $read_time;
                // Explicitly set the created_at field if needed
                $userStats->created_at = now(); // or use a specific timestamp if required

                $userStats->save();
            // } else {
            //     $userStatistics->read_time = (int)$userStatistics->read_time +  $read_time;
            //     $userStatistics->save();
            // }

            // $currentYear = date('Y');  // Get the current year
            // $currentMonth = date('m'); // Get the current month

            // // Check if there is already a record for this user, book, year, and month
            // $monthlyReadTime = UserMonthlyReadTime::where('user_id', $user->id)
            // ->where('book_id', $book_id)
            // ->where('year', $currentYear)
            // ->where('month', $currentMonth)
            // ->first();

            // if ($monthlyReadTime) {
            //     // Update the existing record by adding the new read time
            //     $monthlyReadTime->read_time += $read_time_seconds;
            //     $monthlyReadTime->save();
            // } else {
            //     // Create a new record for this month
            //     UserMonthlyReadTime::create([
            //         'user_id' => $user->id,
            //         'book_id' => $book_id,
            //         'year' => $currentYear,
            //         'month' => $currentMonth,
            //         'read_time' => $read_time_seconds
            //     ]);
            // }

            //! For Book Stats
            // $bookStatistics = BookStatistics::where("book_id", $book_id)->orderBy('id', "DESC")->first();
            // if ($bookStatistics === null) {
                $bookStats = new BookStatistics();
                $bookStats->book_id = $book_id;
                $bookStats->read_time = $read_time;
                // Explicitly set the created_at field if needed
                $bookStats->created_at = now(); // or use a specific timestamp if required

                $bookStats->save();
            // } else {
            //     $bookStatistics->read_time = (int)$bookStatistics->read_time +  $read_time;
            //     $bookStatistics->save();
            // }



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
