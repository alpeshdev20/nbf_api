<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreRegistrations extends Model
{
    use HasFactory;
    protected $table = "pre_registrations";

    protected $hidden = [
        'otp'
    ];
}
