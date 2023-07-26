<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewLand;
use App\Models\Rent;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewLandApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_land'   => 'required|exists:land,ID_LAND',
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

            $review_land = DB::table('review_land')
                ->crossJoin('user')
                ->select('review_land.ID_REVIEW_LAND', 'review_land.ID_USER', 'user.NAME_USER', 'user.IMG_USER', 'review_land.ID_LAND', 'review_land.RATING', 'review_land.REVIEW_TITLE', 'review_land.REVIEW_CONTENT', 'review_land.DATE_REVIEW')
                ->where('review_land.ID_USER', '=', DB::raw('user.ID_USER'))
                ->where('review_land.ID_LAND', '=', $req->input('id_land'))
                ->get();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $review_land
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
                'id_land'           => 'required|exists:land,ID_LAND',
                'rating'            => 'required',
                'review_title'      => 'required',
                'review_content'    => 'required',
                'id_rent'           => 'required',
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

            $review_land = new ReviewLand();
            $review_land->ID_USER           = $req->input('id_user');
            $review_land->ID_LAND           = $req->input('id_land');
            $review_land->RATING            = $req->input('rating');
            $review_land->REVIEW_TITLE      = $req->input('review_title');
            $review_land->REVIEW_CONTENT    = $req->input('review_content');
            $review_land->DATE_REVIEW       = date('Y-m-d H:i:s');

            if ($review_land->save()) {
                $rent = Rent::find($req->input('id_rent'));
                $rent->STATUS_REVIEW_LAND    = 1;
                $rent->save();

                return response([
                    "status_code"       => 200,
                    "status_message"    => 'Data berhasil disimpan!',
                    "data"              => ['id_review_land' => $review_land->ID_REVIEW_LAND]
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
