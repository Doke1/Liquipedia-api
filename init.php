<?php
    require "./ParsePlayers.php";
    require "./Curl.php";

    $a = new GetPlayers();
    $country = readline("Country: ");
    $role = readline("Role: ");
    $age = readline("Age: ");
    $majors = readline("Majors: ");

    system("clear");

    $str = "/^[a-zA-Z_]+$/";
    if(preg_match($str, $country) && preg_match($str, $role) && preg_match("/\d+/", $age) && preg_match("/\d+/", $majors)) {
        $a->get($country, $role, $age, $majors);
    } else echo "\r\nInvalid filters\r\n";
?>
