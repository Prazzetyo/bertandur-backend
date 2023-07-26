<?php

namespace App\Http\Controllers\api;

use App\Models\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class RegisterApi extends Controller
{
    public function register(Request $req)
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
                'img_ktp_selfie'=> 'required|image',
                // 'token'         => 'required',
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
                'numeric'   => 'Parameter :attribute harus bertipe angka!',
                'exists'    => 'Parameter :attribute tidak ditemukan!',
            ]);
    
            if($validator->fails()){
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            $otp        = rand(100000,999999);
            $path       = $req->file('img_user')->store('images', 's3');
            $path_ktp   = $req->file('img_ktp')->store('images', 's3');
            $path_selfie= $req->file('img_ktp_selfie')->store('images', 's3');

            $user = new Users();
            $user->ID_USER      = substr(md5(time().rand(10, 99)), 0, 8);
            $user->ID_DISTRICT  = $req->input('id_district');
            $user->ID_CITY      = $req->input('id_city');
            $user->ID_PROVINCE  = $req->input('id_province');
            $user->EMAIL_USER   = $req->input('email');
            $user->KTP_USER     = $req->input('ktp');
            $user->NAME_USER    = $req->input('name');
            $user->PASSWORD_USER= hash('sha256', md5($req->input('password')));
            $user->TELP_USER    = $req->input('telp');
            $user->ADDRESS_USER = $req->input('address');
            // $user->LONG_USER    = $req->input('long');
            // $user->LAT_USER     = $req->input('lat');
            $user->IMG_USER     = Storage::disk('s3')->url($path);
            $user->IMG_KTP      = Storage::disk('s3')->url($path_ktp);
            $user->IMG_SELFIE_KTP= Storage::disk('s3')->url($path_selfie);
            // $user->TOKEN_USER   = $req->input('token');
            $user->OTPVERIF_USER= $otp;
            $start = date('Y-m-d H:i:s');
            $user->OTPEXP_USER  = date('Y-m-d H:i:s',strtotime('+24 hour',strtotime($start)));

            try {
                $users = Users::where('EMAIL_USER', '=', $req->input('email'))->first();
                if ($users === null) {
                    $user->save();
                    $isi_email['name'] = $req->input('name');
                    $isi_email['title'] = 'Tandur Urban Farming';
                    $isi_email['body'] = 'Selamat Datang di Tandur';
                    $isi_email['otp'] = $otp;
                    $isi_email['email'] = $req->input('email');
                    // $isi_email['email'] = Crypt::encryptString($req->input('email'));
                    Mail::to($req->input('email'))->send(new SendEmail($isi_email));
                    return response([
                        "status_code"       => 200,
                        "status_message"    => 'Data berhasil disimpan!',
                        "data"              => ['id_user' => $user->ID_USER]
                    ], 200);
                }else{
                    return response([
                        "status_code"       => 400,
                        "status_message"    => 'Email telah telah digunakan!',
                    ], 400);
                }

                
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if($errorCode == '1062'){
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

    public function verify(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'email'   => 'required',
                'otp'   => 'required'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);
    
            if($validator->fails()){
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            
		    // $decrypted_email = Crypt::decryptString($req->email);
            $decrypted_email = $req->email;
            $otp = $req->otp;

            $user = Users::where('EMAIL_USER', '=', $decrypted_email)
                ->where('ISVERIF_USER', '=', 0)
                ->first();

            // dd($user);
            if ($user == null) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => 'User telah terverifikasi sebelumnya!'
                ], 400);
            }else {
                if (date('Y-m-d H:i:s') > $user->OTPEXP_USER) {
                    return response([
                        "status_code"       => 400,
                        "status_message"    => 'Kode OTP kadaluwarsa, kirim kode OTP kembali!'
                    ], 400);
                }else {
                    if ($otp == $user->OTPVERIF_USER) {
                        $user->ISVERIF_USER = 1;
                        $user->email_verified_at = date('Y-m-d H:i:s');
                        $user->save();
        
                        return response([
                            "status_code"       => 200,
                            "status_message"    => 'Berhasil Verifikasi!'
                        ], 200);
                    }else{
                        return response([
                            "status_code"       => 400,
                            "status_message"    => 'Kode OTP tidak sama, kirim kode OTP kembali!'
                        ], 400);
                    }
                }
            }
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function resendOTP(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'email'   => 'required'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);
    
            if($validator->fails()){
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $user = Users::where('EMAIL_USER', '=', $req->email)->first();
            $otp = rand(100000,999999);
            $user->OTPVERIF_USER = $otp;
            $start = date('Y-m-d H:i:s');
            $user->OTPEXP_USER  = date('Y-m-d H:i:s',strtotime('+5 minutes',strtotime($start)));
            $user->save();

            $isi_email['name'] = $req->input('name');
            $isi_email['title'] = 'Tandur Urban Farming';
            $isi_email['body'] = 'Kode OTP Baru';
            $isi_email['otp'] = $otp;
            $isi_email['email'] = Crypt::encryptString($req->input('email'));
            
            Mail::to($req->input('email'))->send(new SendEmail($isi_email));

            return response([
                "status_code"       => 200,
                "status_message"    => 'OTP sudah update!'
            ], 200); 
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
