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
            -->
        </style>
        
        <script type="text/javascript">
/******************  Functions invoked when startup.  ******************/
var userId = <?php echo $_SESSION['user_id']; ?>;
var userName = '<?php echo $_SESSION['user']; ?>';
$(function(){
    

    $("#menubar").load("menubar.php", {"page_id": 2, "user":userName});
    rewriteitems(userId);
    $("#post").click(function(){
        var PostID = $(this).attr("id"); 
   }).change();

//    $("#progress").progressbar({
//        value: 1,
//        max: 100
//    });

});


/******************  Functions called from code.  ******************/
function checkURLs( purpose )
{
    var progressbar_lines = 1000;   // このURL数以上だったらプログレスバーを表示
    var urlstring = $('#urltextbox').val();
    var exist = $('#checkexist:checked').val();
    var noregister = $('#noreg:checked').val();
    urlstring = urlstring.trim();
    if ( urlstring == "" ){
        return;
    }
    if (!noregister){
        noregister = 0;
    }
    if (!exist){
        exist = 0;
    }
    
    var lines = urlstring.match(/\n/g);
    var linecount;
    if ( lines ){
        linecount = lines.length;
    } else {
        linecount = 0;
    }
//    if ( linecount > progressbar_lines ){
//        $("#progressbar_dialog").dialog('open');
//    }
    var timerId;
    var jRetVal;
    var jsData = {
        "url": urlstring,
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId
    };
/*    
    $.ajax({
        url: 'checkurl.php',
        type: 'POST',
        data: jsData
        xhrFields: {
            onloadstart: function(){
                //var jqxhr = this;
                var retVal = "";
                var iBarVal;
                var tvalue1 = "";
                var preVal = "";
                timerId = setInterval( function(){ 
                    retVal = this.responseText;
                    tvalue1 = retVal.substring(preVal.length);
                    preVal = retVal;
                    console.log("RETURN FROM PHP:" + retVal);
                    if (window.JSON){       // ブラウザがJSONに対応しているかチェック
                        jRetVal = JSON.parse(tvalue1);
                    } else {
                        jRetVal = eval("("+tvalue1+")");
                    }
                    iBarVal = parseInt(jRetVal.prog);
                    $("#progressbar_dialog").progressbar('value', iBarVal );
                    $("#loading").text(iBarVal+'%');
                }, 50);
            }
        }
                
    })
    .done(function( data ){
        clearInterval(timerId);
        var receivedData = "";
        
        if (data != "" ){
            //receivedData = JSON.parse( data );
            resultOutput( jRetVal );
        }

        //$("#resultarea").html( data );
        $(".checkbutton").prop('disabled', true);
        setTimeout("calledaftertimeout()", 500);
        if ( linecount > progressbar_lines ){
            $("#progressbar_dialog").dialog('close');
        }
    })
*/
    

/*    $("#resultarea").load("checkurl.php",{
        "url": urlstring,
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId
    }, function(response, status, xhr){
        $(".checkbutton").prop('disabled', true);
        setTimeout("calledaftertimeout()", 500);
        $(this).delay(2000).queue(function(){
            rewriteitems(userId);
            getList();
            //if (response != "" ){
            //  receivedData = JSON.parse( response );
            //  resultOutput( receivedData );
            //}
        });
//        if ( lines > progressbar_lines ){
//            $("#progressbar_dialog").dialog('close');
//        }
    });*/
    
    $.post("checkurl.php", {
        "url": urlstring,
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId
    }, function( response, status, xhr){
        $(".checkbutton").prop('disabled', true);
        setTimeout("calledaftertimeout()", 500);
        $(this).delay(500).queue(function(){
            rewriteitems(userId);
            getList();
            if (response != "" ){
              receivedData = JSON.parse( response );
              resultOutput( receivedData );
            }
        });
    });

}

function resultOutput( jsResult )
{
    var sRes = "";
    sRes += "URL有効入力件数: " + jsResult.all + "<br />\n";
    sRes += "登録済みURL: " + jsResult.reg + "<br />\n";
    sRes += "新規登録URL: " + jsResult.new + "<br />\n";
    sRes += "登録NG該当URL: " + jsResult.ngu + "<br />\n";
    if ( jsResult.nreg != 0 ){
        sRes += "データベースにないURL: " + jsResult.nreg + "<br />\n";
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

        </script>
    </head>
    <body>
        <div id="menubar">
        </div>
        <div id="maincontents">
            <h1>Check Illegal Website!</h1>
            <span id="comment1">違法サイトをチェックし、データベースに登録します。</span><span id="countofsites"></span><br />
            <span>最新の登録：</span><span id="latestregister"></span>
            <div id="topform">
                URLを貼り付けて「送信」をクリックしてください。<br />
             <textarea name="inputurls" id='urltextbox'></textarea>
                <input type="button" value="詳細ページURLチェック" class='checkbutton' onclick="checkURLs(1)" />
                <input type="button" value="アップローダーURLチェック" class='checkbutton' onclick="checkURLs(2)" />
                <input type="button" value="Tube系URLチェック" class='checkbutton' onclick="checkURLs(3)" />
                <div><a href='' onclick='clearBox()'>ボックスをクリア</a></div><br />

                <label><input name="noregister" type="checkbox" id="noreg" value="1">チェックのみで登録しない</label>
                <!--label><input name="exist" type="checkbox" id="checkexist" value="1">サイトの存在チェック(時間がかかります)</label-->
                
            </div>

            <div id="resultarea">
            <!-- OUTPUT QUERY RESULT HERE  -->
            
            </div>
            <div id='detail'>
                <!-- Detail URL LIST display area -->
            </div>
        </div>
        
        <div id="progressbar_dialog">
        </div>                                
    </body>
</html>
