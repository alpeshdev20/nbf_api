<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersResources extends Model
{
    use HasFactory;


    use HasFactory;

    // Define the table associated with the model
    protected $table = 'create_users_resources';

    // Specify which attributes can be mass-assigned
    protected $fillable = [
        'resource_type',
        'name',
        'email_address',
        'mobile_number',
        'birth_date',
        'gender',
        'personal_address',
        'institution_address',
        'preferred_segment',
        'class',
        'publisher_name',
        'contact_number',
        'resource_catalogue',
        'school_college_university_name',
        'student_enrollment',
        'summary',
    ];

    // Define any casts for attributes, if needed (e.g., dates)
    protected $casts = [
        'birth_date' => 'date',
    ];

    // If you want to hide certain attributes from JSON responses, you can use the $hidden property
    protected $hidden = [];
}
