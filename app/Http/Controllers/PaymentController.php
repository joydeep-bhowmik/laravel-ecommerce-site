<?php
namespace App\Http\Controllers;

use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $input   = $request->all();
        $api     = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        if (count($input) && ! empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);
                $payment  = Payment::create([
                    'r_payment_id'  => $response['id'],
                    'method'        => $response['method'],
                    'currency'      => $response['currency'],
                    'user_email'    => $response['email'],
                    'amount'        => $response['amount'] / 100,
                    'json_response' => json_encode((array) $response),
                ]);
            } catch (Exceptio $e) {
                return $e->getMessage();
                return redirect()->back();
            }
        }
        return redirect()->back();
    }
}
