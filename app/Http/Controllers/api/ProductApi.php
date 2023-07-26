<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\PurchaseDetail;
use DB;

class ProductApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'   => 'required'
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

            $jmlTerjual = PurchaseDetail::where('ID_PRODUCT', '=', $req->input('id_product'))->sum('QTY_PD');

            $products = DB::select("
            SELECT 
                p.ID_PRODUCT,
                p.PHOTO_PRODUCT,
                p.ID_PCAT,
                p.NAME_PRODUCT,
                p.PRICE_PRODUCT,
                FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = p.ID_PRODUCT),0) AS RATING_PRODUCT,
                u.ADDRESS_USER,
                u.NAME_USER,
                u.IMG_USER,
                u.TELP_USER,
                u.ID_PROVINCE,
                u.ID_CITY,
                u.ID_DISTRICT,
                p.STOCK_PRODUCT,
                " . $jmlTerjual . " AS 'JML_TERJUAL',
                p.CONDITION_PRODUCT,
                mpc.NAME_PCAT,
                p.DESC_PRODUCT,
                p.NOTE_PRODUCT 
            FROM product p, `user` u, md_product_category mpc  
            WHERE p.ID_USER = u.ID_USER AND p.ID_PCAT = mpc.ID_PCAT 
            AND p.ID_PRODUCT = " . $req->input('id_product') . "
            ");

            foreach ($products as $key => $value) {
                $list_img = explode(";", $products[$key]->PHOTO_PRODUCT);
                $products[$key]->{'PHOTO_PRODUCT'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $products
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function list_product(Request $req)
    {
        try {
            $query = "";
            $order = "";
            $category = "";
            $id_product_category = "";

            if ($req->input('search') != null) {
                $query .= "AND p.NAME_PRODUCT LIKE '%" . $req->input('search') . "%'";
            }
            if ($req->input('sort') != null) {
                if ($req->input('sort') == 1) {
                    $sort = "ASC";
                }
                if ($req->input('sort') == 2) {
                    $sort = "DESC";
                }
                $order .= "ORDER BY p.ID_PRODUCT " . $sort . "";
            }
            if ($req->input('category') != null) {
                if ($req->input('category') == 1) {
                    $type = "Tandur Market";
                }
                if ($req->input('category') == 2) {
                    $type = "Ground Garden";
                }
                $category .= "AND mpc.CATEGORY = '" . $type . "'";
            }
            if ($req->input('id_product_category') != null) {
                $id_product_category .= "AND mpc.ID_PCAT = " . $req->input('id_product_category') . "";
            }

            $list = DB::select("
            SELECT 
                p.ID_PRODUCT,
                p.NAME_PRODUCT,
                mpc.CATEGORY,
                mpc.NAME_PCAT,
                p.PHOTO_PRODUCT,
                FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                p.PRICE_PRODUCT,
                COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = p.ID_PRODUCT),0) AS RATING_PRODUCT,
                md.NAME_DISTRICT,
                p.IS_ACTIVE 
            FROM product p, `user` u, md_district md, md_product_category mpc 
            WHERE p.ID_USER = u.ID_USER AND  u.ID_DISTRICT = md.ID_DISTRICT AND p.ID_PCAT = mpc.ID_PCAT  
            AND p.IS_ACTIVE = 1
            " . $query . "
            " . $category . "
            " . $id_product_category . "
            " . $order . "  
            ");

            foreach ($list as $key => $value) {
                $list_img = explode(";", $list[$key]->PHOTO_PRODUCT);
                $list[$key]->{'PHOTO_PRODUCT'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $list
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function list_product_web(Request $req)
    {
        try {
            $query = "";
            $order = "";
            $category = "";
            $id_product_category = "";

            if ($req->input('search') != null) {
                $query .= "AND p.NAME_PRODUCT LIKE '%" . $req->input('search') . "%'";
            }
            if ($req->input('sort') != null) {
                if ($req->input('sort') == 1) {
                    $sort = "ASC";
                }
                if ($req->input('sort') == 2) {
                    $sort = "DESC";
                }
                $order .= "ORDER BY p.ID_PRODUCT " . $sort . "";
            }
            if ($req->input('category') != null) {
                if ($req->input('category') == 1) {
                    $type = "Tandur Market";
                }
                if ($req->input('category') == 2) {
                    $type = "Ground Garden";
                }
                $category .= "AND mpc.CATEGORY = '" . $type . "'";
            }
            if ($req->input('id_product_category') != null) {
                $id_product_category .= "AND mpc.ID_PCAT = " . $req->input('id_product_category') . "";
            }

            $list = DB::select("
            SELECT 
                p.ID_PRODUCT,
                p.NAME_PRODUCT,
                mpc.CATEGORY,
                mpc.NAME_PCAT,
                p.PHOTO_PRODUCT,
                FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                p.PRICE_PRODUCT,
                COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = p.ID_PRODUCT),0) AS RATING_PRODUCT,
                md.NAME_DISTRICT,
                p.IS_ACTIVE 
            FROM product p, `user` u, md_district md, md_product_category mpc 
            WHERE p.ID_USER = u.ID_USER AND  u.ID_DISTRICT = md.ID_DISTRICT AND p.ID_PCAT = mpc.ID_PCAT  
            " . $query . "
            " . $category . "
            " . $id_product_category . "
            " . $order . "  
            ");

            foreach ($list as $key => $value) {
                $list_img = explode(";", $list[$key]->PHOTO_PRODUCT);
                $list[$key]->{'PHOTO_PRODUCT'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $list
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function product_user(Request $req)
    {
        try {
            $list = DB::select("
            SELECT 
                p.ID_PRODUCT,
                p.ID_USER,
                p.NAME_PRODUCT,
                mpc.CATEGORY,
                mpc.NAME_PCAT,
                p.PHOTO_PRODUCT,
                FORMAT(p.WEIGHT_PRODUCT, 2) AS WEIGHT_PRODUCT,
                p.PRICE_PRODUCT,
                COALESCE((SELECT SUM(rv.RATING)/COUNT(rv.ID_PRODUCT) FROM `review_product` rv WHERE rv.ID_PRODUCT = p.ID_PRODUCT),0) AS RATING_PRODUCT,
                md.NAME_DISTRICT,
                p.IS_ACTIVE 
            FROM product p, `user` u, md_district md, md_product_category mpc 
            WHERE p.ID_USER = u.ID_USER AND  u.ID_DISTRICT = md.ID_DISTRICT AND p.ID_PCAT = mpc.ID_PCAT  
            AND p.ID_USER = '" . $req->id_user . "'
            ");

            foreach ($list as $key => $value) {
                $list_img = explode(";", $list[$key]->PHOTO_PRODUCT);
                $list[$key]->{'PHOTO_PRODUCT'} = $list_img;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $list
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
                'id_product_category'   => 'required',
                'name_product'          => 'required',
                'weight'                => 'required',
                'stock'                 => 'required',
                'condition'             => 'required',
                'price'                 => 'required',
                'desc'                  => 'required',
                'note'                  => 'required',
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

            $path_gallery1     = ($req->file('gallery_1') == null) ? '-' : $req->file('gallery_1')->store('images', 's3');
            $path_gallery2     = ($req->file('gallery_2') == null) ? '-' : $req->file('gallery_2')->store('images', 's3');
            $path_gallery3     = ($req->file('gallery_3') == null) ? '-' : $req->file('gallery_3')->store('images', 's3');
            $path_gallery4     = ($req->file('gallery_4') == null) ? '-' : $req->file('gallery_4')->store('images', 's3');
            $path_gallery5     = ($req->file('gallery_5') == null) ? '-' : $req->file('gallery_5')->store('images', 's3');

            $path_gallery1     = ($path_gallery1 == '-' ? '-' : Storage::disk('s3')->url($path_gallery1));
            $path_gallery2     = ($path_gallery2 == '-' ? '-' : Storage::disk('s3')->url($path_gallery2));
            $path_gallery3     = ($path_gallery3 == '-' ? '-' : Storage::disk('s3')->url($path_gallery3));
            $path_gallery4     = ($path_gallery4 == '-' ? '-' : Storage::disk('s3')->url($path_gallery4));
            $path_gallery5     = ($path_gallery5 == '-' ? '-' : Storage::disk('s3')->url($path_gallery5));

            $products = new Product();

            $products->ID_USER          = $req->input('id_user');
            $products->ID_PCAT          = $req->input('id_product_category');
            $products->ID_PCON          = 1;
            $products->NAME_PRODUCT     = $req->input('name_product');
            $products->WEIGHT_PRODUCT     = $req->input('weight');
            $products->STOCK_PRODUCT    = $req->input('stock');
            $products->CONDITION_PRODUCT = $req->input('condition');
            $products->PRICE_PRODUCT    = $req->input('price');
            $products->DESC_PRODUCT     = $req->input('desc');
            $products->NOTE_PRODUCT     = $req->input('note');
            $products->PHOTO_PRODUCT    = $path_gallery1 . ";" . $path_gallery2 . ";" . $path_gallery3 . ";" . $path_gallery4 . ";" . $path_gallery5;
            $products->RATING_PRODUCT   = 5;
            $products->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => ['id_product' => $products->ID_PRODUCT]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function update(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'   => 'required',
                'id_product_category'   => 'required',
                'name_product'          => 'required',
                'weight'                => 'required',
                'stock'                 => 'required',
                'condition'             => 'required',
                'price'                 => 'required',
                'desc'                  => 'required',
                'note'                  => 'required',
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

            $path_gallery1     = ($req->file('gallery_1') == null) ? '-' : $req->file('gallery_1')->store('images', 's3');
            $path_gallery2     = ($req->file('gallery_2') == null) ? '-' : $req->file('gallery_2')->store('images', 's3');
            $path_gallery3     = ($req->file('gallery_3') == null) ? '-' : $req->file('gallery_3')->store('images', 's3');
            $path_gallery4     = ($req->file('gallery_4') == null) ? '-' : $req->file('gallery_4')->store('images', 's3');
            $path_gallery5     = ($req->file('gallery_5') == null) ? '-' : $req->file('gallery_5')->store('images', 's3');

            $products = Product::find($req->input('id_product'));

            // if ($products->ID_USER != $req->input('id_user')) {
            //     return response([
            //         'status_code'       => 400,
            //         'status_message'    => "Anda tidak berhak mengupdate!",
            //     ], 400);
            // }

            $explode_image = explode(";", $products->PHOTO_PRODUCT);

            $path_gallery1     = ($path_gallery1 == '-' ? $explode_image[0] : Storage::disk('s3')->url($path_gallery1));
            $path_gallery2     = ($path_gallery2 == '-' ? $explode_image[1] : Storage::disk('s3')->url($path_gallery2));
            $path_gallery3     = ($path_gallery3 == '-' ? $explode_image[2] : Storage::disk('s3')->url($path_gallery3));
            $path_gallery4     = ($path_gallery4 == '-' ? $explode_image[3] : Storage::disk('s3')->url($path_gallery4));
            $path_gallery5     = ($path_gallery5 == '-' ? $explode_image[4] : Storage::disk('s3')->url($path_gallery5));

            $products->ID_PCAT           = $req->input('id_product_category');
            $products->ID_PCON           = 1;
            $products->NAME_PRODUCT      = $req->input('name_product');
            $products->WEIGHT_PRODUCT    = $req->input('weight');
            $products->STOCK_PRODUCT     = $req->input('stock');
            $products->CONDITION_PRODUCT = $req->input('condition');
            $products->PRICE_PRODUCT     = $req->input('price');
            $products->DESC_PRODUCT      = $req->input('desc');
            $products->NOTE_PRODUCT      = $req->input('note');
            $products->PHOTO_PRODUCT     = $path_gallery1 . ";" . $path_gallery2 . ";" . $path_gallery3 . ";" . $path_gallery4 . ";" . $path_gallery5;
            $products->RATING_PRODUCT    = 5;
            $products->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil diupdate!',
                "data"              => ['id_product' => $products->ID_PRODUCT]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function delete(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'        => 'required|exists:product,ID_PRODUCT'
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

            $product = Product::find($req->input('id_product'));
            $product->delete();

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

    public function verify(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_product'            => 'required',
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

            $products = Product::find($req->input('id_product'));

            $products->IS_ACTIVE    = 1;
            $products->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Produk terverifikasi!',
                "data"              => ['id_product' => $products->ID_PRODUCT]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
