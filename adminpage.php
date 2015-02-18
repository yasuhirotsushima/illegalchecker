<?php
include "illegalsitecheck_common_inc.php";
logincheck(ADMINPAGE);
?>


<!DOCTYPE html>
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
        <script src="js/spin.min.js"></script>
        <link href="styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            <!--
            #container {
                overflow: hidden;
            }
            
            div.dbitems {
                width: 600px;
                padding-bottom: 15px;
            }
            
            tr.tr1 {
                height: 21px;
                padding: 0;
                margin: 0;
            }
            td.td1 {
                width: 500px;
            }
            td.td2 {
                width: 40px;
            }
            td.td3 {
                width: 460px;
            }
            span.caption {
                width: 120px;
            }
            
            #domainlist {
                border: 1px solid #9FAFD1;
                width: 800px;
                height: 160px;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            #domainlist.td1 {
                width: 300px;
                border: 1px solid #9FAFD1;
            }
            #domainlist.td3{
                width: 350px;
                border-left: 1px solid #9FAFD1;
            }
            #ngdomainlist_h {
                width: 800px;
                border: 1px solid #9FAFD1;
                background-color: #CCCCCC;
                height: 20px;
                font-weight: bold;
                font-size: 10pt;
                text-align: right;
            }
            #ngdomainlist_h .c1 {
                width: 350px;
                text-align: center;
                border: none;
            }
            #ngdomainlist_h .c2 {
                width: 400px;
                text-align: center;
                border: none;
            }
            #userlist {
                border: 1px solid #9FAFD1;
                width: 550px;
                height: 160px;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            #userlist_h {
                width: 550px;
                border: 1px solid #9FAFD1;
                background-color: #CCCCCC;
                height: 20px;
                font-weight: bold;
                font-size: 10pt;
                text-align: right;
            }
            #userlist_h .c1 {
                width: 150px;
                text-align: center;
                border: none;
            }
            #userlist_h .c2 {
                width: 350px;
                text-align: center;
                border: none;
            }
            #listView {
                border: 1px solid #9FAFD1;
                width: 950px;
                height: 160px;
                text-align: right;
                overflow-y: scroll;
                margin-bottom: 15px;
            }
            #listView_h {
                border: 1px solid #9FAFD1;
                background-color: #CCCCCC;
                width: 950px;
                height: 20px;
                font-weight: bold;
                font-size: 10pt;
                text-align: center;
            }
            #listView_h .c1 {
                width: 233px;
            }
            #listView_h .c2 {
                width: 110px;
            }
            #listView_h .c3 {
                width: 150px;
            }
            #historylist_h {
                border: 1px solid #9FAFD1;
                background-color: #CCCCCC;
                width: 900px;
                height: 20px;
                font-weight: bold;
                font-size: 10pt;
                text-align: left;
            }
            #historylist_h .c1 {
                width: 120px;
            }
            #historylist_h .c2 {
                width: 62px;
            }
            #historylist_h .c3 {
                width: 70px;
            }
            #historylist_h .c4 {
                width: 450px;
            }
            #historylist {
                border: 1px solid #9FAFD1;
                padding: 0px;
                width: 900px;
                height: 300px;
                font-size: 10pt;
                margin-bottom: 15px;
                overflow-y: scroll;
            }
            #history_detail {
                margin-top: 15px;
                border: 1px solid #9FAFD1;
                padding: 0px;
                width: 900px;
                height: 300px;
                font-size: 10pt;
                overflow-y: scroll;
            }
            .list_m {
                width: 932px;
                border: 1px solid;
                border-color: #3366FF;
            }
            .list_m .c1{
                width: 132px;
            }
            .list_m .c2{
                width: 92px;
            }
            .list_m .c3{
                width: 91px;
            }
            .list_m .c4{
                width: 450px;
            }
            .list_l {
                width: 880px;
                border: 1px solid;
                border-color: #3366FF;
            }
            .list_l .c1{
                width: 130px;
            }
            .list_l .c2{
                width: 80px;
            }
            .list_l .c3{
                width: 70px;
            }
            .list_l .c4{
                width: 450px;
            }
            .list_l .c5{
                width: 30px;
            }
            .urllist{
                border-collapse: collapse;
            }
            .urllist tr td{
                border: 1px solid;
                border-color: #3366FF;
            }
            a:hover {
                color: #FF0000;
                font-weight: bold;
            }
            a:hover {
                color: #FF0000;
                font-weight: bold;
            }
            -->
        </style>
        <script type="text/javascript">
// Functions invoked when startup. 
$(function(){
    $("#menubar").load("menubar.php", {"page_id": 1, "user":'<?php echo $_SESSION['user']; ?>'});
    
    loadDBInfo();
});


// ユーザログアウト時処理
function logout()
{
    var ans = confirm("ログアウトします。よろしいですか？" );
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'logout'}, function(data, status){
            window.location.href="login.php";
        });
    }
}

