<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHandler;
use App\Models\ClassesMaster;
use App\Models\CmsPages;
use App\Models\Genres;
use App\Models\MaterialTypes;

class General extends Controller
{
    //* Get All Classes
    function get_classes()
    {
        try {
            $classes = ClassesMaster::select('id', 'class_name')->get();
            return ApiResponseHandler::successWithData($classes, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Get All Age Group
    function get_age_groups()
    {
        try {
            $age_groups = ageGroups();
            return ApiResponseHandler::successWithData($age_groups, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //* Get All Material Categories
    function get_resource_categories()
    {
        try {
            $resource_categories = MaterialTypes::select('id', 'material_type')->get();
            return ApiResponseHandler::successWithData($resource_categories, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }

    //* Terms & Conditions
    function terms_condtions_page()
    {
        try {
            $page = CmsPages::select('page_name', 'content')->where('active', '1')->where('page_name', 'Terms & Conditions')->first();
            return ApiResponseHandler::successWithData($page, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Privacy Policies
    function privacy_policies_page()
    {
        try {
            $page = CmsPages::select('page_name', 'content')->where('active', '1')->where('page_name', 'privacy-policy')->first();
            return ApiResponseHandler::successWithData($page, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }



    //* faqs
    function faqs_page()
    {
        try {
            $page = CmsPages::select('page_name', 'content')->where('active', '1')->where('page_name', 'FAQ\'s')->first();
            return ApiResponseHandler::successWithData($page, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //* Get Genres
    function get_genres()
    {
        try {
            $genres = Genres::select('id', 'genre_name')->get();
            return ApiResponseHandler::successWithData($genres, "success", 200);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("INTERNAL_SERVER_ERROR", 500);
        }
    }


    //! Class Ends
}
