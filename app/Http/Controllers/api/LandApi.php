<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Land;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Exceptions\HttpResponseException;
use DB;

class LandApi extends Controller
{
    public function index(Request $req)
    {
        try {
            $search = "";
            $order = "";
            $distance = "";

            $lon = ($req->input('longtitude') == null) ? 0 : $req->input('longtitude');
            $lat = ($req->input('latitude') == null) ? 0 : $req->input('latitude');

            $distance = ",
                " . DB::raw('6371 * acos(cos(radians(' . $lat . ')) 
                * cos(radians(l.LAT_LAND)) 
                * cos(radians(l.LONG_LAND) - radians(' . $lon . ')) 
                + sin(radians(' . $lat . ')) 
                * sin(radians(l.LAT_LAND))) AS DISTANCE') . "";

            if ($req->input('search') != null) {
                $search .= "AND l.NAME_LAND LIKE '%" . $req->input('search') . "%'";
            }
            if ($req->input('sort') != null) {
                if ($req->input('sort') == 1) {
                    $sort = "DESC";
                    $field = "l.REGISTERAT_LAND";
                }
                if ($req->input('sort') == 2) {
                    $sort = "ASC";
                    $field = "l.PRICE_LAND";
                }
                if ($req->input('sort') == 3) {
                    $sort = "DESC";
                    $field = "l.PRICE_LAND";
                }
                if ($req->input('sort') == 4) {
                    $sort = "ASC";
                    $field = "DISTANCE";
                }

                $order .= "ORDER BY " . $field . " " . $sort . "";
            }

            $land = DB::select("
            SELECT 
                l.ID_LAND,
                l.NAME_LAND,
                l.DESC_LAND,
                l.LOCATION_LAND,
                l.DESCDOC_LAND,
                l.OWNER_USER,
                l.OWNKTP_LAND,
                l.NOCERTIFICATE_LAND,
                l.WIDTH_LAND,
                l.LENGTH_LAND,
                l.PRICE_LAND,
                l.FACILITY_LAND,
                COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = l.ID_LAND), 0) AS RATING_LAND,
                l.IS_ACTIVE,
                l.PROVINCE_LAND,
                mp.NAME_PROVINCE,
                l.CITY_LAND,
                mc.NAME_CITY,
                l.DISTRICT_LAND,
                md.NAME_DISTRICT,
                l.URLGALLERY_LAND,
                l.URLDOC_LAND,
                l.LONG_LAND,
                l.LAT_LAND
                " . $distance . "
            FROM land l, md_province mp, md_city mc, md_district md 
            WHERE l.PROVINCE_LAND = mp.ID_PROVINCE AND l.CITY_LAND = mc.ID_CITY AND l.DISTRICT_LAND = md.ID_DISTRICT
            AND IS_ACTIVE = 1
            " . $search . "
            " . $order . " 
            ");

