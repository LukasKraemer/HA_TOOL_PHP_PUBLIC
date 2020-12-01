<?php
require "../config.ini.php"; //load configs
require_once "../functions.php"; //load commun functions
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//get connection
$mysqli = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if(!$mysqli){
  die("Connection failed: " . $mysqli->error);
}

//log activity and check permission
DB_logger($_POST['passwort']);
$_SESSION['Rechte'] = key_checker($_POST['passwort']);
if($_SESSION['Rechte']!= 10 && $_SESSION['Rechte']!= 9){
  die("Error");
}

//check if values are set
if(!isset($_POST['output'])){
  $_POST['output'] ="none";
}
if(isset($_POST['filter1_min'])){
$min_trip=$_POST['filter1_min'];
}else{
  $min_trip= 0;
}


if(isset($_POST['filter1_max'])){
$max_trips= $_POST['filter1_max'];
}else{
  $max_trips= 10000;
}

if(isset($_POST['beginn'])){
  $beginn_html =$_POST['beginn'];}
  else{
    $beginn_html = '1970-01-01';
  }
  if(isset($_POST['ende'])){
    $ende_html = $_POST['ende'];
  }
  else{
    $ende_html = "2030-01-01";
  }


if($_POST['output']=== "json" ){
  //return column names as json 
  header("Content-Type: application/json; charset=UTF-8");
  $_SESSION['login']="true";
  if($_POST['chart']=="month"){
      $columns=array(
"Fahrtstrecke_gesamt" ,
"Fahrtstrecke_elektisch",
"Gesamtfahrzeit_in_Stunden",
"Fahrzeit_elektrisch",
"Fahrzeit in Bewegung",
"Fahrzeit elektrisch Maximal",
"Fahrzeit Elektrisch in Prozent",
"Durschnittlicher Akkustand",
"Spritverbrauch in L",
"Durschnittsverbauch",
"Außentemperatur",
"Durschnittsgeschwindigkeit", 
"Bewegung in Prozent",
"Bewegung Elektrisch in Prozent",
"Min Außentemperatur",
"Max Außentemperatur"
);
    print json_encode($columns);
    exit();
  }
  else if($_POST['chart']=="day"){

  }
  $sql = "SHOW COLUMNS FROM $tabellenname_uebersicht";
  $res = $mysqli->query($sql);
  
  $columns=array();
  while($row = $res->fetch_assoc()){
        $columns[] = $row['Field'];}
      print json_encode($columns);
      

        $res->close();
        exit;
    }

if($_POST['APP']=="show_chart"){
      if($_POST['chart']=="month"){
      $query = '

SELECT 

      sum(trip_laenge) as Fahrtstrecke_gesamt ,
       sum(trip_laengeev) as Fahrtstrecke_elektisch,
       sum(fahrzeit)/60 as Gesamtfahrzeit_in_Stunden,
       sum(fahrzeit_ev) as Fahrzeit_elektrisch,
       sum(fahrzeit_bewegung) as "Fahrzeit in Bewegung",
       sum(spritverbrauch)/1000 as "Spritverbrauch in L",
       avg(aussentemperatur_durchschnitt) as Außentemperatur,
       avg(soc_durchschnitt) as "Durschnittlicher Akkustand",
       avg(verbauch_durchschnitt) as "Durschnittsverbauch",
       avg(ev_anteil) as "Fahrzeit Elektrisch in Prozent",
       avg(geschwindichkeit_durchschnitt) as "Durschnittsgeschwindigkeit", 
       sum(fahrzeit_bewegung)/100*sum(fahrzeit_ev) as "Bewegung in Prozent",
       sum(trip_laenge)/100*sum(trip_laengeev) as "Bewegung Elektrisch in Prozent",
       max(fahrzeit_ev) as "Fahrzeit elektrisch Maximal",
       min(aussentemperatur_durchschnitt) as "Min Außentemperatur",
       max(aussentemperatur_durchschnitt) as "Max Außentemperatur",
       year(tag) as jahr,
       MONTH(tag) as monat,
       day(tag) as tag
      FROM uebersicht
      group by month(tag), year(tag)
order by date(tag)
;
';
      }
      if($_POST['chart']=="day"){
      //query to get data from the table
      $query = "SELECT * FROM $tabellenname_uebersicht where tag >= '$beginn_html' AND tag <= '$ende_html' AND trip_laenge >=$min_trip and trip_laenge <= $max_trips ORDER BY tag, uhrzeit_Beginns";
      //$query = "SELECT * FROM $tabellenname_ueberischt where tag BETWEEN '$beginn_html' AND '$ende_html'  ORDER BY tag, uhrzeit_Beginns ;";
      }
      //execute query
      $result = $mysqli->query($query);
      //loop through the returned data
      $data = array();
      foreach ($result as $row) {
        $data[] = $row;
  }


  //free memory associated with result
  $result->close();

  //close connection
  $mysqli->close();

  //now print the data
  print json_encode($data);

}
     ?>
