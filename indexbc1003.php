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
                font-size: 14px;
            }
            #registered {
                border: 1px solid #9FAFD1;
                height: 100px;
                overflow-y: scroll;            
            }
            #regng {
                border: 1px solid #9FAFD1;
                height: 100px;
                overflow-y: scroll;            
            }
            #void {
                border: 1px solid #9FAFD1;
                height: 100px;
                overflow-y: scroll;            
            }
            #double {
                border: 1px solid #9FAFD1;
                height: 100px;
                overflow-y: scroll;            
            }
            #resultarea a {
                text-decoration: none;
                color: #FF0000;
            }
            span.labeltext {
                color: #b81900;
                font-size: 90%;
                text-decoration: none;
                font-weight: bold;
            }
            span#allurlcnt {
                font-weight: bold;
                text-decoration: underline;
            }
            span#availablecnt {
                text-decoration: underline;
            }
            div.listarea{
                margin-bottom: 10px;
            }
            .no-close .ui-dialog-titlebar-close {
                    display: none;
            }
            -->
        </style>
        
        <script type="text/javascript">
/******************  Functions invoked when startup.  ******************/
var userId = <?php echo $_SESSION['user_id']; ?>;
var userName = '<?php echo $_SESSION['user']; ?>';
var max_size = 3000000;     // POSTで送る最大サイズ。これを超えたら警告。
var ajaxId;
var aDelUrlList = new Array();
var aVoidUrlList = new Array();
var hid = 0;    // Register Hostory ID

$(function(){
    $("#menubar").load("menubar.php", {"page_id": 2, "user":userName});
    rewriteitems(userId);
    $("#post").click(function(){
        var PostID = $(this).attr("id"); 
   }).change();

   $("#progressbar_dialog").dialog({
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
                    ajaxId.abort();
                    $(this).dialog('close');
                }
            }
        },
    });

    $("#progress").progressbar({
        value: 0,
        max: 100
    });
    
    $("#maxsize").text(max_size /1000);
    
    $("#urltextbox").keyup( function(){
        var txt = $(this).val();
        var length = jstrlen(txt);
        var lengthkb = length / 1000;
        lengthkb = lengthkb.toFixed(3);
        $("#textSize").text(lengthkb.toString() + " KB");
        if (lengthkb > (max_size / 1000)){
            $("#textSize").css("color", "red");
        } else {
            $("#textSize").css("color", "black");
        }
    });
    

});


