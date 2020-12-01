<?php

//  error_reporting(E_ALL);
//  // Fehler in der Webseite anzeigen (nicht in Produktion verwenden)
//  ini_set('display_errors', 'On');

require_once "../config.ini.php"; //loading configs
require_once "../functions.php"; // loading logger und key checker
require_once "app_config.ini.php"; // loading api configs


/** 
 * IF DATA SEND - ELSE HTML Context
*/

if(isset($_POST['APP'])){
    $dataFromClient = $_POST;
}else if(isset($_GET['APP'])){
    $dataFromClient = $_GET;
}

if(isset($dataFromClient['token'])){
    $authkey = $$dataFromClient['token'];
}else if(isset($_SERVER['HTTP_AUTHORIZATION'])){
    $authkey = $_SERVER['HTTP_AUTHORIZATION'];

}

$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);


if(isset($_POST['APP'])|| isset($_GET['APP'])){



    //define return array
    $return_array =Array();

    //log User data
    DB_logger($authkey);
    
    //permission checker
    if($tokenscheck){
        $_SESSION['Rechte']= key_checker($authkey);
        // if deny Access
        if($_SESSION['Rechte'] ==0 && $dataFromClient['APP']!="tool"){
            //return empty error array
            $return_array['error']= "Token ungültig"; 
            echo json_encode($return_array);
            exit;
        }

    }else{
        //allow all functions
        $_SESSION['Rechte']=100;
    }
    

    if($dataFromClient['APP']=="reader" && $_SESSION['Rechte'] >= 1){

        try{
        $return_array['db'] =  listTripsDB(); // last Trip number from DB
        $return_array['stg']= listStorage();  // count files in path   

        }catch(Exception $e){
            $return_array['error']= $e -> getMessage(); 
        }


    }else if($dataFromClient['APP']== "filename_reader" && $_SESSION['Rechte'] >= 3){
        try{
            $files = array_slice(scandir($PathToTripData."/Archiv"), 2); //return all Files in path
            $return_array["files"]= $files; //filenames
            $return_array['size']= count($files); // count files
        }catch(Exception $e){
            $return_array['error']= $e -> getMessage(); 
        }



    }else if($dataFromClient['APP']=="uploader" && $_SESSION['Rechte'] >= 5){
        try{
            #simple uploader
            global $PathToTripData;
            #if(preg_match_all("/Trip_20[1-3][0-9]-[0-2][0-9]-[0-3][0-9]_[0-3][0-9]-[0-9][0-9]-[0-9][0-9].txt", $_FILES['uploadedfile']['name'], $matches, PREG_SET_ORDER, 0)){
                $target_path = $PathToTripData;
                $target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
                move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path);
	    #}
	    }catch(Exception $e){
                $return_array['error']= $e -> getMessage(); 
        }


    }else if($dataFromClient['APP']=="start" && $_SESSION['Rechte'] >= 7){
        global $PathToPy;
        try{
            //$programm = escapeshellcmd($PathToPy + $args);
            $command = "cd $PathToPy ; python3 $Pyname -nogui -compact"; // shell comand
            $ausgabe = shell_exec($command);
            $return_array["shell"]= "$ausgabe";

        }catch(Exception $e){
            $return_array['error']= $e -> getMessage();
        }
    }else if($dataFromClient['APP']=="tool"){
            global $PathToTripData;
            #simple uploader
            $target_path = $PathToTripData;
            $target_path = $target_path+'archiv/' . basename( $_FILES['uploadedfile']['name']); 
           
            move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path);
    }
    if(!isset($return_array['error'])){
        $return_array['error']= "none";
    }
    echo json_encode($return_array);

}else{
    header("Content-Type: text/html; charset=utf-8");
echo "<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8' />
  <meta name='viewport' content='width=device-width, initial-scale=1' />
  <meta http-equiv='X-UA-Compatible' content='ie=edge' />
  <title>Welcome</title>
</head>
<body>
  <h2>Willkommen auf meinem Testserver</h2>
  Dies ist ein privater Testserver für Test- und Ausbildungsinhalte. Dieser Server wird nicht Kommerziell genutzt. Jedes Projekt ist selbstständig und hat einige Anforderungen
  und Funktionen, eine Allgemeingültige Datenschutzerklärung und Impressum entnehmen Sie unten. <br>
  Alle Projekte sind nicht für die Öffentlichkeit gedacht und werden ggf. beim Abschluss in vorhandene Systeme eingebunden.
  <a href='impressum.html'><div>impressum </div> </a>
  <a href='datenschutzerklaerung.html'><div>Datenschutzerklärung </div> </a>
</body>
</html>";
}
?>
