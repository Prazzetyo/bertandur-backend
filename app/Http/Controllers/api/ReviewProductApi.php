<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewProduct;
use App\Models\PurchaseProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewProductApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'   => 'required|exists:product,ID_PRODUCT',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
                'numeric'   => 'Parameter :attribute harus bertipe angka!',
                'exists'    => 'Parameter :attribute tidak ditemukan!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $review_product = DB::table('review_product')
                ->crossJoin('user')
                ->select('review_product.ID_REVIEW_PRODUCT', 'review_product.ID_USER', 'user.NAME_USER', 'user.IMG_USER', 'review_product.ID_PRODUCT', 'review_product.RATING', 'review_product.REVIEW_TITLE', 'review_product.REVIEW_CONTENT', 'review_product.DATE_REVIEW')
                ->where('review_product.ID_USER', '=', DB::raw('user.ID_USER'))
                ->where('review_product.ID_PRODUCT', '=', $req->input('id_product'))
                ->get();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $review_product
            ], 200);
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
                'id_product'        => 'required|exists:product,ID_PRODUCT',
                'rating'            => 'required',
                'review_title'      => 'required',
                'review_content'    => 'required',
                'id_purchase'       => 'required',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
                'numeric'   => 'Parameter :attribute harus bertipe angka!',
                'exists'    => 'Parameter :attribute tidak ditemukan!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $review_product = new ReviewProduct();

            $review_product->ID_USER                = $req->input('id_user');
            $review_product->ID_PRODUCT             = $req->input('id_product');
            $review_product->RATING                 = $req->input('rating');
            $review_product->REVIEW_TITLE           = $req->input('review_title');
            $review_product->REVIEW_CONTENT         = $req->input('review_content');
            $review_product->DATE_REVIEW            = date('Y-m-d H:i:s');

            if ($review_product->save()) {
                $purchase = PurchaseProduct::find($req->input('id_purchase'));
                $purchase->STATUS_REVIEW_PRODUCT    = 1;
                $purchase->save();

                return response([
                    "status_code"       => 200,
                    "status_message"    => 'Data berhasil disimpan!',
                    "data"              => ['id_review_product' => $review_product->ID_REVIEW_PRODUCT]
                ], 200);
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
