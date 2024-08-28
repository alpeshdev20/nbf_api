<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\AppMaterialItem;
use App\Models\Books;
use App\Models\SubscriptionPlans;
use App\Models\UsersActiveSubscriptions;
use App\Models\Wishlists;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class Resources extends Controller
{

    //* Books
    function get_books($start, $limit, $genre, $age)
    {

        //* Validating response
        $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
            'start' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:1',
            'genre' => 'required|numeric|min:0',
            'age' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponseHandler::error($validator->messages(), 400);
        }

        $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 5);


        if ($age != 0) {
            $query->where('age', (int)$age);
        }

        if ($genre != 0) {
            $query->where('genre_id', (int)$genre);
        }


        $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();

        $finalResponse = [];

        if (!$resources->isEmpty()) {
            foreach ($resources as $data) {
                $data->resource_id = encrypt((string) $data->id);
                $data->resource_name = $data->book_name;
                $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                $data->rating = (int)$data->rating;
                $data->reviews = (int)$data->reviews;

                unset($data->id);
                unset($data->book_name);
                unset($data->book_image);
                $finalResponse[] = $data;
            }
        }

        return ApiResponseHandler::successWithData($finalResponse, "success", 200);
    }




    //* Audio Books
    function get_audio_books($start, $limit, $genre, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
                'start' => 'required|numeric|min:0',
                'limit' => 'required|numeric|min:1',
                'genre' => 'required|numeric|min:0',
                'age' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 4);


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($genre != 0) {
                $query->where('genre_id', (int)$genre);
            }

            $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();

            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }

            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Videos
    function get_videos($start, $limit, $genre, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
                'start' => 'required|numeric|min:0',
                'limit' => 'required|numeric|min:1',
                'genre' => 'required|numeric|min:0',
                'age' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 2);


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($genre != 0) {
                $query->where('genre_id', (int)$genre);
            }

            $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();

            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }

            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Class Notes
    function get_class_notes($start, $limit, $genre, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
                'start' => 'required|numeric|min:0',
                'limit' => 'required|numeric|min:1',
                'genre' => 'required|numeric|min:0',
                'age' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 3);


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($genre != 0) {
                $query->where('genre_id', (int)$genre);
            }

            $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();


            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* AR/VR
    function get_ar_vr($start, $limit, $genre, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
                'start' => 'required|numeric|min:0',
                'limit' => 'required|numeric|min:1',
                'genre' => 'required|numeric|min:0',
                'age' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }


            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 5);


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($genre != 0) {
                $query->where('genre_id', (int)$genre);
            }

            $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();

            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Gamified Learning
    function get_gamified_learning($start, $limit, $genre, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['start' => $start, 'limit' => $limit, 'genre' => $genre, 'age' => $age], [
                'start' => 'required|numeric|min:0',
                'limit' => 'required|numeric|min:1',
                'genre' => 'required|numeric|min:0',
                'age' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews')->where('material_type', 5);


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($genre != 0) {
                $query->where('genre_id', (int)$genre);
            }

            $resources = $query->limit($limit)->offset($start)->orderBy('id', 'DESC')->get();


            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }



    //* Get Resources Info Info
    function get_resource_info($id)
    {

        //* Validating response
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

        $isAllowed = false;
        //* getting customer information
        $user = auth('customers')->user();

        //* getting Item information
        $resourceInfo = Books::select('id', 'year', 'tags', 'summary', 'language', 'material_type', 'mat_category', 'genre_id', 'genre_id', 'subject_id', 'publisher_id', 'book_name', 'book_image', 'publisher_id', 'author', 'book_pdf', 'length', 'Isbn_Code')->find(e(trim(decrypt($id))));

        //if ($resourceInfo->mat_category == 0) {
           // $isAllowed = true;
        //} else {
            //$user_subscription = UsersActiveSubscriptions::where('user_id', $user->id)->first();
            //$plan_info = SubscriptionPlans::find($user_subscription->subscription_id);
            //if (!empty($user_subscription) && $user_subscription->plan_end_date >= Carbon::now()) {
                //if ($plan_info->configuration_type == 0 || $plan_info->configuration_type == null) {
                   // if (in_array($resourceInfo->material_type, explode(',', $plan_info->allowed_material))) {
                        //if ($plan_info->plan_category == 1 && $resourceInfo->mat_category <= 1) {
                          //  $isAllowed = true;
                        //} else if ($resourceInfo->mat_category > $plan_info->plan_category) {
                         //   $isAllowed = false;
                       // }
                    //} else {
                     //   $isAllowed = false;
                   // }
                //} else {
                    //$allowedPublisher =  in_array($resourceInfo->publisher_id, explode(',', $plan_info->allowed_publisher)) ? true : false;
                    //$allowedGeners =  in_array($resourceInfo->genre_id, explode(',', $plan_info->allowed_genres)) ? true : false;
                    //$allowedDepartments =  in_array($resourceInfo->department_id, explode(',', $plan_info->allowed_department)) ? true : false;
                   // $allowedSubject =  in_array($resourceInfo->subject_id, explode(',', $plan_info->allowed_subject)) ? true : false;
                 //   $isAllowed = $allowedPublisher && $allowedGeners && $allowedDepartments && $allowedSubject ? true : false;
               // }
            //} else {
               // $isAllowed = false;
           // }
       // }

       if ($resourceInfo->mat_category == 0) {
            $isAllowed = true;
        } else {
            $user_subscription = UsersActiveSubscriptions::where(['user_id' => $user->id  , 'status' => '1'])->first();
            $plan_info = SubscriptionPlans::find($user_subscription->subscription_id);
<<<<<<< HEAD
         
            if (!empty($user_subscription) && !empty($plan_info)  && $user_subscription->plan_end_date >= Carbon::now()) {

                              if (in_array($resourceInfo->material_type, explode(',', $plan_info->allowed_material))) {
=======
            if (!empty($user_subscription) && !empty($plan_info)  && $user_subscription->plan_end_date >= Carbon::now()) {

                // --------------------------previous code ----------------------------
                //     if ($plan_info->configuration_type == 0 || $plan_info->configuration_type == null) {
                //         if (in_array($resourceInfo->material_type, explode(',', $plan_info->allowed_material))) {
                //             if ($plan_info->plan_category == 1 && $resourceInfo->mat_category <= 1) {
                //                 $isAllowed = true;
                //         } else if ($resourceInfo->mat_category > $plan_info->plan_category) {
                //             $isAllowed = false;
                //         }
                //     } else {
                //         $isAllowed = false;
                //     }
                // } else {
                //     $allowedPublisher =  in_array($resourceInfo->publisher_id, explode(',', $plan_info->allowed_publisher)) ? true : false;
                //     $allowedGeners =  in_array($resourceInfo->genre_id, explode(',', $plan_info->allowed_genres)) ? true : false;
                //     $allowedDepartments =  in_array($resourceInfo->department_id, explode(',', $plan_info->allowed_department)) ? true : false;
                //     $allowedSubject =  in_array($resourceInfo->subject_id, explode(',', $plan_info->allowed_subject)) ? true : false;
                //     $isAllowed = $allowedPublisher && $allowedGeners && $allowedDepartments && $allowedSubject ? true : false;
                // }
                // --------------------------End previous code----------------------------
                // --------------------------new Code  to accsees resource
                if (in_array($resourceInfo->material_type, explode(',', $plan_info->allowed_material))) {
>>>>>>> 9933b658c329c955229ef7552218ada722b0c1ed

                    if ($plan_info->plan_category >= $resourceInfo->mat_category) {
                        $isAllowed = true;
                    } else if ($resourceInfo->mat_category > $plan_info->plan_category) {
                        $isAllowed = false;
                    } else {
                        $isAllowed = false;
                    }
                } else {
                    $isAllowed = false;
                }
<<<<<<< HEAD
                          }
=======
                // --------------------------End New code to accsees resource----------------------------
            }
>>>>>>> 9933b658c329c955229ef7552218ada722b0c1ed
        }


        $resourceInfo->resource_id = encrypt($resourceInfo->id);
        $resourceInfo->resource_name = $resourceInfo->book_name . " " . $resourceInfo->mat_category;
        $resourceInfo->author = $resourceInfo->author;
        $resourceInfo->isbn_code = $resourceInfo->Isbn_code;
        $resourceInfo->length = $resourceInfo->length;
        $resourceInfo->resource_image = $resourceInfo->book_image ? env('RESOURCES_URL') . $resourceInfo->book_image : "";
        $resourceInfo->publisher_name = getPublisherInfo($resourceInfo->publisher_id)['company_name'] ?? "";
        $resourceInfo->language = getLanguageInfo($resourceInfo->language)['language_name'] ?? "";
        $resourceInfo->material_type = getMaterialTypeInfo($resourceInfo->material_type)['material_type'] ?? "";
        $resourceInfo->material_category = getMaterialCategoryInfo($resourceInfo->mat_category) ?? "";
        $resourceInfo->genre = geGenreInfo($resourceInfo->genre_id)['genre_name'] ?? "";
        $resourceInfo->subject = geSubjectInfo($resourceInfo->subject_id)['subject_name'] ?? "";
        $resourceInfo->rating = random_int(3, 5);
        $resourceInfo->reviews = random_int(100, 500);
        $resourceInfo->format = pathinfo($resourceInfo->book_pdf, PATHINFO_EXTENSION);
        if ($isAllowed === true) {
            $resourceInfo->resource =  $resourceInfo->book_pdf ? env('RESOURCES_URL') . $resourceInfo->book_pdf : "";
        } else {
            $resourceInfo->resource = "";
        }


        //* getting Resouce Episodes
        $isResouceWishlisted = Wishlists::where('user_id', $user->id)->where('book_id', decrypt(e(trim($id))))->where('status', 'Active')->first();
        if ($isResouceWishlisted === null) {
            $resourceInfo->wishlist = false;
        } else {
            $resourceInfo->wishlist = true;
        }



        unset($resourceInfo->id);
        unset($resourceInfo->publisher_id);
        unset($resourceInfo->book_name);
        unset($resourceInfo->book_image);
        unset($resourceInfo->genre_id);
        unset($resourceInfo->subject_id);
        unset($resourceInfo->mat_category);
        unset($resourceInfo->book_pdf);
        $finalResponse[] = $resourceInfo;


        return ApiResponseHandler::successWithData($resourceInfo, "success", 200);
    }


    //* Related Resources
    function get_related_resources($id)
    {
        try {

            //* Validating response
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


            //* getting Item information
            $resourceInfo = Books::select('id', 'year', 'tags', 'summary', 'language', 'material_type', 'mat_category', 'genre_id', 'genre_id', 'subject_id', 'publisher_id', 'book_name', 'book_image', 'publisher_id', 'author', 'book_pdf')->find(e(trim(decrypt($id))));

            if ($resourceInfo === null) {
                return ApiResponseHandler::successWithData([], "success", 200);
            }

            $relatedResources = Books::select('id', 'book_name', 'book_image', 'author', 'publisher_id', 'material_type', 'rating', 'reviews')->where('subject_id', $resourceInfo->subject_id)->where('id', "!=", $resourceInfo->id)->inRandomOrder()
                ->limit(20)
                ->get();

            $finalResponse = [];

            if (!$relatedResources->isEmpty()) {
                foreach ($relatedResources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->publisher_name = getPublisherInfo($data->publisher_id)['company_name'] ?? "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->publisher_id);
                    unset($data->book_name);
                    unset($data->book_image);
                    $finalResponse[] = $data;
                }
            }


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Get Resource Episodes
    function get_resources_episodes($id)
    {
        try {
            //* Validating response
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


            //* getting Resouce Episodes

            $episodes = AppMaterialItem::select('id', 'title', 'image_file', 'sequence',)->where('appmaterial_id', decrypt(e(trim($id))))
                ->limit(50)
                ->get();

            $finalResponse = [];

            if (!$episodes->isEmpty()) {
                foreach ($episodes as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->title;
                    // $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->resource_image = $data->image_file ? env('RESOURCES_URL') . $data->image_file : "";

                    unset($data->id);
                    unset($data->title);
                    unset($data->image_file);
                    $finalResponse[] = $data;
                }
            }

            usort($finalResponse, function ($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });


            return ApiResponseHandler::successWithData($finalResponse, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Wishlist
    function wishlist($id)
    {
        try {

            //* Validating response
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

            //* getting customer information
            $user = auth('customers')->user();


            //* getting Resouce Episodes
            $isResourceExists = Wishlists::where('user_id', $user->id)->where('book_id', decrypt(e(trim($id))))->first();

            if ($isResourceExists === null) {
                $resource = new Wishlists();
                $resource->user_id = $user->id;
                $resource->book_id = decrypt(e(trim($id)));
                $resource->save();
                return ApiResponseHandler::success("ADDED_TO_WISHLIST", 200);
            }


            //* checking the status of the resouce
            if ($isResourceExists->status === "Active") {
                $isResourceExists->status = "Inactive";
                $isResourceExists->save();
                return ApiResponseHandler::success("REMOVED_FROM_WISHLIST", 200);
            } else {
                $isResourceExists->status = "Active";
                $isResourceExists->save();
                return ApiResponseHandler::success("ADDED_TO_WISHLIST", 200);
            }
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Search Resources
    function search_resources($key, $category, $age)
    {
        try {

            //* Validating response
            $validator = Validator::make(['key' => $key, 'category' => $category, 'age' => $age], [
                'key' => 'required|string',
                'category' => 'required|numeric',
                'age' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            $query = Books::select('id', 'book_name', 'book_image', 'author', 'material_type', 'rating', 'reviews');


            if ($age != 0) {
                $query->where('age', (int)$age);
            }

            if ($category != 0) {
                $query->where('material_type', (int)$category);
            }

            if (!empty($key)) {
                $query->where('book_name', 'LIKE', "%{$key}%");
            }

            $resources = $query->limit(100)->orderBy('id', 'DESC')->get();

            $finalResponse = [];

            if (!$resources->isEmpty()) {
                foreach ($resources as $data) {
                    $data->resource_id = encrypt((string) $data->id);
                    $data->resource_name = $data->book_name;
                    $data->resource_image = $data->book_image ? env('RESOURCES_URL') . $data->book_image : "";
                    $data->material_type = getMaterialTypeInfo($data->material_type)['material_type'] ?? "";
                    $data->rating = (int)$data->rating;
                    $data->reviews = (int)$data->reviews;

                    unset($data->id);
                    unset($data->book_name);
                    unset($data->book_image);
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
