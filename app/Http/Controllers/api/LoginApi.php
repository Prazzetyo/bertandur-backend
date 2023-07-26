<?php

namespace App\Http\Controllers\api;

use App\Models\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Firebase\JWT\JWT;
use DB;

class LoginApi extends Controller
{
    public function login(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'email'  => 'required',
                'password'  => 'required'
            ], [
                'required' => 'Parameter :attribute tidak boleh kosong!'
            ]);
    
            if($validator->fails()){
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
    
            $user = Users::where([
                        ['EMAIL_USER', '=', $req->input('email')],
                        ['PASSWORD_USER', '=', hash('sha256', md5($req->input('password')))]
                    ])->first();
            
            $user_jwt = DB::select("
                SELECT
                    u.*,
                    md.NAME_DISTRICT,
                    mc.NAME_CITY,
                    mp.NAME_PROVINCE 
                FROM `user` u, md_province mp, md_city mc, md_district md
                WHERE 
                u.ID_PROVINCE = mp.ID_PROVINCE AND u.ID_CITY = mc.ID_CITY AND u.ID_DISTRICT = md.ID_DISTRICT 
                AND u.EMAIL_USER = '".$req->input('email')."'
                AND u.PASSWORD_USER = '".hash('sha256', md5($req->input('password')))."'
                ");

            $array_user = json_decode(json_encode($user_jwt), true);
            
            if($user == null){
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Username atau password salah!',
                ], 400);
            }
            else if($user->ISVERIF_USER == 0) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'User belum aktivasi!',
                ], 400);
            }
            else{
                $user->sess_key = md5(rand(100, 999));
                $user->save();
                // $jwt = JWT::encode($array_user, env("JWT_SECRET_KEY"), env("JWT_ALGO"));
                $jwt = JWT::encode($user->toArray(), env("JWT_SECRET_KEY"), env("JWT_ALGO"));
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Selamat anda berhasil login!',
                    'data'              => ['jwt' => $jwt],
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
