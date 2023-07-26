<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductCategory;

class ProductCategoryApi extends Controller
{
    public function index(Request $req)
    {
        try {
            if ($req->input('category') != null) {
                if ($req->input('category') == 1) {
                    $val = "Tandur Market";
                }

                if ($req->input('category') == 2) {
                    $val = "Ground Garden";
                }

                $product_category = ProductCategory::where('CATEGORY', '=', $val)->get();
            }else{
                $product_category = ProductCategory::all();
            }
            
            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $product_category
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
