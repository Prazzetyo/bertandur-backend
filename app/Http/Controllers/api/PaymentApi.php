<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Rent;
use App\Models\PurchaseProduct;
use App\Models\Users;
use App\Models\LogMessage;
use App\Http\Controllers\api\PushNotificationApi;
use App\Models\PaymentProduct;
use App\Models\PaymentRent;

class PaymentApi extends Controller
{
    public function payment_handler(Request $req)
    {
        try {
            $pecah = explode("-", $req->order_id);
            unset($pecah[4]);

            $type = $pecah[2];

            $new_order_id = implode('-', $pecah);

            if ($type == 'rent') {
                $data = Rent::where('ORDER_ID', $new_order_id)->first(['ID_USER']);
            }

            if ($type == 'purchase') {
                $data = PurchaseProduct::where('ORDER_ID', $new_order_id)->first(['ID_USER']);
            }

            $id_user = $data->ID_USER;
            $token = Users::select('TOKEN_USER')->where('ID_USER', $id_user)->first();
            if ($token->TOKEN_USER == null) {
                $token->TOKEN_USER = '-';
            }

            // $order = new Order();
            $payment_rent = new PaymentRent();
            $payment_product = new PaymentProduct();
            $log_message = new LogMessage();

            if ($req->status_code == 201) {
                if ($type == 'rent') {
                    $bank_code = null;
                    $va_number = null;
                    $bill_code = null;
                    if (!empty($req->va_numbers)) {
                        $bank_code = $req->va_numbers[0]['bank'];
                        $va_number = $req->va_numbers[0]['va_number'];
                    }

                    if ($req->payment_type == "echannel") {
                        $bank_code = "mandiri";
                        $va_number = $req->bill_key;
                        $bill_code = $req->biller_code;
                    }

                    if (!empty($req->permata_va_number)) {
                        $bank_code = "permata";
                        $va_number = $req->permata_va_number;
                    }
                    if (!empty($req->va_numbers)) {
                        $bank_code = $req->va_numbers[0]['bank'];
                        $va_number = $req->va_numbers[0]['va_number'];
                    }

                    $payment_rent->order_id        = $req->order_id;
                    $payment_rent->total_price     = $req->gross_amount;
                    $payment_rent->payment_type    = $req->payment_type;
                    $payment_rent->status          = $req->transaction_status;
                    $payment_rent->bank_code       = $bank_code;
                    $payment_rent->va_number       = $va_number;
                    $payment_rent->bill_code       = $bill_code;
                    $payment_rent->created_at      = $req->transaction_time;
                    $payment_rent->updated_at      = $req->transaction_time;
                    $payment_rent->save();
                }

                if ($type == 'purchase') {
                    $bank_code = null;
                    $va_number = null;
                    $bill_code = null;
                    if (!empty($req->va_numbers)) {
                        $bank_code = $req->va_numbers[0]['bank'];
                        $va_number = $req->va_numbers[0]['va_number'];
                    }

                    if ($req->payment_type == "echannel") {
                        $bank_code = "mandiri";
                        $va_number = $req->bill_key;
                        $bill_code = $req->biller_code;
                    }

                    if (!empty($req->permata_va_number)) {
                        $bank_code = "permata";
                        $va_number = $req->permata_va_number;
                    }

                    $payment_product->order_id        = $req->order_id;
                    $payment_product->total_price     = $req->gross_amount;
                    $payment_product->payment_type    = $req->payment_type;
                    $payment_product->status          = $req->transaction_status;
                    $payment_product->bank_code       = $bank_code;
                    $payment_product->va_number       = $va_number;
                    $payment_product->bill_code       = $bill_code;
                    $payment_product->created_at      = $req->transaction_time;
                    $payment_product->updated_at      = $req->transaction_time;
                    $payment_product->save();
                }
            }

            if ($req->status_code == 202) {
                if ($type == 'rent') {
                    $payment_rent = PaymentRent::where('order_id', '=', $req->order_id)->first();
                    $payment_rent->updated_at      = $req->transaction_time;
                    $payment_rent->status          = $req->transaction_status;
                    $payment_rent->save();
                }

                if ($type == 'purchase') {
                    $payment_product = PaymentProduct::where('order_id', '=', $req->order_id)->first();
                    $payment_product->updated_at      = $req->transaction_time;
                    $payment_product->status          = $req->transaction_status;
                    $payment_product->save();
                }
            }

            if ($req->status_code == 200) {
                if ($req->transaction_status == "capture") {
                    if ($type == 'rent') {
                        $payment_rent->order_id        = $req->order_id;
                        $payment_rent->total_price     = $req->gross_amount;
                        $payment_rent->payment_type    = $req->payment_type;
                        $payment_rent->status          = $req->transaction_status;
                        $payment_rent->created_at      = $req->transaction_time;
                        $payment_rent->updated_at      = $req->transaction_time;
                        $payment_rent->save();
                    }

                    if ($type == 'purchase') {
                        $payment_product->order_id        = $req->order_id;
                        $payment_product->total_price     = $req->gross_amount;
                        $payment_product->payment_type    = $req->payment_type;
                        $payment_product->status          = $req->transaction_status;
                        $payment_product->created_at      = $req->transaction_time;
                        $payment_product->updated_at      = $req->transaction_time;
                        $payment_product->save();
                    }

                    $log_message->ID_USER          = $id_user;
                    $log_message->USER_TOKEN       = $token->TOKEN_USER;
                    $log_message->TITLE_MESSAGE    = 'Pembayaran berhasil';
                    $log_message->BODY_MESSAGE     = 'Selamat pembayaran anda telah berhasil';
                    $log_message->STATUS_MESSAGE   = 0;
                    $log_message->created_at       = date('Y-m-d h:i:s');
                    $log_message->save();

                    $req_notif = array(
                        'registration_ids' => array($token->TOKEN_USER),
                        'notification' => array(
                            'title' => $log_message->TITLE_MESSAGE,
                            'body' => $log_message->BODY_MESSAGE
                        )
                    );
                    (new PushNotificationApi)->push_notif($req_notif);
                }

                if ($type == 'rent') {
                    $payment_rent = PaymentRent::where('order_id', '=', $req->order_id)->first();
                    $payment_rent->updated_at      = $req->settlement_time;
                    $payment_rent->status          = $req->transaction_status;
                    $payment_rent->save();
                }

                if ($type == 'purchase') {
                    $payment_product = PaymentProduct::where('order_id', '=', $req->order_id)->first();
                    $payment_product->updated_at      = $req->settlement_time;
                    $payment_product->status          = $req->transaction_status;
                    $payment_product->save();
                }

                $log_message->ID_USER          = $id_user;
                $log_message->USER_TOKEN       = $token->TOKEN_USER;
                $log_message->TITLE_MESSAGE    = 'Pembayaran berhasil';
                $log_message->BODY_MESSAGE     = 'Pembayaran dengan order: ' . $req->order_id . ' anda telah berhasil';
                $log_message->STATUS_MESSAGE   = 0;
                $log_message->created_at       = date('Y-m-d h:i:s');
                $log_message->save();

                $req_notif = array(
                    'registration_ids' => array($token->TOKEN_USER),
                    'notification' => array(
                        'title' => $log_message->TITLE_MESSAGE,
                        'body' => $log_message->BODY_MESSAGE
                    )
                );
                (new PushNotificationApi)->push_notif($req_notif);
            }

            return response([
                "status_code"       => 200,
                "status_message"    => 'Ok!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function create_payment_link(array $param)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sandbox.midtrans.com/v1/payment-links',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($param),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic U0ItTWlkLXNlcnZlci1DN1lYcnMwUnFlbEpHRmNKR3RuNEpyRUk6'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function delete_payment_link(Request $req)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sandbox.midtrans.com/v1/payment-links/' . $req->id . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic U0ItTWlkLXNlcnZlci1DN1lYcnMwUnFlbEpHRmNKR3RuNEpyRUk6'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
