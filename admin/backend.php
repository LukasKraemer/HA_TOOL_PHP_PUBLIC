<?php
require_once "../config.ini.php"; //einladen der Configs
require_once "../functions.php";
session_start();
//var_dump($_POST);

$json= array();

DB_logger($_POST['token']);


if(!isset($_SESSION['Rechte'])|| isset($_POST['token'])){
    $_SESSION['Rechte'] = key_checker($_POST['token']);
}

if($_SESSION['Rechte']!= 10){
    $json['login'] = "keine Berechitigung";
    echo json_encode($json);
    exit();
} else{
    $json['login'] = "logged in";
}

function loadtoken(){
    global $pdo, $tabellenname_token, $tabellenname_api_logger, $DEBUG;
    $statement = $pdo->prepare("SELECT * FROM $tabellenname_token;");
    $result = $statement->execute();
    $token = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $token;
    }


function loadlogsapi(){
global $pdo, $tabellenname_token, $tabellenname_api_logger, $DB_NAME, $json, $DEBUG;

    $statement = $pdo->prepare("SELECT idAPILOGGER,ip, name, id, datum,  rechte, beschreibung, functionsaufruf  FROM $DB_NAME.$tabellenname_api_logger inner join $DB_NAME.$tabellenname_token on $tabellenname_token.token = $tabellenname_api_logger.token order by idAPILOGGER desc;");
$result = $statement->execute();
$apilogger = $statement->fetchAll(PDO::FETCH_ASSOC);

/*
    $sql = "SELECT * FROM $DB_NAME.$tabellenname_api_logger inner join $DB_NAME.$tabellenname_token on $tabellenname_token.token and $tabellenname_api_logger.token where ";


    $exiarry= array();
    if($_POST['name']!= "" && !isset($_POST['name'])){
        $exiarry['name']=$_POST['name'];
        $name= $_POST['name'];
        $sql .= "name = :name and ";
    }
    if($_POST['ip-adresse']!= "" && !isset($_POST['ip-adresse'])){
        $exiarry['ip']= $_POST['ip-adresse'];
        $ip= $_POST['ip-adresse'];
        $sql .= "ip = :ip and ";
    }
    if($_POST['functionsname']!= ""&& !isset($_POST['functionsname'])){
        $exiarry['func']=$_POST['functionsname'];
        $func= $_POST['functionsname'];
        $sql .= "functionsaufruf = ':func' and ";
    }
    if($_POST['rechte']!= ""&& !isset($_POST['rechte'])){
        $exiarry['rechte']=$_POST['rechte'];
        $rechte= $_POST['rechte'];
        $sql .= "rechte = :rechte and ";
    }
    if(isset($_POST['aktivetoken']) && !isset($_POST['aktivetoken'])){
        $exiarry['activ']=$_POST['aktivetoken'];
        $activ= $_POST['aktivetoken'];
        $sql .= "active = :activ and ";
    }

    $sql .= "datum between ':datum_start' and ':datum_ende';";    


    $date1 =new DateTime($_POST['datum_start']);
    $exiarry['datum_start']= $date1 ->format('d.m.Y');

    $date2 =new DateTime($_POST['datum_ende']);
    $exiarry['datum_ende']= $date2 ->format('d.m.Y');

    $arr = array(
        'name'        => $name,
        'ip'          => $ip,
        'func'        => $func,
        'rechte'      => $rechte,
        'activ'       => $activ,
        'datum_start' => $_POST['datum_start'],
        'datum_ende'  => $_POST['datum_ende']
    );

    $statement = $pdo->prepare($sql);
    $result = $pdo->prepare($sql)->execute($exiarry);
    $apilogger = $statement-> fetchAll(PDO::FETCH_ASSOC);
*/
    return $apilogger;

}
function createtoken(){
    global $pdo, $tabellenname_token, $json, $hashalgo;

    $statement = $pdo->prepare("SELECT token FROM $tabellenname_token where token = :token;");
    $result = $statement->execute(array('token' => hash($hashalgo,utf8_encode($_POST['token_token']))));
    $test = $statement->fetch();

    if($test !== false) {
        $json['error']= "token schon vorhanden";
    echo json_encode($json);
    exit();}



    if($_POST['token_activ'] == "on"){
        $activ = 1;
    }else{
        $activ=0;
    }
        $exi = array();
        $exi['token']= hash($hashalgo,utf8_encode($_POST['token_token']));
        $exi['name']=$_POST['token_namen'];
        $exi['rechte']=intval($_POST['token_rechte']);
        $exi['beschreibung']=$_POST['token_disc'];
        $exi['active']=$activ;

        $statement = $pdo->prepare("Insert Into $tabellenname_token (token, name, rechte, beschreibung, active) VALUES (:token, :name, :rechte, :beschreibung, :active); ");
        $statement->execute($exi);

        $json['tesdt']= $exi;
        if($statement == True){
            return "Erfolgreich";
        }else{
            return "Fehler";
        }
}



function fetchnames($Spalte){
    global $pdo, $tabellenname_token;
    $statement = $pdo->prepare("SELECT $Spalte, ID FROM $tabellenname_token");
    $result = $statement->execute();
    $test = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $test;
}
function fetchvales($id){
    global $pdo, $tabellenname_token;
    $statement = $pdo->prepare("SELECT * FROM $tabellenname_token where ID = $id");
    $result = $statement->execute();
    $test = $statement->fetch(PDO::FETCH_ASSOC);
    return $test;
}
function updatevalues(){
    global $pdo, $hashalgo, $tabellenname_token, $json;

    if($_POST['token_activ'] == "on"){
        $activ = 1;
    }else{
        $activ=0;
    }
    $exi = array();
    $exi['id']= $_POST['id'];
    $exi['name']=$_POST['token_namen'];
    $exi['rechte']=intval($_POST['token_rechte']);
    $exi['beschreibung']=$_POST['token_disc'];
    $exi['active']= intval($activ);

    $sql = "UPDATE $tabellenname_token SET name=:name, rechte=:rechte, beschreibung=:beschreibung,active =:active  WHERE ID=:id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute($exi);
    $json['DEBUG']= var_dump($_POST);
    return "sucessfull";
}

try{
    if($_POST['functionsrequst']=="showtoken"){
        $json['tokenlist']= loadtoken();
    }
    if($_POST['functionsrequst']=="showlog"){
        $json['apilog']= loadlogsapi();
    }
    if($_POST['functionsrequst'] == "tokencreate"){
        $json['createtoken']=createtoken();
    }
    if($_POST['functionsrequst'] == "fetchnames"){
        $json['names']=fetchnames("name");
    }
    if($_POST['functionsrequst'] == "fetchid"){
        $json['id']=fetchnames("ID");
        $json['values']=fetchvales($_POST['ID']);
    }
    if($_POST['functionsrequst'] == "fetchvales"){
        $json['values']=fetchvales($_POST['ID']);
    }
    if($_POST['functionsrequst'] == "updatevales"){
        $json['response']=updatevalues();
    }
    $json['error']= "null";
}catch(Exception $e){
    $json['error']= $e -> getMessage();
}
echo json_encode($json);
