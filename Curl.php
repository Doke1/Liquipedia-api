<?php
    namespace Main;

    class CurlE
    {
        public static function send(string $url)
        {
            $MAX_CONNECTS = 1;
            $TIMEOUT = 2;

            $headers = array(
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
                "Keep-Alive: true",
            );

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_MAXCONNECTS, $MAX_CONNECTS);
            curl_setopt($curl, CURLOPT_TIMEOUT, $TIMEOUT);
            #curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
        }
    }
?>
