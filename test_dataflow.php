<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
//ini_set("allow_url_fopen", true);

//array for JSON response
$response = array();

//include db connect class
//require_once __DIR__ . '/db_connect.php';
//connecting to db
//$db = new DB_CONNECT();

function db_connect(){
        $_myserver="localhost";
        $_user="root";
        $_passwd="MomLink-Root";
        $_database = "momlink";
        if(!mysql_connect($_myserver, $_user, $_passwd)) {
          echo "Could not Connect";
        } else {
          mysql_select_db($_database);
        }
}

//check for post data
if(isset($_REQUEST)) {
    db_connect();
    header('Content-Type: text/javascript');
    echo "Work..";
    echo file_get_contents('php://input');
    $data = json_decode(file_get_contents('php://input'));
    $userName = $data->{'pncc-name'};
    $psw = $data->psw;
    $username=mysql_real_escape_string($userName);
    $password=mysql_real_escape_string($psw);
    $username = stripslashes($username);
    $password = stripslashes($password);
    $sql_id = "SELECT id FROM pncc WHERE username = '".$username."' AND password = MD5('".$password."')";
    $pncc_id = mysql_query($sql_id); 
    $data = mysql_fetch_assoc($pncc_id);
    if ($data) {
        echo $data["id"] . "\n";
    } 

    $sql_update = "UPDATE pncc SET email = 'Pingjie.Tang.28@nd.edu' WHERE id = '43'";
    $update = mysql_query($sql_update);
    $sql_update_select = "SELECT email FROM pncc WHERE id = '43'";
    $update_data = mysql_query($sql_update_select);
    $update_result = mysql_fetch_assoc($update_data);
    if ($update_result) {
        echo $update_result["email"];
    }

    $rows = mysql_num_rows($pncc_id);   
    if($rows == 0) { 
        echo "<script type='text/javascript'> 
               jQuery(document).ready(function(){
                   jQuery('#err').fadeIn(); 
                   return false;
               });
              </script>";
    }
   // else {
       
          //$data = mysql_fetch_assoc($pncc_id);
          //print_r($data);
          // echo $data['id'];
          //session_register("username");
          //session_register("password");
          //session_start();
          //$userPermission = 1;
          //$_SESSION['username'] = $username;
          //$_SESSION['password'] = $password;
          //setcookie("permission", $userPermission);
          //header("Location: http://momlink.crc.nd.edu/MomLink/main.php");
    //}
}
else {
     //required field is missing
     $response["success"] = 0;
     $response["message"] = "Required field(s) is missing";
     echo json_encode($response);
}
//mysqli_close($db);
mysql_close();
?>