/******************  Functions called from code.  ******************/
function checkURLs( purpose )
{
        
    var urlstring = $('#urltextbox').val();

    var urlsize = jstrlen(urlstring);
    var urlsizekb = urlsize / 1000
    if ( urlsize > max_size ){
        alert("送信できる最大サイズ(" + (max_size /1000) + " KB)を越えています。\n送信サイズ：" + urlsizekb.toFixed(3) + "KB");
        return false;
    }
    
    var exist = $('#checkexist:checked').val();
    var noregister = $('#noreg:checked').val();
    urlstring = urlstring.trim();
    if ( urlstring == "" ){
        return;
    }


    $(".checkbutton").prop('disabled', true);   //　連続クリック防止のためボタン表示をオフ
    $("#progressbar_dialog").dialog('open');    // プログレスバーダイアログ表示
    
    // 重複URLを入力文字列から削除
    var lines = checkDoubledUrl( urlstring );
    
    //var lines = urlstring.match(/\n/g);
    var linecount;
    if ( lines ){
        linecount = lines.length;
    } else {
        linecount = 0;
    }

    if (!noregister){
        noregister = 0;
    }
    if (!exist){
        exist = 0;
    }
    
    var timerId;
    var jRetVal;
    var jsData = {
        "url": lines.join("\n"),
        "purpose": purpose,
        "noreg": noregister,
        "checkexist": exist,
        "user_id":  userId
    };
    
    $("#dialogmessage").text("処理中・・・");
    
    ajaxId = $.ajax({
        timeout:900000,        // タイムアウト15min.
        url: 'checkurl.php',
        type: 'POST',
        data: jsData,
        xhrFields: {
            onloadstart: function(){
                var xhr = this;
                var retVal = "";
                var iBarVal;
                var tvalue1;
                var tvalue2;
                try{
                    timerId = setInterval( function(){ 
                        retVal = xhr.responseText;
                        if (retVal !== "" && retVal.slice(-1) == '}' ){
                            tvalue1 = retVal.split('{');
                            tvalue2 = '{' + tvalue1[(tvalue1.length - 1)];
                            if ( tvalue2.slice(-1) == '}'){
                                if (window.JSON){       // ブラウザがJSONに対応しているかチェック
                                    jRetVal = JSON.parse(tvalue2);
                                } else {
                                    jRetVal = eval("("+tvalue2+")");
                                }
                                iBarVal = parseInt(jRetVal.prog);
                                if (!isNaN(iBarVal)){
                                    console.log("PROGRESSBAR VAL:" + iBarVal);
                                    if ( iBarVal > 0 && iBarVal <= 100 ){
                                        $("#progress").progressbar('value', iBarVal );
                                        $("#loading").text(iBarVal+'%');
                                    }
                                }
                                hid = parseInt(jRetVal.hid);
                            }
                        }
                    }, 1000);
                }catch(e){
                    clearInterval(timerId);
                    console.log("ERROR: AJAX" + e);
                }
            }
        }
                
    })
    .done(function( response, status, xhrSuccess ){
        clearInterval(timerId);
        $("#progressbar_dialog").dialog('close');
        if (response != "" ){
            try {
                var work1 = response.split('{');
                var work2 = '{' + work1[(work1.length - 1)];
                //console.log("RES_JSON:" + work2);
                var receivedData = JSON.parse( work2 );
                
                resultOutput( receivedData );
            } catch(e){
                $("#resultarea").html( "JSON PARSE ERROR: " + work2 + "<br />\n" );
            }
        }
                
        rewriteitems(userId);
        getList();
        getOtherLists( receivedData );  // Get Other data into page.
        calledaftertimeout();
        
    })
    .fail(function( response, status, xhrSuccess  ){
        $("#progressbar_dialog").dialog('close');
        if ( status != "abort"){
            alert("Error : " + status );            
        }
        $(".checkbutton").prop('disabled', false)
        console.log( "JQ ERROR: " + response + " STATUS :" + status );
    })
    .complete(function( response, status, xhrcomp ){
        if ( (status == 'abort' || status == 'timeout') && hid > 0){
            setTimeout( function(){
                $.post("illegalsitecheckerfunctions.php",{"command": 'deleteHistoryRecord', "hid": hid}, function(data, status, xhr){});                
                $("#resultarea").html("中断しました。<br />\n");
            }, 3000 );
        }
    })
}

