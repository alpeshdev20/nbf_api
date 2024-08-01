<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanParentCategory extends Model
{
    use HasFactory;

    protected $table = "plan_parent_category";

    public function subscriptionPlans()
    {
        return $this->hasMany(SubscriptionPlans::class);
    }
}
