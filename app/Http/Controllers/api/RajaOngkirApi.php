<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RajaOngkirApi extends Controller
{
    private $key = '1a497fd68fb28ee626f55c88b5ab0e75';
    private $paramId = '';
    private $paramProvince = '';

    public function provinceRajaOngkir(Request $req)
    {
        if ($req->id != null) {
            $this->paramId = '?id=' . $req->id;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.rajaongkir.com/starter/province' . $this->paramId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'key: ' . $this->key
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $results = json_decode($response);
        $code = $results->rajaongkir->status->code;
        $desc = $results->rajaongkir->status->description;
        $result = $results->rajaongkir->results;

        return response([
            'status_code'       => $code,
            'status_message'    => $desc,
            'data'              => $result
        ], 200);
    }

    public function cityRajaOngkir(Request $req)
    {
        if ($req->province != null) {
            $this->paramProvince = '?province=' . $req->province;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.rajaongkir.com/starter/city' . $this->paramProvince,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'key: ' . $this->key
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $results = json_decode($response);
        $code = $results->rajaongkir->status->code;
        $desc = $results->rajaongkir->status->description;
        $result = $results->rajaongkir->results;

        return response([
            'status_code'       => $code,
            'status_message'    => $desc,
            'data'              => $result
        ], 200);
    }

    public function ship_cost(Request $req)
    {
        $param = "origin=" . $req->origin . "&destination=" . $req->destination . "&weight=" . $req->weight . "&courier=" . $req->courier . "";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $this->key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $results = json_decode($response);
        $code = $results->rajaongkir->status->code;
        $desc = $results->rajaongkir->status->description;
        $result = $results->rajaongkir->results[0]->costs;

        return response([
            'status_code'       => $code,
            'status_message'    => $desc,
            'data'              => $result
        ], 200);
    }
}
