<?php

require_once "config.ini.php"; //einladen der gobal Configs
    function DB_logger($value){
        /**
         * save IP, date, userAgend, post parameter, token on the DB
         *
         * @return nothing
         */ 
            global $pdo, $timestamp, $hashalgo, $tabellenname_api_logger;
            if($_POST['APP']=="uploader" || $_POST['APP']=="filename_reader"){
                $zusatz = "Dateiname"+$_POST['uploadedfile']['name'];
            }else if($_POST['APP']=="reader" ){
                $zusatz = "keiner vorhanden";
            }else if($_POST['APP']=="start"){
                $zusatz = "programmstart python";
            }else if($_POST['APP']=="tool"){
                $zusatz = "Python Programm txt Sync";
            }else{
                $zusatz = "keine gültiges Programm";
            }
    
            $sql = "INSERT INTO $tabellenname_api_logger (token, datum, ip, userAgend, functionsaufruf, parameter ) VALUES (?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([hash($hashalgo,$value), date("d.m.Y H:i",$timestamp), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_POST['APP'], $zusatz]);
    
        }



            
    function key_checker(String $token){
        /**
         * Check token if activ and have right permission
         *
         * @param String $token  Der Schlüssel der über Post übergeben wurde, ungehasht
         * 
         * @return Int mit Rechten(0-10)
         */ 
            
            #Funktion der die Berechtigung überprüft und protokolliert
            global $tabellenname_token, $hashalgo, $pdo;
        
            $statement = $pdo->prepare("SELECT * FROM $tabellenname_token WHERE token = :token;");
            $result = $statement->execute(array('token' => hash($hashalgo,$token)));
            $tokenreturn = $statement->fetch();
                    
            //Überprüfung des Tokens
            if ($tokenreturn !== false && $tokenreturn['rechte']>= 1 && $tokenreturn['active'] == 1) {return $tokenreturn['rechte'];} else {return 0;}
            
        }
        function listTripsDB(){
            /**
         * return last trip number from summary table
         * 
         * @return Int mit dem höchsten Wert auf der DB
         */ 
            global $pdo;
            global $tabellenname_uebersicht;
    
            //$statement = $pdo->prepare("SELECT * FROM $tabellenname_list_trips;");
            //$result = $statement->execute();
            //$trips = $statement->fetchAll();
            $statement = $pdo->prepare("SELECT trip_nummer FROM $tabellenname_uebersicht  order by trip_nummer desc limit 1;");
            $result = $statement->execute();
            $trips = $statement->fetch();

            return Intval($trips['trip_nummer']);
            }
    
        function listStorage(){
            /**
         * count files with regex in path
         *
         * @return Array mit allen Datennamen
         */ 
            global $PathToTripData;
	    $path =$PathToTripData. "Archiv";
            $files = scandir($path);
            $re = '/Trip_[A-Za-z0-9_]*.txt/m';
            $i=0;
            $treffer = array();
            foreach ($files as $data){
                if(!preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0)!=False){
                    $i++;
                }
            }
            return $i;
        }
?>
