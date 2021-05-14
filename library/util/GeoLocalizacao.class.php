<?php

class GeoLocalizacao {

    public static function get($ip) {
        $geoplugin = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
        foreach ($geoplugin as $key => $geo) {
            $key = str_replace('geoplugin_', '', $key);
            $out[$key] = $geo;
        }
        unset($out['credit']);
        return $out;
    }

    public static function getGeoByAddress($address) {
        $address = urlencode($address);
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=".Config::getData('keyGoogle');
        //Log::logTxt('debug', $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response);
        $out['latitude'] = $response_a->results[0]->geometry->location->lat;
        $out['longitude'] = $response_a->results[0]->geometry->location->lng;
        $out['endereco'] = $response_a->results[0]->formatted_address;
        return $out;
    }

}
