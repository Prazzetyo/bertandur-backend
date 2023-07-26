<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckAuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // dump($request->input());
        // dump("tes");
        try {
            $jwt = JWT::decode($request->header('Authorization'), new Key(env('JWT_SECRET_KEY'), env('JWT_ALGO')));
            $user = Users::find($jwt->ID_USER);
            if ($user->sess_key != $jwt->sess_key) {
                return response([
                    'status_code'       => 401,
                    'status_message'    => 'Autentikasi anda gagal, harap login kembali!',
                ], 401);
            }

            $request->request->add([
                'id_user' => $jwt->ID_USER,
                'name_user' => $jwt->NAME_USER,
                'email_user' => $jwt->EMAIL_USER,
                'telp_user' => $jwt->TELP_USER,
                'long' => $jwt->LONG_USER,
                'lat' => $jwt->LAT_USER,
                'token' => $jwt->TOKEN_USER
            ]);
            return $next($request);
        } catch (Exception $exp) {
            return response([
                'status_code'       => 401,
                'status_message'    => 'Anda belum login!'
            ], 401);
        }
    }
}
