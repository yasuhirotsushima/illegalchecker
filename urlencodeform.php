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
            
            #detail {
                border: 1px solid #9FAFD1;
                padding: 0px;
                width: 100%;
                height: 350px;
                font-size: 10px;
                overflow-y: scroll;
            }
            #latestregister {
                color: #FF0000;
                font-weight: bold;
            }
            -->
        </style>
        
        <script type="text/javascript">
/******************  Functions invoked when startup.  ******************/


/******************  Functions called from code.  ******************/
function decode( )
{
    var urlstring = $('#urltextbox').val();
    var encode = $('#encode:checked').val();

    var aUrls = urlstring.split("\n");
    var inputurls = 0;
    var decodedurrls = 0;
    var errorurls = 0;
    
    for ( var i=0; i<aUrls.length; i++ ){
        var url = aUrls[i];
        if ( url == "" ){
            continue;
        }
        
        if ( url.slice( -1 ) == '%' ){
            url = url.slice(0, -1)
        }
        inputurls++;
        try {
            if (encode == 1) {
                var durl = encodeURIComponent(url);
            } else {
                var durl = decodeURIComponent(url);                
            }
            $("#detail").append(durl+"<br />\n");
            decodedurrls++;
        } catch(e) {
              errorurls++;
              $("#resultarea").html( "ERROR!" + e.toString() );
        }
    }
    //$("#resultarea").html("入力URL件数:" + inputurls + "件<br />\n");
    $("#resultarea").append("デコード/デコードした件数:" + decodedurrls + "件<br />\n");
    $("#resultarea").append("デコード/デコード失敗件数:" + errorurls + "件<br />\n");
    //alert("ErrorURL:" + errorurls.length );
    //for (var url in aUrls){
    //    $("#detail").add(decodeURI(url));
    //}
    
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


        </script>
    </head>
    <body>
        <div id="menubar">
        </div>
        <div id="maincontents">
            <h1>Decode URL encoded text!</h1>
            <span id="comment1">URLエンコードされた文字列をデコードします。</span><span id="countofsites"></span><br />
            <div id="topform">
                URLを貼り付けて「送信」をクリックしてください。<br />
             <textarea name="inputurls" id='urltextbox'></textarea>
                <input type="button" value="送信" class='checkbutton' onclick="decode()" />
                <div><a href='' onclick='clearBox()'>ボックスをクリア</a></div><br />
                <label><input name="encode" type="checkbox" id="encode" value="1">エンコードする。</label>
                
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
