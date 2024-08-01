<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class UsersActiveSubscriptions extends Model
{
    use HasFactory;
    protected $table = "subscribers";

    protected $fillable = [
        'plan_name',
        'Plan_end_date',
        'user_id',
        'subscription_id',
        'auto_renew',
        'status'
    ];

    public function updateSubscriptionPlan($user_id, $plan_id)
	{
        DB::table('subscribers')->where('user_id',$user_id)->update([
            'status' => 0
        ]);

		$getplan = SubscriptionPlans::find($plan_id);

        DB::table('subscribers')->insertGetId([
            'plan_name' => $getplan->name,
            'plan_end_date' => Carbon::now()->addDays($getplan->validity)->format('Y-m-d H:m:s'),
            'user_id' => $user_id, 
            'subscription_id' => $getplan->id, 
            'status' => '1',
        ]);
	}
}