function loadDBInfo()
{
    $("#domainlist").load("illegalsitecheckerfunctions.php", {"command": 'getDomainList'});
    $("#userlist").load("illegalsitecheckerfunctions.php", {"command": 'getUserList'});    
    $("#listView").load("illegalsitecheckerfunctions.php", {"command": 'getListByUser'});    
    $("#historylist").load("illegalsitecheckerfunctions.php", {"command": 'getURLList'});    
}
// NGドメインの登録
function registerNGdomain() 
{
    var domain = $("#ngdomain").val();
    var remark = $("#remark").val();
    if ( !domain.match(/^http:\/\//i) ) {
        alert("無効なURLです。\nhttp://で始まるURLを入力してください。");
    } else {
        $.post("illegalsitecheckerfunctions.php",{"command": 'registerNGdomain', "domain":domain, "remark":remark}, function(data, status, xhr){
            if ( data ) {
                alert( data );
            }
            loadDBInfo();
            $("#ngdomain").val("");
            $("#remark").val("");
        });
    } 
}
// 新規ユーザの登録
function registerNewUser()
{
    var userid = $("#userid").val();
    var passwd = $("#passwd").val();
    if ( !userid.match(/[0-9a-zA-Z]/g) ) {
        alert("名前が無効です。\nユーザ名は半角英数字のみを使用してください");
    } else {
        if ( passwd == "" ) {
            alert("パスワードを設定してください");
            return;
        }     
        $.post("illegalsitecheckerfunctions.php",{"command": 'registerNewUser', "userid":userid, "passwd":passwd}, function(data, status, xhr){
            loadDBInfo();
            $("#userid").val("");
            $("#passwd").val("");
        });
    } 

}
// NGドメインの削除
function deldomain( domainid  )
{
    var ans = confirm("NGドメイン " + domainid + " を削除します。よろしいですか？");
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'delDomain', "domainid":domainid}, function(data, status, xhr){
            loadDBInfo();
        });
    }
}

// ユーザの削除
function deluser( userid )
{
   var ans = confirm("ユーザー " + userid + " を削除します。よろしいですか？");
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'delUser', "userid":userid}, function(data, status, xhr){
       });
        loadDBInfo();
        alert("削除が完了しました");
    }
}

function mes()
{
}
// ユーザパスワードのリセット
function resetpw( userid )
{
    var pw = prompt("ユーザー " + userid + " の新パスワードを入力してください。");
    if ( pw　!== "" ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'resetpw', "userid":userid, "passwd":pw},function(data, status, xhr){
        });
        loadDBInfo();
        alert("変更が完了しました");        
    }else{
        alert("変更するパスワードを入力してください");
    }

}
// ユーザー毎の登録詳細を表示
function getUserRegisterDetail( userId )
{
    $("#listView").load("illegalsitecheckerfunctions.php", {"command": 'getListByUser'});    
    $("#historylist").load("illegalsitecheckerfunctions.php", {"command": 'getHistoryList', "userid": userId});
    $("#history_detail").text("");
}
// 登録履歴の削除
function deleterecord( hid )
{
    var ans = confirm("履歴に関連するURLも削除します。よろしいですか？" );
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'deleteHistoryRecord', "hid": hid}, function(data, status, xhr){
            $(this).delay(500).queue(function(){
                getUserRegisterDetail();                 
            });
        });
    }
}
// 履歴詳細の表示
function dispDetail( hid )
{
    $("#history_detail").load("illegalsitecheckerfunctions.php", {"command": "getHistory_Detail", "hid": hid}, function(){});
    return;
}
        </script>
    </head>
    <body>
        <div id="menubar"><!-- Here is MENU ITEM area  --></div>
        <div id="maincontents">
            <div id="header"><h1>Check Illegal Website! Admin Page.</h1></div>
            
            <div id="container">
                <div class="ngdomainarea">
                    <h3>NGドメイン登録</h3>
                    <span class="caption">ドメイン名</span><input type="text" id="ngdomain" size="50" /><br />
                    <span class="caption">備　考</span><input type="text" id="remark" size="50" /><input type="button" value="登録" onclick="registerNGdomain()" />
                    <table id="ngdomainlist_h">
                        <tr><td class="c1">NGドメイン名</td><td class="c2">備　考</td></tr>
                    </table>
                    <div id="domainlist"></div>
                </div>

                <div class="dbitems">
                    <h3>ユーザ管理</h3>
                    <span class="caption">ユーザID</span><input type="text" id="userid" size="50" /><br />
                    <span class="caption">パスワード</span><input type="text" id="passwd" size="50" /><input type="button" value="登録" onclick="registerNewUser()" />
                    <table id="userlist_h">
                        <tr><td class="c1">ユーザー名</td><td class="c2">パスワード</td></tr>
                    </table>
                    <div id="userlist"></div>
                </div>

                <div class="dbitems">
                    <h3>データ集計</h3>
                    <table id="listView_h">
                        <th><td class="c1">ユーザ名</td><td class="c2">Google<br>登録回数</td><td class="c3">URL件数</td><td class="c2">Uploader<br>登録回数</td><td class="c3">URL件数</td><td class="c2">Tube系<br>登録回数</td><td class="c3">URL件数</td><td class="c2">FC2<br>登録回数</td><td class="c3">URL件数</td><td class="c2">Image<br>登録回数</td><td class="c3">URL件数</td><td></td></th>
                    </table>
                    <div id="listView"></div>
                    <table id="historylist_h">
                        <th><td class="c1">登録日時</td><td class="c2">種別</td><td class="c3">URL件数</td><td class="c4">登録時の最初のURL</td><td></td></th>
                    </table>
                    <div id="historylist"></div>
                    <div id="history_detail"></div>
                </div>
            </div>
       </div>
    </body>
</html>
