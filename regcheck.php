<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Reg checker</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
        <style type="text/css">
            <!--
body {
    margin: 0;
    padding: 0;
    font-family: sans-serif;
    font-size: 11pt;
}
h1, h2 { color: #666 }
h3 { 
    color: #4E6CA3;
    margin-bottom: 5px;
}
#h3_replace {
    color: #ddd;
}

#maincontents {
    width: 80%;
    padding: 15px;
    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;
    height: auto;
}
div .inputarea {
    width : 600px;
    margin-bottom: 10px;
}
.exec {
    margin-top: 5px;
    margin-bottom: 5px;
    margin-left: 15px;
}
.desc {
    font-size: 80%;
    color: red;
}

.optionbox {
    width: 450px;
    font-size: 80%;
    background-color: #B0BED9;
    border-radius: 10px;
    margin: 5px;
    padding: 5px;
}
            -->            
        </style>
        <script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
        <script type="text/javascript">
            <!--
$(function(){
    cls();
    
    $("input:radio[name='mode']").change(function(){
        var radioval = $("input:radio[name='mode']:checked").val();
        switch( radioval ){
            case "2" :
                $("input:checkbox[name='gflg']").attr("checked", false);
                $("input:checkbox[name='gflg']").attr("disabled", "disabled");
                $("#replacestr").attr("disabled", "disabled");
                $("#h3_replace").css("color", "#ddd");
                break;
            case "3" :
                $("#replacestr").removeAttr("disabled");
                $("input:checkbox[name='gflg']").removeAttr("disabled");
                $("#h3_replace").css("color", "#4E6CA3");
                break;
            default :
                $("input:checkbox[name='gflg']").removeAttr("disabled");
                $("#replacestr").attr("disabled", "disabled");
                $("#h3_replace").css("color", "#ddd");
                break;
        }

    })
});

function cls()
{
    $("textarea").val("");
    $("#patternstr").val("");
    $("#replacestr").val("");
    //$("input:radio[name='mode']").val(1);
    $("input:radio[name='mode']:first").prop("checked", true);
    $("input:checkbox").attr("disabled", false);
}

function regexec()
{
    var sText = $("#instr").val();
    var sPattern = $("#patternstr").val();
    var iMethod = parseInt($("input:radio[name='mode']:checked").val());
    var sMode = "";
    $("input:checkbox[name$='flg']:checked").each(function(index, checkbox){
        sMode += $(checkbox).val();
    })
    var regExp = new RegExp(sPattern, sMode);
    
    $("span#exp").val(regExp.toString());
    
    switch(iMethod){
        case 1:
            var sResult = sText.match(regExp);
            //var sResult = regExp.test(sText);
            if ( sResult ){
                $("#res").val(sResult);
            } else {
                $("#res").val("No Match.");
            }
            break;
        case 2:
            //var sResult = sText.split(sPattern);
            var reg = /\s/;
            var sResult = sText.split(regExp);
            if ( sResult ){
                $("#res").val(sResult);
            }
            break;
        case 3:
            var sReplaceText = $("#replacestr").val();
            var sResult = sText.replace(regExp, sReplaceText)
            if ( sResult ){
                $("#res").val(sResult);
            }
            break;
        default :
            break;
    }
}
            -->
        </script>
    </head>
    <body>
        <div id="maincontents">
            <h1>REGEX CHECK</h1>
            <div class="inputarea">
                <h3>対象文字列</h3>
                <textarea id="instr" name="instr" rows="6" cols="50"></textarea>
            </div>
            <div class="inputarea">
                <h3>正規表現パターン文字列</h3>
                <span class="desc">※　「/」でくくる必要はありません。</span><br />
                <input id="patternstr" type="text" name="pattern" value="" size="70" />
            </div>
            <div class="inputarea">
                <h3 id="h3_replace">置換時の置換文字列</h3>
                <input id="replacestr" type="text" name="replace" value="" size="70" disabled="disabled" />
            </div>
            <div>
                <label><input type="radio" name="mode" value="1" checked="checked" />match</label>
                <label><input type="radio" name="mode" value="2" />split</label>
                <label><input type="radio" name="mode" value="3" />replace</label>
            </div>
            <div class="optionbox">
                <label><input type="checkbox" name="gflg" value="g" />グローバルマッチ(g)</label><br />
                <label><input type="checkbox" name="iflg" value="i" />大文字小文字を無視(i)</label><br />
                <label><input type="checkbox" name="mflg" value="m" />複数行にマッチ(m)</label>
            </div>
            <input class="exec" type="button" value="クリア" name="clear" onclick="javascript:cls();" />
            <input class="exec" type="button" value="実行" name="exec" onclick="javascript:regexec();" />
            <div>
                <h3>結果</h3>
                <textarea id="res" name="res" rows="6" cols="50" readonly="readonly"></textarea>
            </div>
        </div>
    </body>
</html>
