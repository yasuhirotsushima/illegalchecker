<?php
/**************************************************/
/**   URL CHECKER MAIN PROGRAM                   **/
/**************************************************/

ini_set("max_execution_time", 901);      // 最長15分
// Arguments from Caller.
define('Google', 1);
define('Uploader', 2);
define('Tubes', 3);
date_default_timezone_set('Canada/Pacific');

$urlstr = filter_input( INPUT_POST, "url", FILTER_SANITIZE_STRING );
$purpose = filter_input(INPUT_POST, "purpose", FILTER_SANITIZE_NUMBER_INT);
$noreg = filter_input(INPUT_POST, "noreg", FILTER_SANITIZE_NUMBER_INT);
$user_id = filter_input(INPUT_POST, "user_id", FILTER_SANITIZE_NUMBER_INT);

// Common include file.
include "illegalsitecheck_common_inc.php";
include "mysqlclass.php";

$duration = microtime( TRUE );

session_start();

$db = dbconnect();
if ( !$db ) {
    echo "ERROR :".$db->error."<br />\n";
    $db->close();
    exit;
}

$regcnt = 0;
$aUrlList = explode("\n", $urlstr);
$aResistered = array();
$aNewurl = array();
$aNGUrl = array();
$aErrorUrl = array();
$aNoRegUrl = array();
$aVoidUrl = array();
$historyId = 0;   // Register History ID

// Return Search Results as a Array.
$aRes = array('prog' => 0, 'reg' => 0, 'new'=>0, 'ngu'=>0, 'err'=>0, 'nreg'=>0, 'all'=>0, 'void'=>0, 'hid'=>0 );

// 新規登録履歴IDの取得
$query = "SELECT MAX(id) as hid FROM register_history ";
$result = $db->query( $query );
if ( $result ){
    $row = $db->fetch_array($result);
    $historyId = $row['hid'] + 1;
} else {
    exit();
}
if ( $historyId > 0 ){
    $tm = localtime( time(), true);
    $timestr = sprintf("%d-%02d-%02d %02d:%02d:%02d",(1900+$tm['tm_year']), (1+$tm['tm_mon']), $tm['tm_mday'], $tm['tm_hour'], $tm['tm_min'], $tm['tm_sec'] );
    $query = "
            INSERT register_history (
            id,
            user_id,
            purpose_id,
            register_date
            ) VALUES (
            $historyId,
            $user_id ,
            $purpose ,
            '$timestr'
            )";

    $result = $db->query( $query );
}
$aRes['hid'] = $historyId;
$urlCount = count($aUrlList);
$iProgress = 0;      // Percentage value of Progress.
$prev_progress = 0;  // Previous value If value changed.

resetTable( "newregister" );
resetTable( "registeredurl" );
resetTable( "regngurl" );
resetTable( "voidurl" );

