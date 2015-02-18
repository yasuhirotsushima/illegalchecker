<?php
include "illegalsitecheck_common_inc.php";

logincheck(INDEXPAGE);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta charset="UTF-8">
        <title>Illegal site checker</title>
	<script src="js/jquery-2.1.0.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
        <link href="js/jquery-ui.min.css" rel="stylesheet" type="text/css">
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
            #resulttable .td1 {
                width: 900px;
            }
            
            #detail {
                border: 1px solid #9FAFD1;
                padding: 0px;
                width: 900px;
                height: 350px;
                overflow-y: scroll;
            }
            #latestregister {
                color: #FF0000;
                font-weight: bold;
            }
            #loading {
                position: absolute;
                left:   50%;
                font-size: 10px;
            }
            -->
        </style>
        
        <script type="text/javascript">
/******************  Functions invoked when startup.  ******************/
var userId = <?php echo $_SESSION['user_id']; ?>;
var userName = '<?php echo $_SESSION['user']; ?>';
$(function(){
    $("#menubar").load("menubar.php", {"page_id": 2, "user":userName});
    
    var jsResult = '<?php echo $_SESSION['jsResult']; ?>';
    if (jsResult != ""){
        resultOutput(jsResult);
    }
    rewriteitems(userId);
    $("#post").click(function(){
        var PostID = $(this).attr("id"); 
   }).change();


});


/******************  Functions called from code.  ******************/
function checkURLs( )
{
    //$(".checkbutton").prop('disabled', true);   //　連続クリック防止のためボタン表示をオフ
    
    //var progressbar_lines = 10;   // このURL数以上だったらプログレスバーを表示
    var max_size = 8000000;     // POSTで送れる最大サイズ。これを超えたら警告。
        
    var urlstring = $('#urltextbox').val();

    var urlsize = jstrlen(urlstring);
    if ( urlsize > max_size ){
        alert("送信できる最大サイズ(8MB)を越えています。\n送信サイズ：" + urlsize);
        return false;
    }

}


function resultOutput( jsResult )
{
    var jsData = JSON.parse( jsResult );
    var sRes = "";
    sRes += "URL有効入力件数: " + jsData.all + "<br />\n";
    sRes += "登録済みURL: " + jsData.reg + "<br />\n";
    sRes += "新規登録URL: " + jsData.new + "<br />\n";
    sRes += "登録NG該当URL: " + jsData.ngu + "<br />\n";
    if ( jsData.nreg != 0 ){
        sRes += "データベースにないURL: " + jsData.nreg + "<br />\n";
    }
    $("#resultarea").html(sRes);
}



function calledaftertimeout()
{
    rewriteitems(userId);
    getList();
    $(".checkbutton").prop('disabled', false)
}

//　対象URL一覧を詳細エリアに出力
function getList()
{
    $("#detail").load("illegalsitecheckerfunctions.php", {"command": 'getSiteList', "user":userName});
}

function logout()
{
    var ans = confirm("ログアウトします。よろしいですか？" );
    if ( ans ){
        $.post("illegalsitecheckerfunctions.php",{"command": 'logout'}, function(data, status){
            window.location.href="login.php";
        });
    }
}
// 入力ボックスをクリア
function clearBox()
{
    $('#urltextbox').val("");
    $('#resultarea').text("");
}

function rewriteitems(userId)
{
    $("#countofsites").load("illegalsitecheckerfunctions.php", {"command": 'getSiteCount'});
    $("#latestregister").load("illegalsitecheckerfunctions.php", {"command": 'getLatestRegister', "userid": userId});
    //$.post("illegalsitecheckerfunctions.php", {"command": 'getLatestRegister', "userid": userId}, function(data, status, xhr){
    //    $("#latestregister").text(data);
    //});
}

//日本語テキストのバイト数を算出
function jstrlen(str) {
   var len = 0;
   str = escape(str);
   for (var i = 0; i < str.length; i++, len++) {
      if (str.charAt(i) == "%") {
         if (str.charAt(++i) == "u") {
            i += 3;
            len++;
         }
         i++;
      }
   }
   return len;
}
        </script>
    </head>
    
    <body>
        <div id="menubar">
        </div>
        <div id="maincontents">
            <h1>Check Illegal Website!</h1>
            <span id="comment1">URLを違法サイト既存データと照らし合わせ、新規URLを抽出＆登録します。</span><span id="countofsites"></span><br />
            <span>最新の登録：</span><span id="latestregister"></span>
            <div id="topform">
                URLを貼り付けて｢登録｣をクリックしてください。(最大サイズ8MB)<br />
                <form action="registerUrls.php" method="POST">
                <textarea name="inputurls" id='urltextbox'></textarea>
                <input type="submit" name="purpose[0]" value="詳細ページURL登録" class='checkbutton' onsubmit="checkURLs()" />
                <input type="submit" name="purpose[1]" value="アップローダURL登録" class='checkbutton' onsubmit="checkURLs()" />
                <input type="submit" name="purpose[2]" value="Tube系URL登録" class='checkbutton' onsubmit="checkURLs()" />
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" />
                <div><a href='' onclick='clearBox()'>ボックスをクリア</a></div><br />
                <label><input name="noregister" type="checkbox" id="noreg" value="1">登録せず、データベース参照のみ</label>
                </form>
            </div>

            <div id="resultarea">
            <!-- OUTPUT QUERY RESULT HERE  -->
            
            </div>
            <div id='detail'>
                <!-- Detail URL LIST display area -->
            </div>
        </div>
        
    </body>
</html>
