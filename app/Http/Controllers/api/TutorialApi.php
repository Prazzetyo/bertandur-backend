<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Tutorial;
use App\Models\TutorialDetail;

class TutorialApi extends Controller
{
    public function index_tutorial(Request $req)
    {
        try {
            $tutorials = Tutorial::where('TITLE_TUTORIAL', 'LIKE', '%' . $req->input('search') . '%')->get();

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $tutorials
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function store_tutorial(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'title_tutorial'    => 'required',
                'desc'              => 'required',
                'image'             => 'required|image',
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

            $path = $req->file('image')->store('images', 's3');
            $tutorials = new Tutorial();

            $tutorials->ID_USER         = $req->input('id_user');
            $tutorials->TITLE_TUTORIAL  = $req->input('title_tutorial');
            $tutorials->DESC_TUTORIAL   = $req->input('desc');
            $tutorials->URLIMG_TUTORIAL = Storage::disk('s3')->url($path);
            $tutorials->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => ['id_tutorial' => $tutorials->ID_TUTORIAL]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function update_tutorial(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_tutorial'       => 'required|exists:tutorial,ID_TUTORIAL',
                'title_tutorial'    => 'required',
                'desc'              => 'required',
                'image'             => 'required|image',
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

            $path = $req->file('image')->store('images', 's3');
            $tutorials = Tutorial::find($req->input('id_tutorial'));

            $tutorials->ID_USER         = $req->input('id_user');
            $tutorials->TITLE_TUTORIAL  = $req->input('title_tutorial');
            $tutorials->DESC_TUTORIAL   = $req->input('desc');
            $tutorials->URLIMG_TUTORIAL = Storage::disk('s3')->url($path);
            $tutorials->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => ['id_tutorial' => $tutorials->ID_TUTORIAL]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function delete_tutorial(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_tutorial'        => 'required|exists:tutorial,ID_TUTORIAL'
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

            $tutorial_details = TutorialDetail::where('ID_TUTORIAL', '=', $req->input('id_tutorial'));
            $tutorial_details->delete();

            $tutorials = Tutorial::find($req->input('id_tutorial'));
            $tutorials->delete();

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

    public function index_tutorial_detail(Request $req)
    {
        try {
            $tutorial_details = TutorialDetail::all();

            if ($req->id_tutorial != null) {
                $tutorial_details = TutorialDetail::where('ID_TUTORIAL', '=', $req->id_tutorial)->get();
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $tutorial_details
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function store_tutorial_detail(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_tutorial'       => 'required|exists:tutorial,ID_TUTORIAL',
                'title'             => 'required',
                'desc'              => 'required',
                'url_video'         => 'required',
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

            $tutorial_details = new TutorialDetail();

            $tutorial_details->ID_TUTORIAL      = $req->input('id_tutorial');
            $tutorial_details->TITLE_TD         = $req->input('title');
            $tutorial_details->DESC_TD          = $req->input('desc');
            $tutorial_details->URLVIDEO_TD      = $req->input('url_video');
            $tutorial_details->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => ['id_tutorial' => $tutorial_details->ID_TD]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function update_tutorial_detail(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_td'             => 'required|exists:tutorial_detail,ID_TD',
                'id_tutorial'       => 'required|exists:tutorial,ID_TUTORIAL',
                'title'             => 'required',
                'desc'              => 'required',
                'url_video'         => 'required',
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

            $tutorial_details = TutorialDetail::find($req->input('id_td'));

            $tutorial_details->ID_TUTORIAL      = $req->input('id_tutorial');
            $tutorial_details->TITLE_TD         = $req->input('title');
            $tutorial_details->DESC_TD          = $req->input('desc');
            $tutorial_details->URLVIDEO_TD      = $req->input('url_video');
            $tutorial_details->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil diupdate!',
                "data"              => ['id_tutorial' => $tutorial_details->ID_TD]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function delete_tutorial_detail(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_td'     => 'required|exists:tutorial_detail,ID_TD'
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

            $tutorial_details = TutorialDetail::find($req->input('id_td'));
            $tutorial_details->delete();

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