// Main Loop by URL in Given URLs from index.php
foreach( $aUrlList as $sUrl){
    $fNoRegister = FALSE;
    $fExist = FALSE;
    $sUrl = trim( $sUrl );
    if ( $sUrl == "" ) {
        continue;
    }
    $regcnt++;

    //URLが「http://」、「https://」で始まらない場合は無効URLバッファに入れて処理をスキップ
//    if ( !preg_match('/^(http.?):\/\//', $sUrl)) {
//        array_push($aVoidUrl, $sUrl);
//        continue;
//    }
//    if( substr( $sUrl, -1) == "/" ){    // URL末尾の/を削除
//        $sUrl = substr( $sUrl, 0, (strlen($sUrl) - 1) ); 
//    }
    
    // FC2アダルトのURLだった場合の処理
    //　ドメインがhttp://video.fc2.com/　で始まる場合、続く言語パラメータを無視し
    //　すべて同じURLとみなして処理する
    //　例)　http://video.fc2.com/en/a/?contents　->　http://video.fc2.com/a/?contents
    //　　　http://chenghuavideo.asia/a/?contents　->　http://video.fc2.com/a/?contents
//    $iPos = strripos($sUrl, "http://chenghuavideo.asia");
//    if ( $iPos !== false ) {
//        $sUrl = preg_replace('/http:\/\/chenghuavideo.asia/', 'http://video.fc2.com', $sUrl, 1);
//    }
//    $iPos = strripos($sUrl, "video.fc2.com");
//    if ( $iPos !== false ) {
//        $sUrl = preg_replace('/\/ja\/|\/en\/|\/tw\/|\/ko\/|\/de\/|\/es\/|\/fr\/|\/ru\/|\/id\/|\/pt\/|\/vi\//', '/', $sUrl, 1);
//    }
    
        
    
    // プログレスバー用の値を作成、出力
    $iProgress = round( ($regcnt / $urlCount) * 100 );
    if ($iProgress != $prev_progress){
        $aRes['prog'] = $iProgress;
        //echo $iProgress;
        echo str_pad(" ", 4096);
        echo json_encode( $aRes );
        @ob_flush();
        @flush();
        $prev_progress = $iProgress;
    }
    
    $urlstr = getGenuinURL($sUrl);       //指定されたURLの最後のhttp://(またはhttps://)以降を取得
    $sEscapedUrl = mysql_real_escape_string($urlstr);
    
    
    $sql = "
    SELECT id, s_url
    FROM urls
    WHERE s_url = '".urlencode($sEscapedUrl)."'";
//echo "SELECT QUERY : ".$sql."<br />\n";
    
    // NGドメインがURLに入っているかチェック.
    $ret = checkNGCorrespond( $db, $sEscapedUrl );
    if ( $ret ){
        array_push($aNGUrl, $sEscapedUrl);
        continue;
    }

    $result = $db->query( $sql );     // サイトが登録されているかチェック
    if ( $db->errno != 0 ){
        echo "SQL ERROR :".$db->error."<br />\n";
        echo "QUERY= ".$sql."<br />\n";
        $db->close();
        exit();
    }
    if ( $db->getselectrownum($result) > 0 ){       // サイトがすでに登録されている場合
        $rec = $db->getrow($result);
        $fExist = TRUE;
        $fNoRegister = TRUE;
    }
    if ( $fExist ){                                  // すでに登録されていた場合
        array_push($aResistered, $sEscapedUrl); // 登録済みバッファに入れて次のURLへ。
        $fNoRegister = TRUE;
        continue;
    } else {        // 新規登録対象URLだった場合の処理
        addRecordIntoDB($sEscapedUrl, $historyId, 'newregister'); //表示用テーブルへ登録
        if ( $noreg == 1 ){                             // 
            array_push($aNoRegUrl, $sEscapedUrl); // 登録URLバッファに入れて次のURLへ。
            $fNoRegister = TRUE;
            continue;
        } else {
            // INSERT Query
            $que = makeInsertSQL( urlencode($sEscapedUrl), $purpose, $historyId );
        }
    }

    if ( !$fNoRegister ){    // URLが登録対象だったらURLテーブルに登録.
        // INSERT Record.
        $db->query($que);
        if ( $db->errno != 0 ){
            echo "ERROR: ".$db->error."<br />\n";
        } else {
            array_push($aNewurl, $sEscapedUrl );
        }
    }
}

// 新規登録URLがなかった場合、登録履歴テーブルから履歴を削除
if ( count($aNewurl) == 0 ){
    $query = " DELETE FROM register_history WHERE id = $historyId ";
    $result = $db->query( $query );
}

//if ( $noreg == 1 ){
//    addRecordIntoDB($aNoRegUrl, $historyId, 'newregister');
//} else {
//    addRecordIntoDB($aNewurl, $historyId, 'newregister');
//}

addListIntoDB( $aNGUrl, $historyId, "regngurl" );               // 登録NGURLをDBに登録
addListIntoDB( $aResistered, $historyId, "registeredurl" );     // 登録済みURLをDBに登録
addListIntoDB( $aVoidUrl, $historyId, "voidurl" );              // 無効URLをDBに登録

$aRes['all'] = $regcnt;
$aRes['ngu'] = count($aNGUrl);
$aRes['new'] = count($aNewurl);
$aRes['reg'] = count($aResistered);
$aRes['nreg'] = count($aNoRegUrl);
$aRes['void'] = count($aVoidUrl);


echo json_encode($aRes);

$db->close();

$duration -= microtime( TRUE );
//echo "<br />Render time ". round( abs($duration), 5 )."　sec.<br />\n";



