<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LogMessage;
use App\Models\Users;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class LogMessageApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $idUser = Users::select('ID_USER', 'TOKEN_USER')->where('ID_USER', '=', '' . $req->id_user . '')->first();

            $data = LogMessage::where([
                ['ID_USER', '=', $idUser->ID_USER],
                ['STATUS_MESSAGE', '=', 0]
            ])->get();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $data
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
                'id_log'       => 'required|exists:log_message,ID_LOG'
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

            $logs = LogMessage::find($req->input('id_log'));
            $logs->STATUS_MESSAGE   = 1;
            $logs->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Status berhasil diupdate!',
                "data"              => ['id_log' => $logs->ID_LOG]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }
}
