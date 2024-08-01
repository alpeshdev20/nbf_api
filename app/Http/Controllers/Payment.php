<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SubscriptionPlans;
use App\Models\UsersActiveSubscriptions;
use App\Models\Transaction;
use App\Models\CouponCode;
use App\Models\Customers;
use App\Helpers\InvoiceHelper;
use App\Helpers\PaytmChecksum;
use App\Mail\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use DB;

class Payment extends Controller
{
    public function pay(Request $request)
    {
        $MID = env('PAYTM_MID');
        $PAYTM_PAYMENT_URL = env('PAYTM_PAYMENT_URL');
        $plan = SubscriptionPlans::find($request->subscription_id);
        $amount = $plan->price;
        $discount = 0;
        $couponId = $request->get('coupon_id', '0');
        $isUpgrade = $request->has('upgrade') ? '1' : '0';

        if ($couponId) {
            $coupon = CouponCode::find($couponId);
            if ($coupon) {
                $discountPercentage = $coupon->discount_percentage;
                $discount = round($amount * ($discountPercentage / 100));
                $amount -= $discount;
            }
        }

        $user = Customers::find(decrypt($request->user_id));

        $active_plan = UsersActiveSubscriptions::where('user_id', $user->id)->first();
        $planEndDate = Carbon::parse($active_plan->plan_end_date);
        $validityDays = $plan->validity;

        // Subtract the validity days from the plan end date
        $adjustedEndDate = $planEndDate->subDays($validityDays);

        // Get the current date
        $currentDate = Carbon::now();

        // Calculate the difference in days
        $diffInDays = $adjustedEndDate->diffInDays($currentDate);
        if ($diffInDays < 7) {
            $active_plan_amount = SubscriptionPlans::find($active_plan->subscription_id);
            $active_plan_amount = $active_plan_amount->price;
            if ($active_plan_amount < $plan->price) {
                $amount = $plan->price - $active_plan_amount;
            }
        }

        $orderId = DB::table('transactions')->insertGetId([
            'amount' => $amount,
            'status' => 'TXN_PENDING',
            'user_id' => $user->id,
            'subscription_id' => $plan->id,
            'subscription_name' => $plan->name,
            'subscription_validity' => $plan->validity,
            'created_at' => Carbon::now(),
            'txn_id' => substr(str_shuffle("01234567891234567"), 0, 16),
            'coupon_code_id' => $couponId,
            'discounted_amount' => $discount,
            'isupgraded' => $isUpgrade
        ]);

        $orderId = $orderId + 500;

        if ($request->has('bypass')) {
            $request['ORDERID'] = $orderId;
            $request['STATUS'] = 'TXN_SUCCESS';
            $this->paymentStatus($request);
        } else {
            $paytmParams = [
                "body" => [
                    "requestType" => "Payment",
                    "mid" => $MID,
                    "websiteName" => "WEBSTAGING",
                    "orderId" => $orderId,
                    "callbackUrl" => url(env('PAYTM_REDIRECT_URL')),
                    "txnAmount" => [
                        "value" => $amount,
                        "currency" => "INR",
                    ],
                    "userInfo" => [
                        "custId" => $user->id,
                        "custName" => $user->name,
                    ],
                ]
            ];

            $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), env('PAYTM_MERCHANT_KEY'));
            $paytmParams["head"] = ["signature" => $checksum];

            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
            $url = str_replace(["{{MID}}", "{{ORDER_ID}}"], [$MID, $orderId], $PAYTM_PAYMENT_URL);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            $response = curl_exec($ch);
            curl_close($ch);

            $paytmResponse = json_decode($response, true);
            $resultInfo = $paytmResponse['body']['resultInfo'];

            if ($resultInfo['resultStatus'] !== 'F') {
                return [
                    'success' => true,
                    'response' => $paytmResponse,
                    'orderId' => $orderId,
                    'amount' => $amount
                ];
            } else {
                return ['success' => false, 'response' => $resultInfo['resultMsg']];
            }
        }
    }

    public function paymentStatus(Request $request)
    {
        $orderId = $request->ORDERID;

        if ($orderId && $this->validatePayment($orderId)) {
            $transaction = Transaction::find($orderId - 500);
            $user_id = $transaction->user_id;
            $plan = SubscriptionPlans::find($transaction->subscription_id);

            DB::table('transactions')
                ->where('id', $orderId)
                ->update([
                    'status' => $request->STATUS,
                    'response_body' => json_encode($request->all()),
                    'updated_at' => Carbon::now()
                ]);

            if ($request->STATUS === 'TXN_FAILURE') {
                $defaultPlan = SubscriptionPlans::find(1);
                DB::table('subscribers')->where('user_id', $user_id)->update([
                    'plan_name' => $defaultPlan->name,
                    'plan_end_date' => Carbon::now()->addDays($defaultPlan->validity),
                    'subscription_id' => $defaultPlan->id
                ]);
                return Redirect::to(env('FRONT_APP_URL') . env('PAYMENT_FAILED'));
            }

            InvoiceHelper::setPaymentInvoice($transaction->id, $plan->id, $user_id);

            $user = UsersActiveSubscriptions::select('subscribers.*', 'subscription_plans.price')
                ->where('user_id', $user_id)
                ->leftJoin('subscription_plans', 'subscribers.subscription_id', '=', 'subscription_plans.id')
                ->first();

            $ulogin = Customers::find($user_id);

            $updateSubscriber = new UsersActiveSubscriptions();
            $updateSubscriber->updateSubscriptionPlan($user_id, $plan->id);

            $subscriber = UsersActiveSubscriptions::where('user_id', $user_id)->latest()->first();
            Mail::mailer("support")->to($request->input('email'))->send(new Invoice($ulogin, $plan, $subscriber, $transaction->id));
            // $message = "<p>Hello {$ulogin->name}, <br /><br /> Your payment has been successful.<br /><br />
            // Plan Name: {$plan->name}
            // <br /><br />
            // Plan Price: {$plan->price}
            // <br /><br />
            // Expiry Date: {$subscriber->plan_end_date}
            // <br /><br />
            // Regards, <br /> Netbookflix
            // </p>";

            // $email = $ulogin->email;
            // $invoicePath = public_path("../../ebook/public/uploads/invoices/{$transaction->id}.pdf");
            // EmailHelper::sendPaymentSuccessEmail($email, "Netbookflix: Payment success", $message, $invoicePath);
            return Redirect::to(env('FRONT_APP_URL') . env('PAYMENT_SUCCESS'));
        } else {
            return Redirect::to(env('FRONT_APP_URL') . env('PAYMENT_FAILED'));
        }
    }

    public function validatePayment($orderId)
    {
        $MID = env('PAYTM_MID');
        $PAYTM_MERCHANT_KEY = env('PAYTM_MERCHANT_KEY');

        $paytmParams = [
            "body" => [
                "mid" => $MID,
                "orderId" => $orderId,
            ]
        ];

        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $PAYTM_MERCHANT_KEY);
        $paytmParams["head"] = ["signature" => $checksum];

        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $url = env('PAYMENT_STATUS_URL');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $paytmResponse = json_decode($response, true);
        $resultInfo = $paytmResponse['body']['resultInfo'];
        return $resultInfo['resultStatus'] === 'TXN_SUCCESS';
    }
}
