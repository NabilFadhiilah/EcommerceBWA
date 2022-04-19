<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;

class MidtransController extends Controller
{
    //
    public function callback()
    {
        # code...
        // set configure midtrans
        // configure midtrans
        // Set your Merchant Server Key
        Config::$serverKey = config('services.midtrans.serverKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('services.midtrans.isProduction');
        // Set sanitization on (default)
        Config::$isSanitized = config('services.midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        Config::$is3ds = config('services.midtrans.is3ds');

        // set instance midtrans notification
        $notification = new Notification();

        // assign variable
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // get transaction id for update
        $order = explode('-', $order_id);

        // search transaction by id
        $transaction = Transaction::findOrFail($order[1]);

        // handle notification status midtrans
        if ($status == 'capture') {
            # code...
            if ($type == 'credit_card') {
                # code...
                if ($fraud == 'challange') {
                    # code...
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'Success';
                }
            }
        } elseif ($status == 'settlement') {
            $transaction->status = 'SUCCESS';
        } elseif ($status == 'pending') {
            $transaction->status = 'PENDING';
        } elseif ($status == 'deny') {
            $transaction->status = 'PENDING';
        } elseif ($status == 'expired') {
            $transaction->status = 'CANCELED';
        } elseif ($status == 'cancel') {
            $transaction->status = 'CANCELED';
        }
        // save transaction
        $transaction->save();
        // return response for midtrans
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'midtrans notification success'
            ]
        ]);
    }
}
