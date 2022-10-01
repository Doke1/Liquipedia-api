<?php
    namespace Main;

    class CurlE
    {
        public static function send(string $url)
        {
            $MAXCONN = 1;
            $TIMEOUT = 10;

            $headers = array(
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
            );

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_MAXCONNECTS, $MAXCONN);
            curl_setopt($curl, CURLOPT_TIMEOUT, $TIMEOUT);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            
            
           	
            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
        }
    }
?>
