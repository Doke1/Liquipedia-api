<?php

    use Main\CurlE;

    class GetPlayers
    {
        public array $europe;

        public array $countrys;

        public string $uri_default = "https://liquipedia.net/counterstrike/";

        public $re_name, $re_country, $re_age, $re_role, $re_team, $re_player_name, $majors, $romanized;

        public $roles = array("Rifler", "AWPers");

        public $user_info = array();

        public function __construct()
        {
            $this->countrys = require("./config/country.php");

            $this->setRegexpVars();
        }

        public function country($country, $role): array
        {
            $country = $this->countrys[$country];
            $curl = "";

            if(in_array($role, $this->roles)) {
                $curl = CurlE::send($this->uri_default."Category:".$country[0]);
            } else if($role == "coach") {
                $curl = CurlE::send($this->uri_default."Category:".$country[1]);
            }

            preg_match_all("/<a href=\"\/counterstrike\/[a-zA-Z0-9\W][^:]+?\" title=\"[a-zA-Z0-9\-\s]+?\">(.*?)<\/a>/", $curl, $players);

            return array(...$players[1]);
        }
        public function get(string $country, string $role, int $age, int $majors)
        {
            $array = $this->country($country, $role);

            echo "Searching with filters: ".$country.", ".$role.", ".$age."\r\n";

            foreach($array as $key) {
                $curl = CurlE::send($this->uri_default.$key);

                $data = $this->setRegexp($curl, array("role" => $role, "age" => $age, "majors" => $majors), $key);
                $i = count($this->user_info);

                if(!empty($data)) {
                    $this->user_info[$i]["id"] = $data[0];
                    $this->user_info[$i]["name"] = $data[1];
                    $this->user_info[$i]["country"] = array(...$data[2]);
                    $this->user_info[$i]["age"] = $data[3];
                    $this->user_info[$i]["role"] = $data[4];
                    $this->user_info[$i]["team"] = $data[5];
                    $this->user_info[$i]["majors"] = $data[6];

                    print_r(".");
                }
            }

            if(count($this->user_info) >= 1) print_r("\r\nPlayers succesfuly founded\r\n");
            else print_r("\r\nPlayers not found\r\n");
            file_put_contents("./players.json", json_encode($this->user_info));
        }

        public function majorCount($uri)
        {
            $page = CurlE::send($uri."/Results");
            preg_match_all($this->majors, $page, $big);

            return count($big[0]);
        }

        public function setRegexp($curl, array $filters, string $key)
        {
            preg_match($this->re_player_name, $curl, $player);
            preg_match($this->re_name, $curl, $name);
            preg_match($this->romanized, $curl, $romanized);
            preg_match_all($this->re_country, $curl, $matches_country);
            preg_match($this->re_age, $curl, $matches_age, PREG_OFFSET_CAPTURE);

            if(empty($matches_age)) return array();
            if(!empty($romanized)) $name[1] = $romanized[1];
            $matches_age[1][0] = str_replace("&#160;", "", $matches_age[1][0]);

            preg_match("/\([^)]*\)/", $matches_age[1][0], $matches_age, PREG_OFFSET_CAPTURE);
            preg_match("/\d+/", $matches_age[0][0], $matches_age, PREG_OFFSET_CAPTURE);
            preg_match_all($this->re_role, $curl, $matches_role);

            if(empty($matches_role[3])) $matches_role[3][0] = "Rifler";

            preg_match($this->re_team, $curl, $matches_team, PREG_OFFSET_CAPTURE);

            $majors = $this->majorCount($this->uri_default.$key);

            if(!strcmp(strtolower($matches_role[3][0]), strtolower($filters["role"])) && 
                $matches_age[0][0] >= ($filters["age"]-3) && $matches_age[0][0] <= ($filters["age"]+3) &&
                $majors >= ($filters["majors"]-3) && $majors <= ($filters["majors"]+3)
            ) {
                return array($player[1], $name[1], $matches_country[6], $matches_age[0][0], $matches_role[3][0] ?? "Rifler", $matches_team[3][0] ?? "none", $majors);
            }

            return array();
        }
        public function setRegexpVars()
        {
            $this->re_name = "
                /<div class=\"infobox\-cell\-2 infobox\-description\">Name:<\/div><div class=\"infobox\-cell\-2\">(\X+?)<\/div>/
            ";

            $this->re_country = "
                /((<span class=\"flag\">)*(<a href=\"\/counterstrike\/Category:(.*?)\" title=\"(\w+)\"><img alt=\"(\w+)\" src=\"\/commons\/images\/(.*?)\" decoding=\"async\" width=\"36\" height=\"24\" loading=\"lazy\" \/><\/a>)(<\/span>))/
            ";

            $this->re_age = "
                /<div class=\"infobox\-cell\-2 infobox\-description\">Born:<\/div><div class=\"infobox\-cell\-2\">(.*?)<\/div>/
            ";

            $this->re_role = "
                /<a href=\"\/counterstrike\/Category:(AWPers|Riflers)\" title=\"Category:(AWPers|Riflers)\">(AWPers|Rifler?)<\/a>/
            ";

            $this->re_team = "
                /<div class=\"infobox\-cell\-2 infobox\-description\">Team:<\/div><div class=\"infobox\-cell\-2\">*(<a href=\"\/counterstrike\/(.*?)\" title=\"(.*?)\">(.*?)<\/a> ?)<\/div>/
            ";

            $this->re_player_name = "
                /<span dir=\"auto\">(.*?)<\/span>/
            ";

            $this->romanized = "
                /<div class=\"infobox\-cell\-2 infobox\-description\">Romanized Name:<\/div><div class=\"infobox\-cell\-2\">(.*?)<\/div>/
            ";

            #<tr class=\"valvemajor\-highlighted\">(.*?)</tr>
            $this->majors = "
                /<tr class=\"valvemajor-highlighted\">((?!<a href=\"\/counterstrike\/A\-Tier_Tournaments\" title=\"A\-Tier Tournaments\">A\-Tier<\/a>).)*?<\/tr>/
            ";
        }
    }
?>
