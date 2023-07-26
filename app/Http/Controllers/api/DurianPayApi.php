<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Durianpay\Durianpay;
use Durianpay\Exceptions\BadRequestException;

Durianpay::setApiKey('dp_test_aubDzC4Ddmpac05n');

class DurianPayApi extends Controller
{
    public function tes()
    {
        return "Tes";
    }

    public function create_order()
    {
        $res = \Durianpay\Resources\Order::create(
            [
                'amount' => '2000000',
                'payment_option' => 'full_payment',
                'currency' => 'IDR',
                'order_ref_id' => 'order_ref_001',
                'customer' => [
                    'customer_ref_id' => 'cust_001',
                    'given_name' => 'Dwi Prasetyo',
                    'email' => 'dwipras_@gmail.com',
                    'mobile' => '6285826125994',
                ],
                'items' => [
                    [
                        'name' => 'Tecno Spark Go 2023',
                        'qty' => 1,
                        'price' => '1000000',
                        'logo' => 'https://carisinyal.com/wp-content/uploads/2023/01/Tecno-Spark-Go-2023_.webp',
                    ],
                    [
                        'name' => 'Infinix Hot 20i',
                        'qty' => 1,
                        'price' => '1000000',
                        'logo' => 'https://carisinyal.com/wp-content/uploads/2022/10/Infinix-Hot-20i_.webp',
                    ],
                ]
            ]
        );

        return json_encode($res);
    }

    public function fetch_orders()
    {
        $res = \Durianpay\Resources\Order::fetch(
            [
                'from' => '2023-05-01',
                'to' => '2023-05-31',
                'skip' => '0',
                'limit' => '8'
            ]
        );

        return json_encode($res);
    }

    public function fetch_single()
    {
        $res = \Durianpay\Resources\Order::fetchOne('ord_dVho6d7TUG7986');
        return json_encode($res);
    }

    public function create_payment_link()
    {
        $res = \Durianpay\Resources\Order::createPaymentLink(
            [
                'amount' => '2000000',
                'currency' => 'IDR',
                'customer' => [
                    'given_name' => 'Dwi Prasetyo',
                    'email' => 'dwipras_@gmail.com',
                    'mobile' => '6285826125994',
                ],
                'items' => [
                    [
                        'name' => 'Tecno Spark Go 2023',
                        'qty' => 1,
                        'price' => '1000000',
                        'logo' => 'https://carisinyal.com/wp-content/uploads/2023/01/Tecno-Spark-Go-2023_.webp',
                    ],
                    [
                        'name' => 'Infinix Hot 20i',
                        'qty' => 1,
                        'price' => '1000000',
                        'logo' => 'https://carisinyal.com/wp-content/uploads/2022/10/Infinix-Hot-20i_.webp',
                    ],
                ]
            ]
        );

        return json_encode($res);
    }

    public function create_payment_charge()
    {
        try {
            $type = 'VA'; // EWALLET, VA, RETAILSTORE, ONLINE_BANKING, BNPL, or QRIS

            $res = \Durianpay\Resources\Payment::charge($type, [
                'order_id' => 'ord_wBxSvHaUlN0752',
                'bank_code' => 'MANDIRI',
                'name' => 'Dwi Prasetyo',
                'amount' => '2000000',
                'sandbox_options' => [
                    'force_fail' => true,
                    'delay_ms' => 10000
                ]
            ]);

            // $res = \Durianpay\Resources\Payment::charge($type, [
            //     'order_id' => 'ord_roLkO9pjMq4398',
            //     'bank_code' => 'BCA',
            //     'name' => 'Dwi Prasetyo',
            //     'amount' => '2000000'
            // ]);

            // $res = \Durianpay\Resources\Payment::charge($type, [
            //     'amount' => '2000000',
            //     'order_id' => 'ord_wBxSvHaUlN0752',
            //     'name' => 'Name Appear in ATM',
            //     "type" => "SHOPEEPAY"
            // ]);

            return json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }

    public function fetch_payments()
    {
        try {
            $res = \Durianpay\Resources\Payment::fetch();
            echo json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }

    public function fetch_payments_single()
    {
        try {
            $res = \Durianpay\Resources\Payment::fetchOne('pay_coQL46Rqc26945');
            echo json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }

    public function payments_status()
    {
        try {
            $res = \Durianpay\Resources\Payment::checkStatus('pay_coQL46Rqc26945');
            echo json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }

    public function verify_payments()
    {
        try {
            $signature = '1087d247921d51d012f3d7ec38e5059eb9f22a40f0e4693c6034a29d8e9e7161';
            $res = \Durianpay\Resources\Payment::verify('pay_coQL46Rqc26945', $signature);
            echo json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }

    public function cancel_payments()
    {
        try {
            $res = \Durianpay\Resources\Payment::cancel('pay_GkbxrlLmvg7841');
            echo json_encode($res);
        } catch (BadRequestException $error) {
            $errorDesc = $error->getDetailedErrorDesc();

            echo $error;
            echo json_encode($errorDesc);
        }
    }
}
