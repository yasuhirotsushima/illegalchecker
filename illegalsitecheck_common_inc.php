<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// ページID
define('LOGINPAGE', 0);
define('ADMINPAGE', 1);
define('INDEXPAGE', 2);
define('HISTORYPAGE', 3);
define('PREFPAGE', 9);

define('SUPERUSER', "administrator");   // スーパーユーザのユーザ名
define('SUPERUSERPASSWORD', "admin1");   // スーパーユーザのパスワード

define('DEBUG', 1); // 本番サイトではこれをコメントアウト

if (defined('DEBUG')){
    $DB_HOST = '127.0.0.1';
    $DB_NAME = 'heyzo_illegalcheck';
    $DB_USER = 'tsushima';
    $DB_PASS = 'mc9855';
} else { 
   $DB_HOST = 'robson01';
    $DB_NAME = 'heyzo_illegalcheck';
    $DB_USER = 'heyzo_illegal';
    $DB_PASS = 'LJukdf9ujfaq';
}
// for LOCAL TEST

//TABLE NAMES
$tbl_main = "urls";

// Set default timezone.
date_default_timezone_set('Canada/Pacific');


// LOGIN CHECK
function logincheck( $pageid )
{
    session_start();
    
    $forwardpage = "location:login.php";
    
    if ( isset($_SESSION['user']) ) {
        $user_id = $_SESSION['user'];
        if ( $user_id == SUPERUSER && $pageid == ADMINPAGE ){
            $forwardpage = "location:adminpage.php";
        } else {
            $forwardpage = "location:index.php";                
        }
    } else {
        header($forwardpage);
    }
    
}

// CONNECT TO DATABASE
function dbconnect()
{
    global $DB_HOST;
    global $DB_NAME;
    global $DB_USER;
    global $DB_PASS;
    // Start DATABASE Connection

//    $db_obj = new mysqli( $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME );
    $db_obj = new mysqlclass( $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME );
    if($db_obj->errno > 0){
        echo "ERROR DB :".$db_obj->error."<br />\n";
    }
    return $db_obj;
}


?>