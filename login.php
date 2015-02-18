<?php
include "mysqlclass.php";
include "illegalsitecheck_common_inc.php";

session_start();

if (isset($_SESSION['user'])){
    header("location:index.php");
}

$sError = "";
$forwardpage = "index.php";
if ( filter_input(INPUT_POST, 'loginbtn', FILTER_SANITIZE_STRING) == "login" ) {
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $passwd = filter_input(INPUT_POST, 'passwd', FILTER_SANITIZE_STRING);
    
    if ( $user == SUPERUSER ) {
        if ( $passwd == SUPERUSERPASSWORD ){
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = 0;
            $_SESSION['jsResult'] = "";
            $forwardpage = "adminpage.php";
        } else {
            $sError = "パスワードが違います。";
        }
    } else {
        if ( $link = dbconnect() ){
            $que = "SELECT id, name, password FROM user_master WHERE name = '".$user."'";
            $result = $link->query($que);
            if ( $rec = $link->getrow($result) ) {
                if ( $rec['password'] == $passwd ){
                    $_SESSION['user_id'] = $rec['id'];
                    $_SESSION['user'] = $user;
                    $_SESSION['jsResult'] = "";
                } else {
                    $sError = "パスワードが違います。";
                }
            } else {
                $sError = "ユーザ名が無効です。";
            }
        } else {
            $sError = "システムエラーです。管理者にお問い合わせください。";
        }
        
    }
    if ( $sError == "" ) {
        header("location:".$forwardpage);
    }
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <title>Illegal site checker</title>
	<script src="js/jquery-2.1.0.min.js"></script>
        <link href="styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript">
// Functions invoked when startup. 
$(function(){
    $("#countofsites").load("illegalsitecheckerfunctions.php", {"command": 'checkLogin'});
    
    $("#post").click(function(){
        var PostID = $(this).attr("id");
    }).change();
});


        </script>
    </head>

    <body>
        <div id="maincontents">
            <h1>Check Illegal Website!</h1>
            <h3>Login page.</h3>
            <form action="login.php" method="POST">
                <table>
                    <tr>
                        <td>USER     : </td><td><input type="text" name="user" size="40" /></td>
                    <tr>
                        <td>PASSWORD     : </td><td><input type="password" name="passwd" size="40" /></td>
                    </tr>
                    <tr colspan="2">
                        <td><input type="submit" name="loginbtn" value="login" /></td>
                    </tr>
                </table>
            </form>
        <?php
            if ($sError != ""){
                echo $sError."<br />\n";
            }
        ?>
        </div>
    </body>
</html>
