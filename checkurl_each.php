<?php
// Arguments from Caller.
define('Google', 1);
define('Uploader', 2);

$urlstr = filter_input( INPUT_POST, "url", FILTER_SANITIZE_STRING );
$historyId = filter_input(INPUT_POST, "hid", FILTER_SANITIZE_NUMBER_INT);
$purpose = filter_input(INPUT_POST, "purpose", FILTER_SANITIZE_NUMBER_INT);
$noreg = filter_input(INPUT_POST, "noreg", FILTER_SANITIZE_NUMBER_INT);
//$checkphisical = (bool) filter_input(INPUT_POST, "checkexist", FILTER_SANITIZE_NUMBER_INT);
$user_id = filter_input(INPUT_POST, "user_id", FILTER_SANITIZE_NUMBER_INT);

// Common include file.
include "illegalsitecheck_common_inc.php";
include "mysqlclass.php";


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

// Return Search Results as a Array.
$aRes = array('prog' => 0, 'reg' => 0, 'new'=>0, 'ngu'=>0, 'err'=>0, 'nreg'=>0, 'all'=>0 );

$urlCount = count($aUrlList);
$iProgress = 0;      // Percentage value of Progress.
$prev_progress = 0;  // Previous value If value changed.
foreach( $aUrlList as $sUrl){
    $fNoRegister = FALSE;
    $fExist = FALSE;
    $sUrl = trim( $sUrl );
    if ( $sUrl == "" ) {
        continue;
    }
    // URL末尾の/を削除
    if( substr( $sUrl, -1) == "/" ){
        $sUrl = substr( $sUrl, 0, (strlen($sUrl) - 1) ); 
    }
    
    
    // FC2アダルトのURLだった場合の処理
    //　ドメインがhttp://video.fc2.com/　で始まる場合、続く言語パラメータを無視し
    //　すべて同じURLとみなして処理する
    //　例)　http://video.fc2.com/en/a/?contents　->　http://video.fc2.com/a/?contents
    //　　　http://chenghuavideo.asia/a/?contents　->　http://video.fc2.com/a/?contents
    $iPos = strripos($sUrl, "http://chenghuavideo.asia");
    if ( $iPos !== false ) {
        $sUrl = preg_replace('/http:\/\/chenghuavideo.asia/', 'http://video.fc2.com', $sUrl, 1);
    }
    $iPos = strripos($sUrl, "video.fc2.com");
    if ( $iPos !== false ) {
        $sUrl = preg_replace('/\/ja\/|\/en\/|\/tw\/|\/ko\/|\/de\/|\/es\/|\/fr\/|\/ru\/|\/id\/|\/pt\/|\/vi\//', '/', $sUrl, 1);
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

    if ( $fExist ){                                  // すでに登録されていたら場合
        array_push($aResistered, $sEscapedUrl); // 登録済みバッファに入れて次のURLへ。
        $fNoRegister = TRUE;
        continue;
    } else {
        if ( $noreg == 1 ){                             // 
            array_push($aNoRegUrl, $sEscapedUrl); // 登録済みバッファに入れて次のURLへ。
            $fNoRegister = TRUE;
            continue;
        } else {
            // INSERT Query
            $que = makeInsertSQL( urlencode($sEscapedUrl), $purpose, $historyId );
        }
    }

    if ( !$fNoRegister ){    // URL登録する場合.
        
        // INSERT Record.
        $db->query($que);
        if ( $db->errno != 0 ){
            echo "ERROR: ".$db->error."<br />\n";
        } else {
            array_push($aNewurl, $sEscapedUrl );
            //echo "URL $mysqlescapedstr を登録しました.<br />\n";
        }
    }
}

if ( $noreg == 1 ){
    addRecordIntoDB($aNoRegUrl, $historyId, 'newregister');
} else {
    addRecordIntoDB($aNewurl, $historyId, 'newregister');
}

$aRes['all'] = $regcnt;
$aRes['ngu'] = count($aNGUrl);
$aRes['new'] = count($aNewurl);
$aRes['reg'] = count($aResistered);
$aRes['nreg'] = count($aNoRegUrl);

echo json_encode($aRes);

$db->close();

$duration -= microtime( TRUE );



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

function addRecordIntoDB( $aData, $regid, $tblname )
{
    resetTable( $tblname );
    if ( $db = dbconnect()) {
        foreach( $aData as $url ){
            $query = "INSERT $tblname (id, register_id, url) VALUES ( NULL, ".$regid.", '".urlencode($url)."') ";
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