            foreach ($land as $key => $value) {
                $list_img = explode(";", $land[$key]->URLGALLERY_LAND);
                $land[$key]->{'URLGALLERY_LAND'} = $list_img;

                $list_doc = explode(";", $land[$key]->URLDOC_LAND);
                $land[$key]->{'URLDOC_LAND'} = $list_doc;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $land
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function land_user(Request $req)
    {
        try {
            $land = DB::select("
            SELECT 
                l.ID_LAND,
                l.NAME_LAND,
                l.DESC_LAND,
                l.LOCATION_LAND,
                l.DESCDOC_LAND,
                l.OWNER_USER,
                l.OWNKTP_LAND,
                l.NOCERTIFICATE_LAND,
                l.WIDTH_LAND,
                l.LENGTH_LAND,
                l.PRICE_LAND,
                l.FACILITY_LAND,
                COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = l.ID_LAND), 0) AS RATING_LAND,
                l.IS_ACTIVE,
                l.PROVINCE_LAND,
                mp.NAME_PROVINCE,
                l.CITY_LAND,
                mc.NAME_CITY,
                l.DISTRICT_LAND,
                md.NAME_DISTRICT,
                l.URLGALLERY_LAND,
                l.URLDOC_LAND,
                l.LONG_LAND,
                l.LAT_LAND,
                l.IS_ACTIVE
            FROM land l, md_province mp, md_city mc, md_district md 
            WHERE l.PROVINCE_LAND = mp.ID_PROVINCE AND l.CITY_LAND = mc.ID_CITY AND l.DISTRICT_LAND = md.ID_DISTRICT 
            AND l.OWNER_USER = '" . $req->id_user . "'
            ");

            foreach ($land as $key => $value) {
                $list_img = explode(";", $land[$key]->URLGALLERY_LAND);
                $land[$key]->{'URLGALLERY_LAND'} = $list_img;

                $list_doc = explode(";", $land[$key]->URLDOC_LAND);
                $land[$key]->{'URLDOC_LAND'} = $list_doc;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $land
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function index_web(Request $req)
    {
        try {
            $search = "";
            $order = "";
            $distance = "";

            $lon = ($req->input('longtitude') == null) ? 0 : $req->input('longtitude');
            $lat = ($req->input('latitude') == null) ? 0 : $req->input('latitude');

            $distance = ",
                " . DB::raw('6371 * acos(cos(radians(' . $lat . ')) 
                * cos(radians(l.LAT_LAND)) 
                * cos(radians(l.LONG_LAND) - radians(' . $lon . ')) 
                + sin(radians(' . $lat . ')) 
                * sin(radians(l.LAT_LAND))) AS DISTANCE') . "";

            if ($req->input('search') != null) {
                $search .= "AND l.NAME_LAND LIKE '%" . $req->input('search') . "%'";
            }
            if ($req->input('sort') != null) {
                if ($req->input('sort') == 1) {
                    $sort = "DESC";
                    $field = "l.REGISTERAT_LAND";
                }
                if ($req->input('sort') == 2) {
                    $sort = "ASC";
                    $field = "l.PRICE_LAND";
                }
                if ($req->input('sort') == 3) {
                    $sort = "DESC";
                    $field = "l.PRICE_LAND";
                }
                if ($req->input('sort') == 4) {
                    $sort = "ASC";
                    $field = "DISTANCE";
                }

                $order .= "ORDER BY " . $field . " " . $sort . "";
            }

            $land = DB::select("
            SELECT 
                l.ID_LAND,
                l.NAME_LAND,
                l.DESC_LAND,
                l.LOCATION_LAND,
                l.DESCDOC_LAND,
                l.OWNER_USER,
                l.OWNKTP_LAND,
                l.NOCERTIFICATE_LAND,
                l.WIDTH_LAND,
                l.LENGTH_LAND,
                l.PRICE_LAND,
                l.FACILITY_LAND,
                COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = l.ID_LAND), 0) AS RATING_LAND,
                l.IS_ACTIVE,
                l.PROVINCE_LAND,
                mp.NAME_PROVINCE,
                l.CITY_LAND,
                mc.NAME_CITY,
                l.DISTRICT_LAND,
                md.NAME_DISTRICT,
                l.URLGALLERY_LAND,
                l.URLDOC_LAND,
                l.LONG_LAND,
                l.LAT_LAND
                " . $distance . "
            FROM land l, md_province mp, md_city mc, md_district md 
            WHERE l.PROVINCE_LAND = mp.ID_PROVINCE AND l.CITY_LAND = mc.ID_CITY AND l.DISTRICT_LAND = md.ID_DISTRICT
            " . $search . "
            " . $order . " 
            ");

            foreach ($land as $key => $value) {
                $list_img = explode(";", $land[$key]->URLGALLERY_LAND);
                $land[$key]->{'URLGALLERY_LAND'} = $list_img;

                $list_doc = explode(";", $land[$key]->URLDOC_LAND);
                $land[$key]->{'URLDOC_LAND'} = $list_doc;
            }

            return response([
                'status_code'       => 200,
                'status_message'    => 'Data berhasil diambil!',
                'data'              => $land
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function detail(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_land'   => 'required'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }
            $id = $req->id_land;
            $land = DB::select("
            SELECT 
                l.ID_LAND,
                l.NAME_LAND,
                COALESCE((SELECT SUM(rl.RATING)/COUNT(rl.ID_LAND) FROM `review_land` rl WHERE rl.ID_LAND = l.ID_LAND), 0) AS RATING_LAND,
                l.ADDRESS_LAND,
                l.PROVINCE_LAND AS ID_PROVINCE,
                l.CITY_LAND AS ID_CITY,
                l.DISTRICT_LAND AS ID_DISTRICT,
                l.LOCATION_LAND,
                l.NOCERTIFICATE_LAND,
                l.OWNKTP_LAND,
                l.DESC_LAND,
                l.OWNNAME_LAND,
                u.IMG_USER,
                u.TELP_USER,
                l.PRICE_LAND,
                l.FACILITY_LAND,
                l.URLGALLERY_LAND,
                l.URLDOC_LAND,
                l.WIDTH_LAND,
                l.LENGTH_LAND,
                l.REGISTERAT_LAND,
                l.RULE_LAND,
                l.LONG_LAND,
                l.LAT_LAND,
                l.IS_ACTIVE
            FROM land l, `user` u 
            WHERE 
                l.OWNER_USER = u.ID_USER
                AND l.ID_LAND = '" . $id . "'
            ");

            foreach ($land as $key => $value) {
                $list_img = explode(";", $land[$key]->URLGALLERY_LAND);
                $land[$key]->{'URLGALLERY_LAND'} = $list_img;

                $list_doc = explode(";", $land[$key]->URLDOC_LAND);
                $land[$key]->{'URLDOC_LAND'} = $list_doc;
            }

            if ($land == null) {
                return response([
                    'status_code'       => 400,
                    'status_message'    => 'Data tidak ditemukan!',
                ], 400);
            } else {
                return response([
                    'status_code'       => 200,
                    'status_message'    => 'Data berhasil ditemukan!',
                    'data'              => $land
                ], 200);
            }
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
                'name_land'         => 'required',
                'address'           => 'required',
                'province'          => 'required',
                'city'              => 'required',
                'district'          => 'required',
                'location_land'     => 'required',
                'foto_1'            => 'required|image',
                'foto_2'            => 'required|image',
                'ownname_land'      => 'required',
                'ownktp'            => 'required',
                'ownemail'          => 'required',
                'owntelp'           => 'required',
                'width_land'        => 'required',
                'length_land'       => 'required',
                'rule'              => 'required',
                'facility'          => 'required',
                'price'             => 'required',
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

            $path_foto1        = $req->file('foto_1')->store('images', 's3');
            $path_foto2        = $req->file('foto_2')->store('images', 's3');
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
            $land = new Land();

            $land->ID_LAND          = "LAND_" . substr(md5(time() . rand(10, 99)), 0, 6);
            $land->OWNER_USER       = $req->input('id_user');
            $land->NAME_LAND        = $req->input('name_land');
            $land->ADDRESS_LAND     = $req->input('address');
            $land->PROVINCE_LAND    = $req->input('province');
            $land->CITY_LAND        = $req->input('city');
            $land->DISTRICT_LAND    = $req->input('district');
            $land->LOCATION_LAND    = $req->input('location_land');
            if ($req->input('location_land') == 1) {
                $land->DESCDOC_LAND     = 'Foto rumah anda;Foto halaman rumah anda';
            } else {
                $land->DESCDOC_LAND     = 'Foto sertifikat tanah;Foto lahan anda';
            }
            $land->NOCERTIFICATE_LAND = ($req->input('nocertificate_land') == null) ? '-' : $req->input('nocertificate_land');
            $land->URLDOC_LAND      = Storage::disk('s3')->url($path_foto1) . ";" . Storage::disk('s3')->url($path_foto2);
            $land->LONG_LAND        = $req->input('longtitude');
            $land->LAT_LAND         = $req->input('latitude');
            $land->OWNNAME_LAND     = $req->input('ownname_land');
            $land->OWNKTP_LAND      = $req->input('ownktp');
            $land->OWNEMAIL_LAND    = $req->input('ownemail');
            $land->OWNTELP_LAND     = $req->input('owntelp');
            $land->WIDTH_LAND       = $req->input('width_land');
            $land->LENGTH_LAND      = $req->input('length_land');
            $land->RULE_LAND        = $req->input('rule');
            $land->DESC_LAND        = ($req->input('desc') == null) ? '-' : $req->input('desc');
            $land->FACILITY_LAND    = $req->input('facility');
            $land->URLGALLERY_LAND  = $path_gallery1 . ";" . $path_gallery2 . ";" . $path_gallery3 . ";" . $path_gallery4 . ";" . $path_gallery5;
            $land->PRICE_LAND       = $req->input('price');
            $land->RATING_LAND      = '5';
            $land->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil disimpan!',
                "data"              => ['id_land' => $land->ID_LAND]
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
                'id_land'           => 'required|exists:land,ID_LAND',
                'name_land'         => 'required',
                'address'           => 'required',
                'province'          => 'required',
                'city'              => 'required',
                'district'          => 'required',
                'location_land'     => 'required',
                // 'foto_1'            => 'required|image',
                // 'foto_2'            => 'required|image',
                'ownname_land'      => 'required',
                'ownktp'            => 'required',
                'ownemail'          => 'required',
                'owntelp'           => 'required',
                'width_land'        => 'required',
                'length_land'       => 'required',
                'rule'              => 'required',
                'facility'          => 'required',
                'price'             => 'required',
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

            $path_foto1        = ($req->file('foto_1') == null) ? '-' : $req->file('foto_1')->store('images', 's3');
            $path_foto2        = ($req->file('foto_2') == null) ? '-' : $req->file('foto_2')->store('images', 's3');

            $path_gallery1     = ($req->file('gallery_1') == null) ? '-' : $req->file('gallery_1')->store('images', 's3');
            $path_gallery2     = ($req->file('gallery_2') == null) ? '-' : $req->file('gallery_2')->store('images', 's3');
            $path_gallery3     = ($req->file('gallery_3') == null) ? '-' : $req->file('gallery_3')->store('images', 's3');
            $path_gallery4     = ($req->file('gallery_4') == null) ? '-' : $req->file('gallery_4')->store('images', 's3');
            $path_gallery5     = ($req->file('gallery_5') == null) ? '-' : $req->file('gallery_5')->store('images', 's3');

            $land               = Land::find($req->input('id_land'));

            // if ($land->OWNER_USER != $req->input('id_user')) {
            //     return response([
            //         'status_code'       => 400,
            //         'status_message'    => "Anda tidak berhak mengupdate!",
            //     ], 400);
            // }
            $explode_image_doc = explode(";", $land->URLDOC_LAND);
            $explode_image = explode(";", $land->URLGALLERY_LAND);

            $path_foto1        = ($path_gallery1 == '-' ? $explode_image_doc[0] : Storage::disk('s3')->url($path_gallery1));
            $path_foto2        = ($path_gallery1 == '-' ? $explode_image_doc[0] : Storage::disk('s3')->url($path_gallery1));

            $path_gallery1     = ($path_gallery1 == '-' ? $explode_image[0] : Storage::disk('s3')->url($path_gallery1));
            $path_gallery2     = ($path_gallery2 == '-' ? $explode_image[1] : Storage::disk('s3')->url($path_gallery2));
            $path_gallery3     = ($path_gallery3 == '-' ? $explode_image[2] : Storage::disk('s3')->url($path_gallery3));
            $path_gallery4     = ($path_gallery4 == '-' ? $explode_image[3] : Storage::disk('s3')->url($path_gallery4));
            $path_gallery5     = ($path_gallery5 == '-' ? $explode_image[4] : Storage::disk('s3')->url($path_gallery5));

            $land->OWNER_USER       = $req->input('id_user');
            $land->NAME_LAND        = $req->input('name_land');
            $land->ADDRESS_LAND     = $req->input('address');
            $land->PROVINCE_LAND    = $req->input('province');
            $land->CITY_LAND        = $req->input('city');
            $land->DISTRICT_LAND    = $req->input('district');
            $land->LOCATION_LAND    = $req->input('location_land');
            if ($req->input('location_land') == 1) {
                $land->DESCDOC_LAND     = 'Foto rumah anda;Foto halaman rumah anda';
            } else {
                $land->DESCDOC_LAND     = 'Foto sertifikat tanah;Foto lahan anda';
            }
            $land->NOCERTIFICATE_LAND = ($req->input('nocertificate_land') == null) ? '-' : $req->input('nocertificate_land');
            $land->URLDOC_LAND      = $path_foto1 . ";" . $path_foto2;
            $land->LONG_LAND        = $req->input('longtitude');
            $land->LAT_LAND         = $req->input('latitude');
            $land->OWNNAME_LAND     = $req->input('ownname_land');
            $land->OWNKTP_LAND      = $req->input('ownktp');
            $land->OWNEMAIL_LAND    = $req->input('ownemail');
            $land->OWNTELP_LAND     = $req->input('owntelp');
            $land->WIDTH_LAND       = $req->input('width_land');
            $land->LENGTH_LAND      = $req->input('length_land');
            $land->RULE_LAND        = $req->input('rule');
            $land->FACILITY_LAND    = $req->input('facility');
            $land->URLGALLERY_LAND  = $path_gallery1 . ";" . $path_gallery2 . ";" . $path_gallery3 . ";" . $path_gallery4 . ";" . $path_gallery5;
            $land->PRICE_LAND       = $req->input('price');
            $land->RATING_LAND      = '5';
            $land->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Data berhasil diupdate!',
                "data"              => ['id_land' => $land->ID_LAND]
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
                'id_land'   => 'required|exists:land,ID_LAND'
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

            $land = Land::find($req->input('id_land'));
            $land->delete();

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
                'id_land'            => 'required',
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

            $land = Land::find($req->input('id_land'));

            $land->IS_ACTIVE    = 1;
            $land->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Lahan terverifikasi!',
                "data"              => ['id_lahan' => $land->ID_LAND]
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function status_land(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id_land'           => 'required|exists:land,ID_LAND',
                'is_active'         => 'required'
            ], [
                'required'  => 'Parameter :attribute tidak boleh kosong!',
            ]);

            if ($validator->fails()) {
                return response([
                    "status_code"       => 400,
                    "status_message"    => $validator->errors()->first()
                ], 400);
            }

            $status_Land = Land::where('ID_LAND', '=', $req->input("id_land"))->first();
            $status_Land->IS_ACTIVE       = $req->input("is_active");
            $status_Land->save();

            return response([
                "status_code"       => 200,
                "status_message"    => 'Berhasil mengubah status lahan!',
            ], 200);
        } catch (HttpResponseException $exp) {
            return response([
                'status_code'       => $exp->getCode(),
                'status_message'    => $exp->getMessage(),
            ], $exp->getCode());
        }
    }

    public function testing_paginate()
    {
        $search = "";
        $order = "4";
        $lon = "-7.967048127545302";
        $lat = "112.59591813625399";


        $land = DB::table('land')
            ->crossJoin('md_province')
            ->crossJoin('md_city')
            ->crossJoin('md_district')
            ->select(
                'land.ID_LAND',
                'land.NAME_LAND',
                'land.DESC_LAND',
                'land.LOCATION_LAND',
                'land.DESCDOC_LAND',
                'land.OWNER_USER',
                'land.OWNKTP_LAND',
                'land.NOCERTIFICATE_LAND',
                'land.WIDTH_LAND',
                'land.LENGTH_LAND',
                'land.PRICE_LAND',
                'land.FACILITY_LAND',
                'land.RATING_LAND',
                'land.IS_ACTIVE',
                'land.PROVINCE_LAND',
                'md_province.NAME_PROVINCE',
                'land.CITY_LAND',
                'md_city.NAME_CITY',
                'land.DISTRICT_LAND',
                'md_district.NAME_DISTRICT',
                'land.URLGALLERY_LAND',
                'land.URLDOC_LAND',
                DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                * cos(radians(land.LAT_LAND)) 
                * cos(radians(land.LONG_LAND) - radians(" . $lon . ")) 
                + sin(radians(" . $lat . ")) 
                * sin(radians(land.LAT_LAND))) AS distance")
            )
            ->where('land.PROVINCE_LAND', '=', DB::raw('md_province.ID_PROVINCE'))
            ->where('land.CITY_LAND', '=', DB::raw('md_city.ID_CITY'))
            ->where('land.DISTRICT_LAND', '=', DB::raw('md_district.ID_DISTRICT'))
            ->where('land.IS_ACTIVE', '=', 1);

        if ($search != null) {
            $land->where('land.NAME_LAND', 'LIKE', '%' . $search . '%');
        }

        if ($order == 1) {
            $land->orderBy('land.REGISTERAT_LAND', 'DESC');
        }
        if ($order == 2) {
            $land->orderBy('land.PRICE_LAND', 'ASC');
        }
        if ($order == 3) {
            $land->orderBy('land.PRICE_LAND', 'DESC');
        }
        if ($order == 4) {
            $land->orderBy('distance', 'ASC');
        }

        $results = $land->paginate(10);

        foreach ($results as $key => $value) {
            $list_img = explode(";", $results[$key]->URLGALLERY_LAND);
            $results[$key]->{'URLGALLERY_LAND'} = $list_img;

            $list_doc = explode(";", $results[$key]->URLDOC_LAND);
            $results[$key]->{'URLDOC_LAND'} = $list_doc;
        }

        $dataPagination = array();
        array_push(
            $dataPagination,
            array(
                "TOTAL_DATA" => $results->total(),
                "PAGE" => $results->currentPage(),
                "TOTAL_PAGE" => $results->lastPage()
            )
        );

        return response([
            'status_code'       => 200,
            'status_message'    => 'Data berhasil diambil!',
            'data'              => $results->items(),
            'status_pagination' => $dataPagination
        ], 200);
    }
}
