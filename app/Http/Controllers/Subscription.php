<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\Customers;
use App\Models\SubscriptionPlans;
use App\Models\PlanParentCategory;
use App\Models\UsersActiveSubscriptions;
use Illuminate\Http\Request;
use Validator;

class Subscription extends Controller
{
    function get_subscription_plans() {

        $data = SubscriptionPlans::where('status', 1)->get();
        $plan = $this->transformData($data);

        return ApiResponseHandler::successWithData($plan, "success", 200);
    }

    function transformData($data) {
        $result = [];

        foreach ($data as $plan) {
            $category = $this->getParentCategoryName($plan['plan_parent_category_id']);
            $subCategory = $this->getSubCategoryName($plan['plan_category']);

            // Check if category exists
            $categoryIndex = null;
            foreach ($result as $index => $item) {
                if ($item['category'] === $category) {
                    $categoryIndex = $index;
                    break;
                }
            }

            // If category doesn't exist, create it
            if ($categoryIndex === null) {
                $result[] = [
                    'id' => count($result) + 1,
                    'category' => $category,
                    'sub_category' => []
                ];

                $categoryIndex = count($result) - 1;
            }

            // Check if sub-category exists
            $subCategoryIndex = null;
            foreach ($result[$categoryIndex]['sub_category'] as $index => $subCat) {
                if ($subCat['title'] === $subCategory) {
                    $subCategoryIndex = $index;
                    break;
                    }
            }

            // If sub-category doesn't exist, create it
            if ($subCategoryIndex === null) {
                $result[$categoryIndex]['sub_category'][] = [
                    'id' => count($result[$categoryIndex]['sub_category']) + 1,
                    'title' => $subCategory,
                    'packages' => []
                ];

                $subCategoryIndex = count($result[$categoryIndex]['sub_category']) - 1;
            }

            // Add the package
            $result[$categoryIndex]['sub_category'][$subCategoryIndex]['packages'][] = [
                'id' => $plan['id'],
                'title' => $plan['name'],
                'price' => $plan['price'],
                'package_covers' => $this->getPackageCovers($plan)
            ];
        }

        return $result;
    }

    function getSubCategoryName($category) {
        switch ($category) {
            case 1: return 'BASIC';
            case 2: return 'PREMIUM';
            default: return 'UNKNOWN';
        }
    }

    function getParentCategoryName($category) {

        $parentCategories = PlanParentCategory::all();

        foreach ($parentCategories as $parentCategory) {
            if ($parentCategory->id == $category) {
                return $parentCategory->name;
            }
        }
    }

   // function getPackageCovers($plan) {
       // $covers = [];
       // if ($plan['description']) {
           // $covers[] = ['content' => $plan['description']];
        //}
        //return $covers;
    //}


function getPackageCovers($plan) {
        $covers = [];

        // Check if 'description' is set and not empty
        if (isset($plan['description']) && !empty($plan['description'])) {
            // Split the description by comma
            $descriptions = explode(',,', $plan['description']);

            // Map each part to an array of covers
            foreach ($descriptions as $desc) {
                // Trim any extra spaces and add to covers array
                $covers[] = ['content' => trim($desc)];
            }
        }
        return $covers;
    }


    function cancelSubscription($id)
    {
        try {
            // Validate the ID
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        try {
                            $decryptedId = decrypt($value);
                            if (!Customers::where('id', $decryptedId)->exists()) {
                                $fail("Customer with given ID does not exist.");
                            }
                        } catch (\Exception $e) {
                            $fail("Invalid ID provided.");
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return ApiResponseHandler::error($validator->messages(), 400);
            }

            // Decrypt the ID
            $decryptedId = decrypt($id);

            // Fetch the active subscription
            $activeSubscription = UsersActiveSubscriptions::where(['user_id'=> $decryptedId ,'status' =>1 ])->first();
            if ($activeSubscription) {

                $activeSubscription->update(['status' => '0']);

                return ApiResponseHandler::success($activeSubscription, 200);
            } else {
                return ApiResponseHandler::error("No active subscription found for the user.", 404);
            }
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }
}
