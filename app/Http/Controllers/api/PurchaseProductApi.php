<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PurchaseProduct;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\PaymentProduct;
use App\Http\Controllers\api\PaymentApi;

class PurchaseProductApi extends Controller
{
    public function store(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'product.*.id_product' => 'required|exists:product,ID_PRODUCT',
                'shipping_method'   => 'required',
                'name_user'         => 'required',
                'email_user'        => 'required',
                'telp_user'         => 'required',
                'alamat_user'       => 'required',
                'shipping_cost'     => 'required',
                'total_payment'     => 'required',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $pecahIdProduct = [];
            $pecahIdProductRemain = [];
            $stok = [];

            $idproduct = [];
            foreach ($req->product as $item) {
                array_push($idproduct, $item['id_product']);
            }

            $cekStok = Product::select('ID_PRODUCT', 'STOCK_PRODUCT')->whereIn('ID_PRODUCT', $idproduct)->get();

            foreach ($cekStok as $item) {
                array_push($pecahIdProduct, $item['ID_PRODUCT']);
                array_push($pecahIdProductRemain, $item['STOCK_PRODUCT']);
            }

            for ($i = 0; $i < count($pecahIdProduct); $i++) {
                $stok[$pecahIdProduct[$i]] = $pecahIdProductRemain[$i];
            }

            $tdkLolos = 0;
            foreach ($req->product as $item) {
                if ($item['qty'] > $stok[$item['id_product']]) {
                    $tdkLolos++;
                }
            }

            if ($tdkLolos > 0) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => "Periksa sisa stok dan jumlah beli anda!",
                ], 400);
            }

            $order_id = "tandur-pay-purchase-" . substr(md5(time() . rand(10, 99)), 0, 8);
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
                    'first_name' => $req->input('name_user'),
                    'last_name' => null,
                    'email' => $req->input('email_user'),
                    'phone' => $req->input('telp_user'),
                    'notes' => 'Thank you for your order. Please follow the instructions to complete payment.',
                )
            );

            $payment = (new PaymentApi)->create_payment_link($req_payment);
            $data_payment = json_decode($payment);

            $idproduct = array();
            $qty = array();
            $totalharga = array();
            foreach ($req->input('product') as $item) {
                array_push($idproduct, $item['id_product']);
                array_push($qty, $item['qty']);
                array_push($totalharga, $item['total_harga']);
            }

            $purchase_product = new PurchaseProduct();

            $purchase_product->ID_PURCHASE          = "PURCHASE_" . substr(md5(time() . rand(10, 99)), 0, 6);
            $purchase_product->ID_USER              = $req->input('id_user');
            $purchase_product->TOTQTY_PURCHASE      = array_sum($qty);
            $purchase_product->SHIPPING_COST        = $req->input('shipping_cost');
            $purchase_product->TOTPAYMENT_PURCHASE  = $req->input('total_payment');
            $purchase_product->SHIPPING_METHOD      = $req->input('shipping_method');
            $purchase_product->NAME_USER            = $req->input('name_user');
            $purchase_product->EMAIL_USER           = $req->input('email_user');
            $purchase_product->TELP_USER            = $req->input('telp_user');
            $purchase_product->ALAMAT_USER          = $req->input('alamat_user');
            $purchase_product->ORDER_ID             = $data_payment->order_id;
            $purchase_product->PAYMENT_URL          = $data_payment->payment_url;

            if ($purchase_product->save()) {
                foreach ($req->input('product') as $item) {
                    PurchaseDetail::insert([
                        [
                            'ID_PRODUCT'    => $item['id_product'],
                            'ID_PURCHASE'   => $purchase_product->ID_PURCHASE,
                            'QTY_PD'        => $item['qty'],
                            'TOTAL_PRICE'   => $item['total_harga'],
                        ]
                    ]);

                    $produk = Product::find($item['id_product']);
                    $produk->STOCK_PRODUCT  = $produk->STOCK_PRODUCT - $item['qty'];
                    $produk->save();
                }

                // $pay_product = new PaymentProduct();

                // $pay_product->ID_PAYPRODUCT        = "PAY_" . substr(md5(time() . rand(10, 99)), 0, 6);
                // $pay_product->ID_PURCHASE          = $purchase_product->ID_PURCHASE;
                // $pay_product->ID_USER              = $req->input("id_user");
                // $pay_product->ORDERID_PAYPRODUCT   = "ORDER_" . substr(md5(time() . rand(10, 99)), 0, 8);
                // $pay_product->TIMEEXP_PAYPRODUCT   = date('Y-m-d H:i:s', strtotime('+24 hour', strtotime(date('Y-m-d H:i:s'))));
                // $pay_product->METHOD_PAYPRODUCT    = $req->input('payment_method');
                // $pay_product->TOTPAYMENT_PAYPRODUCT = $req->input('total_payment');
                // $pay_product->save();

                return response([
                    "status_code"       => 200,
                    "status_message"    => 'Data berhasil disimpan!',
                    "data"              => [
                        'id_purchase' => $purchase_product->ID_PURCHASE,
                        'payment_url' => $data_payment->payment_url
                    ]
                ], 200);
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function list_purchase(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                // 'id_purchase'   => 'required|exists:purchase_product,ID_PURCHASE'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            $user_id = "";

            if ($req->input('user_id') != null) {
                $user_id .= "AND pp.ID_USER LIKE '%" . $req->input('user_id') . "%'";
            }

            $list_purchase = PurchaseProduct::select(
                'ID_PURCHASE',
                'ID_USER',
                'NAME_USER',
                'EMAIL_USER',
                'TELP_USER',
                'ALAMAT_USER',
                'TOTQTY_PURCHASE',
                'ORDER_ID',
                'PAYMENT_URL',
                'TOTPAYMENT_PURCHASE',
                'SHIPPING_METHOD',
                'SHIPPING_COST',
                'STATUS_REVIEW_PRODUCT'
            )->where('ID_USER', '=', $req->id_user)->get();

            foreach ($list_purchase as $key => $value) {
                $purchase_detail = DB::select("
                    SELECT 
                        pd.ID_PRODUCT,
                        p.NAME_PRODUCT,
                        p.PRICE_PRODUCT,
                        FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                        pd.QTY_PD,
                        FORMAT(pd.QTY_PD*p.WEIGHT_PRODUCT, 2) AS TOTAL_WEIGHT,
                        pd.TOTAL_PRICE,
                        p.PHOTO_PRODUCT,
                        COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = pd.ID_PRODUCT),0) AS RATING_PRODUCT,
                        mc.NAME_CITY 
                    FROM purchase_detail pd, product p, `user` u, md_city mc  
                        WHERE 
                        pd.ID_PRODUCT = p.ID_PRODUCT AND p.ID_USER = u.ID_USER AND u.ID_CITY = mc.ID_CITY  
                        AND 
                        pd.ID_PURCHASE  = '" . $list_purchase[$key]->ID_PURCHASE . "'
                    ");
                $list_purchase[$key]->{'PURCHASE_DETAIL'} = $purchase_detail;
            }

            if ($list_purchase == null) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Data tidak ditemukan!',
                ], 400);
            } else {
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Data berhasil ditemukan!',
                    'data'              => $list_purchase
                ], 200);
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function list_purchase_web(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                // 'id_purchase'   => 'required|exists:purchase_product,ID_PURCHASE'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $list_purchase = PurchaseProduct::select(
                'ID_PURCHASE',
                'ID_USER',
                'NAME_USER',
                'EMAIL_USER',
                'TELP_USER',
                'ALAMAT_USER',
                'TOTQTY_PURCHASE',
                'ORDER_ID',
                'PAYMENT_URL',
                'TOTPAYMENT_PURCHASE',
                'SHIPPING_METHOD',
                'SHIPPING_COST',
                'STATUS_REVIEW_PRODUCT'
            )->get();

            foreach ($list_purchase as $key => $value) {
                $purchase_detail = DB::select("
                    SELECT 
                        pd.ID_PRODUCT,
                        p.NAME_PRODUCT,
                        p.PRICE_PRODUCT,
                        FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                        pd.QTY_PD,
                        FORMAT(pd.QTY_PD*p.WEIGHT_PRODUCT, 2) AS TOTAL_WEIGHT,
                        pd.TOTAL_PRICE,
                        p.PHOTO_PRODUCT,
                        COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = pd.ID_PRODUCT),0) AS RATING_PRODUCT,
                        mc.NAME_CITY 
                    FROM purchase_detail pd, product p, `user` u, md_city mc  
                        WHERE 
                        pd.ID_PRODUCT = p.ID_PRODUCT AND p.ID_USER = u.ID_USER AND u.ID_CITY = mc.ID_CITY  
                        AND 
                        pd.ID_PURCHASE  = '" . $list_purchase[$key]->ID_PURCHASE . "'
                    ");
                $list_purchase[$key]->{'PURCHASE_DETAIL'} = $purchase_detail;
                foreach ($purchase_detail as $key => $value) {
                    $list_img = explode(";", $purchase_detail[$key]->PHOTO_PRODUCT);
                    $purchase_detail[$key]->{'PHOTO_PRODUCT'} = $list_img;
                }
            }

            if ($list_purchase == null) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Data tidak ditemukan!',
                ], 400);
            } else {
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Data berhasil ditemukan!',
                    'data'              => $list_purchase
                ], 200);
            }
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
                'id_purchase'   => 'required|exists:purchase_product,ID_PURCHASE'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            $id = $req->id_purchase;

            $detail = PurchaseProduct::select(
                'ID_PURCHASE',
                'ID_USER',
                'NAME_USER',
                'EMAIL_USER',
                'TELP_USER',
                'ALAMAT_USER',
                'TOTQTY_PURCHASE',
                'ORDER_ID',
                'PAYMENT_URL',
                'TOTPAYMENT_PURCHASE',
                'SHIPPING_METHOD',
                'SHIPPING_COST',
                'STATUS_REVIEW_PRODUCT'
            )->where('ID_PURCHASE', '=', $id)->first();

            $purchase_detail = DB::select("
            SELECT 
                pd.ID_PRODUCT,
                p.NAME_PRODUCT,
                p.PRICE_PRODUCT,
                FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                pd.QTY_PD,
                FORMAT(pd.QTY_PD*p.WEIGHT_PRODUCT, 2) AS TOTAL_WEIGHT,
                pd.TOTAL_PRICE,
                p.PHOTO_PRODUCT,
                COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = pd.ID_PRODUCT),0) AS RATING_PRODUCT,
                mc.NAME_CITY 
            FROM purchase_detail pd, product p, `user` u, md_city mc  
                WHERE 
                pd.ID_PRODUCT = p.ID_PRODUCT AND p.ID_USER = u.ID_USER AND u.ID_CITY = mc.ID_CITY  
                AND 
                pd.ID_PURCHASE  = '" . $id . "'
            ");
            foreach ($purchase_detail as $key => $value) {
                $list_img = explode(";", $purchase_detail[$key]->PHOTO_PRODUCT);
                $purchase_detail[$key]->{'PHOTO_PRODUCT'} = $list_img;
            }

            // $payment_product = PaymentProduct::where('order_id', 'LIKE', '%' . $detail->ORDER_ID . '%')->get();
            $payment_product = DB::select("
                select * from payment_product_1 where order_id like '%" . $detail->ORDER_ID . "%'
            ");

            // foreach ($detail as $key => $value) {
            $detail->{'PURCHASE_DETAIL'} = $purchase_detail;
            $detail->{'PAYMENT_DETAIL'} = $payment_product;
            // }

            if ($detail == null) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Data tidak ditemukan!',
                ], 400);
            } else {
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Data berhasil ditemukan!',
                    'data'              => $detail
                ], 200);
            }
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
                'id_purchase'           => 'required|exists:purchase_product,ID_PURCHASE',
                'evidence_transfer'     => 'required|image',
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

            $pay_Product = PaymentProduct::where('ID_PURCHASE', '=', $req->input("id_purchase"))->first();
            $pay_Product->EVIDENCE_TRANSFER    = Storage::disk('s3')->url($path);
            $pay_Product->TIME_PAYPRODUCT      = date('Y-m-d H:i:s');
            $pay_Product->STATUS_PAYPRODUCT    = 1;
            $pay_Product->save();

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

    public function verif_purchase_product(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_purchase'           => 'required|exists:purchase_product,ID_PURCHASE',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $status_Product = PaymentProduct::where('ID_PURCHASE', '=', $req->input("id_purchase"))->first();
            $status_Product->STATUS_PAYPRODUCT    = 2;
            $status_Product->save();
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
