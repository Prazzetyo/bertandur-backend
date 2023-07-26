<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Rent;
use App\Models\PaymentRent;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\api\PaymentApi;
use App\Models\Land;

class RentApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $rent = DB::table('rent')
                ->crossJoin('land')
                ->crossJoin('md_province')
                ->crossJoin('md_city')
                ->crossJoin('md_district')
                ->select(
                    'rent.ID_RENT',
                    'rent.ID_USER',
                    'rent.ID_LAND',
                    'land.URLGALLERY_LAND',
                    'land.NAME_LAND',
                    'land.LENGTH_LAND',
                    'land.WIDTH_LAND',
                    'land.PRICE_LAND',
                    'land.FACILITY_LAND',
                    DB::raw('COALESCE(FORMAT((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = land.ID_LAND), 1), 0) AS RATING_LAND'),
                    'md_province.NAME_PROVINCE',
                    'md_city.NAME_CITY',
                    'md_district.NAME_DISTRICT',
                    'rent.DURATION_RENT',
                    'rent.STARTDATE_RENT',
                    'rent.ENDDATE_RENT',
                    'rent.TOTPAYMENT_RENT',
                    'rent.ORDER_ID',
                    'rent.PAYMENT_URL',
                    'rent.STATUS_REVIEW_LAND'
                )
                ->where('rent.ID_LAND', '=', DB::raw('land.ID_LAND'))
                ->where('land.PROVINCE_LAND', '=', DB::raw('md_province.ID_PROVINCE'))
                ->where('land.CITY_LAND', '=', DB::raw('md_city.ID_CITY'))
                ->where('land.DISTRICT_LAND', '=', DB::raw('md_district.ID_DISTRICT'))
                ->where('rent.ID_USER', '=', $req->id_user)
                ->get();

            foreach ($rent as $key => $value) {
                $list_img = explode(";", $rent[$key]->URLGALLERY_LAND);
                $rent[$key]->{'URLGALLERY_LAND'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $rent
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function index_web(Request $req)
    {
        try {
            $rent = DB::table('rent')
                ->crossJoin('land')
                ->crossJoin('md_province')
                ->crossJoin('md_city')
                ->crossJoin('md_district')
                ->crossJoin('user')
                ->select(
                    'rent.ID_RENT',
                    'rent.ID_USER',
                    'user.NAME_USER',
                    'rent.ID_LAND',
                    'land.URLGALLERY_LAND',
                    'land.NAME_LAND',
                    'land.LENGTH_LAND',
                    'land.WIDTH_LAND',
                    'land.PRICE_LAND',
                    'land.FACILITY_LAND',
                    DB::raw('COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = land.ID_LAND), 0) AS RATING_LAND'),
                    'md_province.NAME_PROVINCE',
                    'md_city.NAME_CITY',
                    'md_district.NAME_DISTRICT',
                    'rent.DURATION_RENT',
                    'rent.STARTDATE_RENT',
                    'rent.ENDDATE_RENT',
                    'rent.TOTPAYMENT_RENT',
                    'rent.ORDER_ID',
                    'rent.PAYMENT_URL',
                    'rent.STATUS_REVIEW_LAND'
                )
                ->where('rent.ID_LAND', '=', DB::raw('land.ID_LAND'))
                ->where('land.PROVINCE_LAND', '=', DB::raw('md_province.ID_PROVINCE'))
                ->where('land.CITY_LAND', '=', DB::raw('md_city.ID_CITY'))
                ->where('land.DISTRICT_LAND', '=', DB::raw('md_district.ID_DISTRICT'))
                ->where('rent.ID_USER', '=', DB::raw('user.ID_USER'))
                ->get();

            foreach ($rent as $key => $value) {
                $list_img = explode(";", $rent[$key]->URLGALLERY_LAND);
                $rent[$key]->{'URLGALLERY_LAND'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $rent
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function detail(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_rent'   => 'required'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            $id = $req->id_rent;

            $rent = DB::table('rent')
                ->crossJoin('land')
                ->crossJoin('md_province')
                ->crossJoin('md_city')
                ->crossJoin('md_district')
                ->crossJoin('user')
                ->select(
                    'rent.ID_RENT',
                    'rent.ID_USER',
                    'user.EMAIL_USER',
                    'user.NAME_USER',
                    'user.TELP_USER',
                    'user.ADDRESS_USER',
                    'rent.ID_LAND',
                    'land.URLGALLERY_LAND',
                    'land.NAME_LAND',
                    'land.LENGTH_LAND',
                    'land.WIDTH_LAND',
                    'land.PRICE_LAND',
                    'land.FACILITY_LAND',
                    DB::raw('COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = land.ID_LAND), 0) AS RATING_LAND'),
                    'md_province.NAME_PROVINCE',
                    'md_city.NAME_CITY',
                    'md_district.NAME_DISTRICT',
                    'rent.DURATION_RENT',
                    'rent.STARTDATE_RENT',
                    'rent.ENDDATE_RENT',
                    'rent.TOTPAYMENT_RENT',
                    'rent.ORDER_ID',
                    'rent.PAYMENT_URL',
                    'rent.STATUS_REVIEW_LAND'
                )
                ->where('rent.ID_LAND', '=', DB::raw('land.ID_LAND'))
                ->where('land.PROVINCE_LAND', '=', DB::raw('md_province.ID_PROVINCE'))
                ->where('land.CITY_LAND', '=', DB::raw('md_city.ID_CITY'))
                ->where('land.DISTRICT_LAND', '=', DB::raw('md_district.ID_DISTRICT'))
                ->where('rent.ID_USER', '=', DB::raw('user.ID_USER'))
                ->where('rent.ID_RENT', '=', $id)
                ->first();

            $payment_rent = PaymentRent::where('order_id', 'LIKE', '%' . $rent->ORDER_ID . '%')->get();
            $rent->{'PAYMENT_DETAIL'} = $payment_rent;

            $list_img = explode(";", $rent->URLGALLERY_LAND);
            $rent->{'URLGALLERY_LAND'} = $list_img;


            if ($rent == null) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Data tidak ditemukan!',
                ], 400);
            } else {
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Data berhasil ditemukan!',
                    'data'              => $rent
                ], 200);
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function store(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_land'       => 'required|exists:land,ID_LAND',
                'duration_rent' => 'required',
                'start_date'    => 'required',
                'end_date'      => 'required',
                // 'payment_method' => 'required',
                'total_payment' => 'required',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $order_id = "tandur-pay-rent-" . substr(md5(time() . rand(10, 99)), 0, 8);
            $req_payment = array(
                'transaction_details' => array(
                    'order_id' => $order_id,
                    'gross_amount' => $req->input('total_payment'),
                    'payment_link_id' => $order_id,
                    'currency' => 'IDR'
                ),
                'credit_card' => array(
                    'secure' => true
                ),
                'expiry' => array(
                    'unit' => 'days',
                    'duration' => 1
                ),
                'usage_limit' => 1,
                'customer_details' => array(
                    'first_name' => $req->name_user,
                    'last_name' => null,
                    'email' => $req->email_user,
                    'phone' => $req->telp_user,
                    'notes' => 'Thank you for your order. Please follow the instructions to complete payment.',
                )
            );

            $checkLand = Land::select('IS_ACTIVE')->where('ID_LAND', '=', $req->input('id_land'))->first();

            if ($checkLand->IS_ACTIVE == 0 || $checkLand->IS_ACTIVE == 2) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => 'Lahan tidak aktif atau sedang disewa!'
                ], 400);
            } else {
                $payment = (new PaymentApi)->create_payment_link($req_payment);
                $data_payment = json_decode($payment);

                $rent = new Rent();
                $rent->ID_RENT          = "RENT_" . substr(md5(time() . rand(10, 99)), 0, 6);
                $rent->ID_USER          = $req->input('id_user');
                $rent->ID_LAND          = $req->input('id_land');
                $rent->DURATION_RENT    = $req->input('duration_rent');
                $rent->ORDER_ID         = $data_payment->order_id;
                $rent->PAYMENT_URL      = $data_payment->payment_url;
                $rent->STARTDATE_RENT   = $req->input('start_date');
                $rent->ENDDATE_RENT     = $req->input('end_date');
                // $rent->PAYMENT_RENT     = $req->input('payment_method');
                $rent->TOTPAYMENT_RENT  = $req->input('total_payment');
                $rent->save();

                $land = new Land();
                $land = Land::where('ID_LAND', '=', $req->input("id_land"))->first();
                $land->IS_ACTIVE       = 2;
                $land->save();
            }

            // if ($rent->save()) {
            //     $pay_rent = new PaymentRent();

            //     $pay_rent->ID_PAYRENT           = "PAY_" . substr(md5(time() . rand(10, 99)), 0, 6);
            //     $pay_rent->ID_RENT              = $rent->ID_RENT;
            //     $pay_rent->ID_USER              = $req->input("id_user");
            //     $pay_rent->ORDERID_PAYRENT      = "ORDER_" . substr(md5(time() . rand(10, 99)), 0, 8);
            //     $pay_rent->TIMEEXP_PAYRENT      = date('Y-m-d H:i:s', strtotime('+24 hour', strtotime(date('Y-m-d H:i:s'))));
            //     $pay_rent->METHOD_PAYRENT       = $req->input('payment_method');
            //     $pay_rent->TOTPAYMENT_PAYRENT   = $req->input('total_payment');
            //     $pay_rent->save();
            // }

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => [
                    'id_rent' => $rent->ID_RENT,
                    'payment_url' => $data_payment->payment_url
                ]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function evidence_upload(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_rent'           => 'required|exists:rent,ID_RENT',
                'evidence_transfer' => 'required|image',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $path = $req->file('evidence_transfer')->store('images', 's3');

            $pay_Rent = PaymentRent::where('ID_RENT', '=', $req->input("id_rent"))->first();
            $pay_Rent->EVIDENCE_TRANSFER    = Storage::disk('s3')->url($path);
            $pay_Rent->TIME_PAYRENT         = date('Y-m-d H:i:s');
            $pay_Rent->STATUS_PAYRENT       = 1;
            $pay_Rent->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Pembayaran Berhasil!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function verif_rent(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_rent'           => 'required|exists:rent,ID_RENT',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $status_Rent = PaymentRent::where('ID_RENT', '=', $req->input("id_rent"))->first();
            $status_Rent->STATUS_PAYRENT       = 2;
            $status_Rent->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Berhasil verifikasi!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
