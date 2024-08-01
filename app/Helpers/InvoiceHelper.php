<?php

namespace App\Helpers;
use Dompdf\Dompdf;
use App\Models\transaction;
use App\Models\SubscriptionPlans;
use App\Models\user_address;
use App\Models\state;
use App\Models\Customers;
class InvoiceHelper
{   
    public static function setPaymentInvoice($transaction_id,$subscription_plan_id,$user_id)
    {
        error_reporting(0);
        $user_info = Customers::find($user_id);
        
        // if($shippingAddress->is_same_billing == 1) {
        //     $billingAddress = $shippingAddress;
        // } else {
        //     $billingAddress = user_address::where(['user_id' => $user_id, 'address_type' => 1])->first();
        // }

        $subscription_plan = SubscriptionPlans::find($subscription_plan_id);
        $transaction = Transaction::find($transaction_id);
        // $user_info = Customers::find($transaction->user_id);
        $html = view('invoice-template.invoice',compact('transaction','user_info','subscription_plan'));        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = $transaction->id.'.pdf';
        $output = $dompdf->output();
        $invoicePath = public_path('uploads/invoices/');

        if (!file_exists($invoicePath)) {
            mkdir($invoicePath, 0777, true);
        }
        file_put_contents($invoicePath . $filename, $output); 
        return $filename;
    }
}