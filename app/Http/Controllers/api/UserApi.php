<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Users;
use Firebase\JWT\JWT;

class UserApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $users = Users::all();
            $id_user = $req->input('user_id');

            if ($id_user != null) {
                $users = Users::where('ID_USER', '=', $id_user)->get();
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $users
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
                'id_district'   => 'required',
                'id_city'       => 'required',
                'id_province'   => 'required',
                'email'         => 'required',
                'ktp'           => 'required',
                'name'          => 'required',
                'password'      => 'required',
                'telp'          => 'required',
                'address'       => 'required',
                // 'long'          => 'required',
                // 'lat'           => 'required',
                'img_user'      => 'required|image',
                'img_ktp'       => 'required|image',
                'img_ktp_selfie' => 'required|image',
                // 'token'         => 'required',
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
            $otp        = rand(100000, 999999);
            $path       = $req->file('img_user')->store('images', 's3');
            $path_ktp   = $req->file('img_ktp')->store('images', 's3');
            $path_selfie = $req->file('img_ktp_selfie')->store('images', 's3');

            $user = new Users();
            $user->ID_USER      = substr(md5(time() . rand(10, 99)), 0, 8);
            $user->ID_DISTRICT  = $req->input('id_district');
            $user->ID_CITY      = $req->input('id_city');
            $user->ID_PROVINCE  = $req->input('id_province');
            $user->EMAIL_USER   = $req->input('email');
            $user->KTP_USER     = $req->input('ktp');
            $user->NAME_USER    = $req->input('name');
            $user->PASSWORD_USER = hash('sha256', md5($req->input('password')));
            $user->TELP_USER    = $req->input('telp');
            $user->ADDRESS_USER = $req->input('address');
            // $user->LONG_USER    = $req->input('long');
            // $user->LAT_USER     = $req->input('lat');
            $user->IMG_USER     = Storage::disk('s3')->url($path);
            $user->IMG_KTP      = Storage::disk('s3')->url($path_ktp);
            $user->IMG_SELFIE_KTP = Storage::disk('s3')->url($path_selfie);
            // $user->TOKEN_USER   = $req->input('token');
            $user->OTPVERIF_USER = $otp;
            $start = date('Y-m-d H:i:s');
            $user->OTPEXP_USER  = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($start)));

            try {
                $user->save();
                return response([
                    "status_code"       => 200,
                    "status_message"    => 'Data berhasil disimpan!',
                    "data"              => ['id_user' => $user->ID_USER]
                ], 200);
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == '1062') {
                    return response([
                        "status_code"       => 400,
                        "status_message"    => 'Email telah telah digunakan!',
                    ], 400);
                }
            }
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
                'user_id'       => 'required|exists:user,ID_USER',
                'id_province'   => 'required',
                'id_city'       => 'required',
                'id_district'   => 'required',
                'ktp'           => 'required',
                'email'           => 'required',
                'name'          => 'required',
                // 'password'      => 'required',
                'telp'          => 'required',
                'address'       => 'required',
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

            $path       = ($req->file('img_user') == null) ? '-' : $req->file('img_user')->store('images', 's3');
            $path_ktp   = ($req->file('img_ktp') == null) ? '-' : $req->file('img_ktp')->store('images', 's3');
            $path_selfie = ($req->file('img_ktp_selfie') == null) ? '-' : $req->file('img_ktp_selfie')->store('images', 's3');

            $user = Users::find($req->input('user_id'));


            $path           = ($path == '-' ? $user->IMG_USER : Storage::disk('s3')->url($path));
            $path_ktp       = ($path_ktp == '-' ? $user->IMG_KTP : Storage::disk('s3')->url($path_ktp));
            $path_selfie    = ($path_selfie == '-' ? $user->IMG_SELFIE_KTP : Storage::disk('s3')->url($path_selfie));

            $user->ID_DISTRICT  = $req->input('id_district');
            $user->ID_CITY      = $req->input('id_city');
            $user->ID_PROVINCE  = $req->input('id_province');
            $user->EMAIL_USER   = $req->input('email');
            $user->KTP_USER     = $req->input('ktp');
            $user->NAME_USER    = $req->input('name');
            $user->PASSWORD_USER = ($req->file('password') == null) ? $user->PASSWORD_USER : hash('sha256', md5($req->input('password')));
            $user->TELP_USER    = $req->input('telp');
            $user->ADDRESS_USER = $req->input('address');
            $user->IMG_USER     = $path;
            $user->IMG_KTP      = $path_ktp;
            $user->IMG_SELFIE_KTP = $path_selfie;
            $user->save();

            $jwt = JWT::encode($user->toArray(), env("JWT_SECRET_KEY"), env("JWT_ALGO"));

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil diubah!',
                "data"              => ['jwt' => $jwt]
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
                'user_id'        => 'required|exists:user,ID_USER'
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

            $user = Users::find($req->input('user_id'));
            $user->delete();

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

    public function update_token_user(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'user_token'        => 'required'
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

            $user = Users::find($req->input('id_user'));

            $user->TOKEN_USER = $req->user_token;
            $user->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Token has updated!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
