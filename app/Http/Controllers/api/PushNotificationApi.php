<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushNotificationApi extends Controller
{
    public function push_notif(array $param)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = 'AAAAn4A7tzA:APA91bHpNg8_Vm_qvpA28hP3WZ4ASPKWFZRGQA_QN61ujVO_9GFxP6c-uuYh-qwMyJYGLC1xjIbCsniDZH7yEjGiA38Xm8qrQgawvVNH2yVscM29DTImVxnSKbS3YCQHRpi1QtO7etpy'; // ADD SERVER KEY HERE PROVIDED BY FCM

        $encodedData = json_encode($param);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
    }
}