/************************************************************************/
/************   Main Routine end. Follows are Functions. ****************/
/************************************************************************/
function checkDB($query)
{
    global $db; 
    $res = FALSE;
    $result = $db->query( $query );
    if ( $db->errno != 0 ){
        echo "SQL ERROR :".$db->error."<br />\n";
        $db->close();
    } else if ( $db->getselectrownum($result) > 0 ){       // サイトがすでに登録されている場合
        $res = TRUE;
    }
    return $res;
}

// Try to access specified URL and check that URL exists
function checkURLexists( $url ){
    $ret = FALSE;
    $header = get_headers(urldecode($url));
    if ( preg_match('/HTTP|200/i',$header[0]) ){
        // When matched, URL Exists
        $ret = TRUE;
    }
    return $ret;
}

// Remove wrapping URL and return target URL
function getGenuinURL( $url )
{
    if ( $pos = strpos( $url, "https://" ) ){
        $urlarrey = preg_split( '/https:\/\//i', $url );
        $ret = 'https://'.$urlarrey[ count($urlarrey)-1 ];
    } else {
        $urlarrey = preg_split( '/http:\/\//i', $url );
        $ret = 'http://'.$urlarrey[ count($urlarrey)-1 ];
    }
    
    $iPos = strrpos($ret, '#');
    if ( $iPos ){
        $ret = substr($ret, 0, $iPos);
    }
    
    return $ret;
}

// Check URL corresponds to NG Domain list
function checkNGCorrespond($db, $mysqlescapedstr)
{
    $ret = FALSE;
    // Check NG Domain name includes URL from DB.
    $domain1 = strstr($mysqlescapedstr, "://");
    if (!$domain1){
        $domain2 = $mysqlescapedstr;
    } else {
        $domain2 = substr( $domain1, 3, strlen($domain1)-3);
    }
    //$domain3 = strstr( $domain2, "/", true);  //PHP5.3 or Later
    $awork = explode( "/", $domain2);
    $domain3 = $awork[0];
    $chkque = "SELECT COUNT(domainname) as cnt FROM ngdomainlist WHERE INSTR( '".urlencode($mysqlescapedstr)."', domainname )"; 
//echo "D1:".$domain1."<br />";
//echo "D2:".$domain2."<br />";
//echo "D3:".$domain3."<br />";
//    echo "CHKQUE: ".$chkque."<br />";
    $result = $db->query($chkque);
    if ( $db->errno == 0 ){
        $row = $db->getrow($result);
        if ($row['cnt'] > 0){
            $ret = TRUE;
        }
    }
    return $ret;
}

// Create SELECT Query
function makeSelectSQL( $url, $option )
{
    $sql = "
    SELECT id, s_url
    FROM urls
    WHERE s_url = '".$url."'";
    
    return $sql;
}

// Create INSERT Query
function makeInsertSQL( $url, $purpose, $hid )
{
    $reported = 1;  // Fixed value TRUE
//echo $now->format('Y-m-d H:i');
    $sqlstr = "
    INSERT urls 
        ( 
        id, 
        s_url, 
        b_reported,
        purpose,
        register_history_id 
        ) VALUES (
        NULL,
        '$url',
        $reported,
        $purpose,
        $hid
    )";
    return $sqlstr;
}

function addRecordIntoDB( $sData, $regid, $tblname )
{
    if ( $db = dbconnect()) {
        $query = "INSERT $tblname (id, register_id, url) VALUES ( NULL, ".$regid.", '".urlencode($sData)."') ";
        $result = $db->query($query);
        if (!$result){
            echo "INSERT $tblname FAILED<br />\n";
            return;
        }
    }
    return;
}
function addListIntoDB( $aData, $regid, $tblname )
{
    if ( $db = dbconnect()) {
        foreach( $aData as $data ){
            $query = "INSERT $tblname (id, register_id, url) VALUES ( NULL, ".$regid.", '".urlencode($data)."') ";
            $result = $db->query($query);
            if (!$result){
                echo "INSERT $tblname FAILED<br />\n";
                return;
            }
        }
    }
    return;
}

function resetTable( $tablename )
{
    $query = "TRUNCATE ".$tablename;
    if ( $db = dbconnect()) {
        $result = $db->query($query);
    }
    return $result;
}
