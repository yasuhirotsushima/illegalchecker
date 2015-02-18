<?php
include "illegalsitecheck_common_inc.php";
include "mysqlclass.php";

logincheck(HISTORYPAGE);


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta charset="UTF-8">
        <title>Illegal site checker</title>
	<script src="js/jquery-2.1.0.min.js"></script>
        <link href="styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            <!--
            #resulttable, th, td {
                border: 1px solid;
                border-color: #B0BED9;
                border-collapse: collapse;
                margin: 0px;
                padding: 2px;
                color: #666;
            }
            
            #historylist {
                border: 1px solid #9FAFD1;
                padding: 0px;
                width: 900px;
                height: 300px;
                font-size: 10pt;
                overflow-y: scroll;
            }
            #historylist_h {
                border: 1px solid #9FAFD1;
                background-color: #CCCCCC;
                padding: 0px;
                width: 900px;
                height: 20px;
                font-size: 10pt;
                font-weight: bold;
            }
            #history_detail {
                margin-top: 20px;
                padding: 0px;
                border: 1px solid #9FAFD1;
                width: 900px;
                height: 350px;
                font-size: 10pt;
                overflow-y: scroll;
            }
            #latestregister {
                color: #FF0000;
                font-weight: bold;
            }
            a:hover {
                color: #FF0000;
                font-weight: bold;
            }
            .c1 {
                width: 150px;
            }
            .c2 {
                width: 80px;
            }
            .c3 {
                width: 80px;
            }
            .c4 {
                width: 600px;
            }
            .c5 {
                width: 50px;
            }
            -->
        </style>
        
        <script type="text/javascript">
/******************  Functions invoked when startup.  ******************/
var userId = <?php echo $_SESSION['user_id']; ?>;
var userName = '<?php echo $_SESSION['user']; ?>';
$(function(){
    //$("#countofsites").load("illegalsitecheckerfunctions.php", {"command": 'getSiteCount'});
    //$("#latestregister").load("illegalsitecheckerfunctions.php", {"command": 'getLatestRegister', "userid": userId});
    $("#menubar").load("menubar.php", {"page_id": 3, "user":userName});
    rewriteitems(userId);
    
});


/******************  Functions called from code.  ******************/

function logout()
{
    var ans = confirm("ログアウトします。よろしいですか？" );
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'logout'}, function(data, status){
            window.location.href="login.php";
        });
    }
}

function rewriteitems()
{
    $("#countofsites").load("illegalsitecheckerfunctions.php", {"command": 'getSiteCount'});
    $("#latestregister").load("illegalsitecheckerfunctions.php", {"command": 'getLatestRegister', "userid": userId});
    $("#historylist").load("illegalsitecheckerfunctions.php", {"command": 'getHistoryList', "userid": userId});
}

function dispDetail( hid )
{
    $("#history_detail").load("illegalsitecheckerfunctions.php", {"command": "getHistory_Detail", "hid": hid}, function(){rewriteitems(0);});
    return;
}

function deleterecord( hid )
{
    var ans = confirm("履歴に関連するURLも削除します。よろしいですか？" );
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'deleteHistoryRecord', "hid": hid}, function(data, status, xhr){
            rewriteitems();
        });
    }
}

        </script>
    </head>
    <body>
        <div id="menubar">
        </div>
        <div id="maincontents">
            <h1>Register History</h1>
            <span id="comment1">登録履歴一覧</span><span id="countofsites"></span><br />
            <span>最新の登録：</span><span id="latestregister"></span>
            <table id="historylist_h">
                <tr><td class="c1">登録日時</td><td class="c2">種別</td><td class="c3">URL件数</td><td class="c4">登録時の最初のURL</td><td></td></tr>
            </table>
            <div id="historylist">
                <!-- Get History List and Display it here. -->
            </div>

            <div id='history_detail'>
                <!-- DETAIL URL LIST display area -->
            </div>
        </div>
    </body>
</html>