function resultOutput( jsResult )
{
    var sRes = "";
    sRes += "<span id='allurlcnt'>全入力URL件数: " + (parseInt(jsResult.all) + aDelUrlList.length + aVoidUrlList.length) 
    sRes += " - <a href='#double'>重複URL: " + aDelUrlList.length + "</a>";
    sRes += " - <a href='#void'>無効なURL: " + aVoidUrlList.length + "</a></span><br />\n";
    sRes += "<span id='availablecnt'>URL有効入力件数: " + jsResult.all + "</span><br />\n";
    if ( jsResult.nreg != 0 ){
        sRes += "データベースにないURL: \t" + jsResult.nreg + "<br />\n";
    } else {
        sRes += "<a href='#regnew'>新規登録URL: \t" + jsResult.new + "</a><br />\n";
    }
    sRes += "<a href='#registered'>登録済みURL: \t" + jsResult.reg + "</a><br />\n";
    sRes += "<a href='#regng'>登録NG該当URL: \t" + jsResult.ngu + "</a></span><br />\n";
        
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

// その他データをリストに出力
function getOtherLists( jsResult )
{
    if ( jsResult.reg > 0){
        $("#registered").load("illegalsitecheckerfunctions.php", {"command": 'getOtherList', "tablename":'registeredurl'});
    }
    if ( jsResult.ngu > 0){
        $("#regng").load("illegalsitecheckerfunctions.php", {"command": 'getOtherList', "tablename":'regngurl'});
    }

    if ( aVoidUrlList.length > 0 ){
        var htmlVoidList = "<table id='resulttable'>\n";
        aVoidUrlList.forEach(function( element, index ){
            htmlVoidList += "<tr><td class='td1'>" + element + "</td></tr>\n";
        });
        htmlVoidList += "</table>\n";
    }
    $("#void").html( htmlVoidList );

    if ( aDelUrlList.length > 0 ){
        var htmlDoubledList = "<table id='resulttable'>\n";
        aDelUrlList.forEach(function( element, index ){
            htmlDoubledList += "<tr><td class='td1'>" + element + "</td></tr>\n";
        });
        htmlDoubledList += "</table>\n";
    }
    $("#double").html( htmlDoubledList );
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

// キャンセルボタンクリック時処理
function cancellProc()
{
    ajaxId.abort();     // ajax通信を中断
    //$.post("illegalsitecheckerfunctions.php",{"command": 'deleteHistoryRecord', "hid": hid}, function(data, status, xhr){
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

// URL文字列から重複するURLを削除
function checkDoubledUrl( sUrlList )
{

    // FC2アダルトのURLだった場合の処理
    //　ドメインがhttp://video.fc2.com/　で始まる場合、続く言語パラメータを無視し
    //　すべて同じURLとみなして処理する
    //　例)　http://video.fc2.com/en/a/?contents　->　http://video.fc2.com/a/?contents
    //　　　http://chenghuavideo.asia/a/?contents　->　http://video.fc2.com/a/?contents
    var regexp1 = new RegExp('http:\/\/chenghuavideo.asia', 'g');
    sUrlList = sUrlList.replace(regexp1, 'http://video.fc2.com');

    var regexp2 = new RegExp('\/ja\/|\/en\/|\/tw\/|\/ko\/|\/de\/|\/es\/|\/fr\/|\/ru\/|\/id\/|\/pt\/|\/vi\/', 'g');
    sUrlList = sUrlList.replace(regexp2, '/');

    var aUrlList = sUrlList.split(/\r\n|\r|\n/);
    var iUrlCnt = aUrlList.length;  // オリジナルの入力ライン数
    var sUrl;
    var cUrl;
    var idx = 0;
    var iDelCnt = 0;
    aDelUrlList = [];
    aVoidUrlList = [];
    aUrlList.forEach(function(element, index){
        aUrlList[index] = getGenuinURL(element);
    });
    while( idx < iUrlCnt) {
        sUrl = aUrlList[idx];

        if ( sUrl == "" ){          // Remove Blank lines
            aUrlList.splice(idx, 1);
            iUrlCnt--;
            continue;
        }
        if ( sUrl.slice(sUrl.length - 1) == '/' ){
             sUrl = sUrl.slice(0, (sUrl.length - 1) );
        }
        
        if ( !sUrl.match(/^http.?:\/\//) ) {  // Remove void URLs
            aVoidUrlList.push( sUrl );
            aUrlList.splice(idx, 1);
            iUrlCnt--;
            continue;
        }
        sUrl = getGenuinURL( sUrl );
        for ( var cnt = iUrlCnt - 1; cnt > idx; cnt-- ){
            cUrl = aUrlList[cnt];
            if ( cUrl.slice(cUrl.length - 1) == '/' ){
                cUrl = cUrl.slice(0, (cUrl.length - 1) );
            }
            //cUrl = getGenuinURL(cUrl);
            if ( sUrl === cUrl ){
                aUrlList.splice(cnt, 1);
                aDelUrlList.push( sUrl );
                iUrlCnt--;
                iDelCnt++;
            }
        }
        idx++;
    } 
     
    return aUrlList;
}

// Remove wrapping URL and return target URL
function getGenuinURL( url )
{
    var regex = new RegExp(/http.?:\/\//);
    var ret = "";
    var urlarrey = url.split( regex );
    if ( url.indexOf("https://" ) >= 0 ){
        ret = 'https://' + urlarrey[ urlarrey.length -1 ];
    } else {
        ret = 'http://' + urlarrey[ urlarrey.length -1 ];
    }
    return ret;
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
                URLを貼り付けて該当する「登録｣ボタンをクリックしてください。(最大<span id="maxsize"></span>KB) 現在の入力サイズ:<span id="textSize"></span><br />
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
            
            <span class="labeltext" name="regnew">今回新規登録、またはデータベースに登録されていない報告対象URLが下記に表示されます。</span>
            <div class="listarea" id='detail'>
                <!-- Detail URL LIST display area -->
            </div>
            <span class="labeltext" name="registered">データベース登録済URL</span>
            <div class="listarea" id='registered'>
                <!-- Detail URL LIST display area -->
            </div>
            <span class="labeltext" name="regng">登録NG該当URL</span>
            <div class="listarea" id='regng'>
                <!-- Detail URL LIST display area -->
            </div>
            <span class="labeltext" name="void">無効なURL（httpで始まっていないなど、記述形式が合っていない）</span>
            <div class="listarea" id='void'>
                <!-- Detail URL LIST display area -->
            </div>
            <span class="labeltext" name="double">入力リスト内で重複していたURL</span>
            <div class="listarea" id='double'>
                <!-- Detail URL LIST display area -->
            </div>
        </div>
        
        <div id="progressbar_dialog">
            <span id="dialogmessage">処理を開始しています</span>
            <div id="progress"><div id="loading"></div></div>
        </div>                                
    </body>
</html>
