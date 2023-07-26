<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductWishlist;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class ProductWishlistApi extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        try {
            $product_wishlist = ProductWishlist::where('ID_USER', $req->id_user)->get();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $product_wishlist
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'    => 'required|exists:product,ID_PRODUCT',
                'id_user'    => 'required|exists:user,ID_USER'
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

            $checkWish = ProductWishlist::where([
                ['ID_USER', '=', $req->input('id_user')],
                ['ID_PRODUCT', '=', $req->input('id_product')]
            ])->first();

            if ($checkWish === null) {
                $product_wishlist = new ProductWishlist();
                $product_wishlist->ID_USER    = $req->input('id_user');
                $product_wishlist->ID_PRODUCT = $req->input('id_product');
                $product_wishlist->save();

                return response([
                    "status_code"       => 200,
                    "status_message"    => 'Data berhasil disimpan!',
                    "data"              => ['ID_PW' => $product_wishlist->ID_PW]
                ], 200);
            } else {
                return response([
                    "status_code"       => 400,
                    "status_message"    => 'Wishlist sudah ada'
                ], 400);
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_pw'        => 'required|exists:product_wishlist,ID_PW'
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

            $product_wishlist = ProductWishlist::find($req->input('id_pw'));
            $product_wishlist->delete();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil dihapus!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
