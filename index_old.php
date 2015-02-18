<?php
include "illegalsitecheck_common_inc.php";

logincheck(INDEXPAGE);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta charset="UTF-8">
        <title>Illegal site checker</title>
	<script src="js/jquery-1.11.1.js"></script>
	<!--script src="js/jquery-ui.min.js"></script-->
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
    rewriteitems(userId);
    $("#post").click(function(){
        var PostID = $(this).attr("id"); 
   }).change();

   /*$("#progressbar_dialog").dialog({
        autoOpen: false,
        title: 'Processing',
        height: 250,
        width: 500,
        modal: true,
        draggable: false,
        closeOnEscape: false,
        resizable: false,
        buttons: {
            'キャンセル':  function(){ 
                if (confirm("キャンセルしますか?データの登録は完了しません。")){
                    // キャンセル処理を記述
                    cancellProc();
                    $(this).dialog('close');
                }
            }
        }
    });

    $("#progress").progressbar({
        value: 0,
        max: 100
    });*/

});


/******************  Functions called from code.  ******************/
function checkURLs( purpose )
{
    if (purpose <= 0){
        return false;
    }
    $(".checkbutton").prop('disabled', true);   //　連続クリック防止のためボタン表示をオフ
    
    var progressbar_lines = 10;   // このURL数以上だったらプログレスバーを表示

        var devide_lines = 1001;   // このURL数以上分割して送信
        
    var urlstring = $('#urltextbox').val();
    var exist = $('#checkexist:checked').val();
    var noregister = $('#noreg:checked').val();
    if (!noregister){
        noregister = 0;
    }
    if (!exist){
        exist = 0;
    }
    
    urlstring = urlstring.trim();
    if ( urlstring == "" ){
        return;
    }

    //var lines = urlstring.match(/\n/g);
    var aLines = urlstring.split('/\r\n|\r|\n/');
    var linecount;
    if ( aLines ){
        linecount = aLines.length;
    } else {
        linecount = 0;
    }
        
    
//    if ( linecount > limit_lines ){
//        alert(" 入力URL件数が最大値を超えています。(入力 " + linecount  + " 件)");
//        $(".checkbutton").prop('disabled', false);
//        return false;
//    }
        
    var timerId;
    var jRetVal;
    var history_id;
    var totalCnt = {
        all: 0,
        reg: 0,
        new: 0,
        gnu: 0,
        nreg: 0
    };
    
//    if ( linecount > progressbar_lines ){
//        $("#progressbar_dialog").dialog('open');
//    }

    // 履歴テーブルに登録し、履歴IDを取得
    $.post("illegalsitecheckerfunctions.php",{"command": 'getNewHistoryId', "userid":userId, "purpose":purpose}, function(data, status, xhr){
        if (data > 0 ){
            history_id = parseInt( data );
        } else {
            alert("HISTORY GET ERROR." + data );
            return false;
        }
    });
    
    var jsData = {
        "url": "",
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId,
        "hid": history_id
    };
    
    //********** メインループ　URL件数分まわす **********
    for (var proc_cnt=0; proc_cnt < linecount; proc_cnt++ ){
        var url = aLines[proc_cnt];
        url = $.trim(url);
        if ( url == ""){
            continue;
        }
        
        var check = url.match('/^(http?):\/\//');
        if (check){
            continue;
        }
        jsData.url = url;


        // プログレスバー用の値を作成(0-100%)
//        iProgress = ( (proc_cnt / linecount) * 100 );
//        iProgress = Math.rount(iProgress);
//        if (iProgress !== prev_progress){
//            prev_progress = iProgress;
//        }

//        $.ajax({
//            url: 'checkurl_each.php',
//            type: 'POST',
//            data: jsData
//            //            
//        })
//        .done(function( response, status, xhrSuccess ){
//            if (response != "" ){
//                try {
//                    var work1 = response.split('{');
//                    var work2 = '{' + work1[(work1.length - 1)];
//                    //console.log("RES_JSON:" + work2);
//                    var receivedData = JSON.parse( work2 );
//                    
//                    totalCnt.all += parseInt(receivedData.all);
//                    totalCnt.reg += parseInt(receivedData.reg);
//                    totalCnt.new += parseInt(receivedData.new);
//                    totalCnt.gnu += parseInt(receivedData.gnu);
//                    totalCnt.nreg += parseInt(receivedData.nreg);
//                    
//                } catch(e){
//                    console.log("JSON ERR:" + work2);
//                    $("#resultarea").html( "JSON PARSE ERROR: " + work2 + "<br />\n" );
//                }
//            }
//            rewriteitems(userId);
//            getList();
//            calledaftertimeout();
//
//        })
//        .fail(function( response, status, xhrFail  ){
//            console.log( "JQ ERROR: " + response + " STATUS :" + status );
//        })

    $.post("checkurl_each.php", {
        "url": url,
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId,
        "hid": history_id
    }, function( response, status, xhr){
        if (response != "" ){
            try {
                var work1 = response.split('{');
                var work2 = '{' + work1[(work1.length - 1)];
                //console.log("RES_JSON:" + work2);
                var receivedData = JSON.parse( work2 );

                totalCnt.all += parseInt(receivedData.all);
                totalCnt.reg += parseInt(receivedData.reg);
                totalCnt.new += parseInt(receivedData.new);
                totalCnt.gnu += parseInt(receivedData.gnu);
                totalCnt.nreg += parseInt(receivedData.nreg);
            } catch(e){
                $("#resultarea").html( "JSON PARSE ERROR: " + work2 + "<br />\n" );
            }
        }
        rewriteitems(userId);
        getList();
        calledaftertimeout();
    });



    }
//************** Main Loop END**************


    resultOutput( totalCnt );
    
    if ( linecount > progressbar_lines ){
        $("#progressbar_dialog").dialog('close');
    }

//    $.post("checkurl.php", {
//        "url": urlstring,
//        "purpose": purpose,
//        "noreg": noregister,
//        "checkexist": exist,
//        "user_id":  userId
//    }, function( response, status, xhr){
//        if (response != "" ){
//            try {
//                var work1 = response.split('{');
//                var work2 = '{' + work1[(work1.length - 1)];
//                //console.log("RES_JSON:" + work2);
//                var receivedData = JSON.parse( work2 );
//                resultOutput( receivedData );
//            } catch(e){
//                $("#resultarea").html( "JSON PARSE ERROR: " + work2 + "<br />\n" );
//            }
//        }
//        rewriteitems(userId);
//        getList();
//        calledaftertimeout();
//    });

}
//************** Main Loop END**************


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

function cancellProc()
{
    $.post("illegalsitecheckerfunctions.php", {"command": 'deleteForCancelled'}, function(data, status, xhr){
        $("#latestregister").text(data);
    });
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
                URLを貼り付けて｢登録｣をクリックしてください。(最大3000件)<br />
             <textarea name="inputurls" id='urltextbox'></textarea>
                <input type="button" value="詳細ページURL登録" class='checkbutton' onclick="checkURLs(1)" />
                <input type="button" value="アップローダURL登録" class='checkbutton' onclick="checkURLs(2)" />
                <input type="button" value="Tube系URL登録" class='checkbutton' onclick="checkURLs(3)" />
                <div><a href='' onclick='clearBox()'>ボックスをクリア</a></div><br />

                <label><input name="noregister" type="checkbox" id="noreg" value="1">登録せず、データベース参照のみ</label>
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
            <label for="title">処理中・・・・</label>
            <div id="progress"><div id="loading"></div></div>
        </div>                                
    </body>
</html>
