<?php

class AssyncRequest {

    /**
     * 
     * AssyncRequest::call('http://localhost/api-2.5/assync/ocrapply/36522', [], ["Api-Key: F5kj123RtyeOL"]);
     * @param type $url
     * @param type $payload
     * @param type $header
     * @return boolean
     */
    public static function call($url, $payload, $header = []) {
        if (!function_exists('fsockopen')) {
            Log::error(__METHOD__ . 'Function fsockpen not exists');
            return false;
        }
        // montando as variáveis de chamada
        $values = [];
        foreach ($payload as $key => $value) {
            $values[] = "$key=" . urlencode($value);
        }
        $post_string = implode("&", $values);

        $parts = parse_url($url);

        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

        $out[] = "POST " . $parts['path'] . " HTTP/1.1";
        $out[] = "Host: " . $parts['host'];
        $out[] = "Content-Type: application/x-www-form-urlencoded";
        $out[] = "Content-Length: " . strlen($post_string);
        $out = array_merge($out, $header);
        $out[] = "Connection: Close\r\n";
        $out[] = $post_string;
        if (isset($post_string)) {
            $bodyRequest = implode("\r\n", $out);
            //$out .= $post_string;
        }

        fwrite($fp, $bodyRequest);
        fclose($fp);
    }

}

