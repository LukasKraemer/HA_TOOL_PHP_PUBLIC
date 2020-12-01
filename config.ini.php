<?php
#header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//error_reporting(-1);
//ini_set('error_reporting', E_ALL);
date_default_timezone_set("Europe/Berlin");
$timestamp = time();

#Datenbank Configs
$DB_HOST ='127.0.0.1';
$DB_USERNAME= 'SECRET';
$DB_PASSWORD= 'SECRET';
$DB_NAME= 'car';

#Tabellennamen der DB
$tabellenname_token="tokenlist";
$tabellenname_raw="fast_log1";
$tabellenname_uebersicht= "uebersicht";
$tabellenname_list_trips= "loggedtrips";
$tabellenname_api_logger = "APILOGGER";

#Haslalgo - php format
$hashalgo = "sha256";


#Debug Flag
$debug= false;
#soll alles protokolliert werden?
$api_logger= True;
#Sollen die Schlüssel überüft werden?
$tokenscheck= True;
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USERNAME, $DB_PASSWORD);

?>
