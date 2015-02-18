<?php

// Common include file.
include "illegalsitecheck_common_inc.php";
include "mysqlclass.php";

$command = filter_input( INPUT_POST, "command", FILTER_SANITIZE_STRING );

// Command Dispatch.イベントハンドラ
// 新規コマンドを追加したらここに呼び出し先関数を設定。
if ( isset($command) ){
    switch($command){
        case 'getSiteCount' :
            echo getSiteCount();
            break;

        case 'getSiteList' :
            $user = filter_input( INPUT_POST, 'user', FILTER_SANITIZE_STRING );
            getSiteList( $user );
            break;
            
        case 'getOtherList' :
            $tablename = filter_input( INPUT_POST, 'tablename', FILTER_SANITIZE_STRING );
            getOtherList( $tablename );
            break;

        case 'logout' :
            logout();
            break;

        case 'getDomainList' :
            getNGDomainList();
            break;

        case 'registerNGdomain' :
            registerNGdomain();
            break;

        case 'getUserList' :
            getUserList();
            break;

        case 'registerNewUser' :
            registerNewUser();
            break;

        case 'getLatestRegister' :
            $userid = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_NUMBER_INT );
            echo getLatestRegister($userid);
            break;

        case 'getHistoryList' :
            $userid = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_NUMBER_INT );
            echo getHistoryList($userid);
            break;

        case 'getHistory_Detail' :
            $historyId = filter_input( INPUT_POST, 'hid', FILTER_SANITIZE_NUMBER_INT );
            getHistory_Detail($historyId);
            break;

        case 'delDomain' :
            $domainid = filter_input( INPUT_POST, 'domainid', FILTER_SANITIZE_STRING );
            delitem($domainid, 'ngdomainlist');
            break;

        case 'delUser' :
            $user = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_STRING );
            updateUser($user, 'register_history');
            delitem($user, 'user_master');
            break;

        case 'delURL' :
            $siteid = filter_input( INPUT_POST, 'domainid', FILTER_SANITIZE_STRING );
            delitem($siteid, 'urls');
            break;

        case 'resetpw' :
            $user = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_STRING );
            $pw = filter_input( INPUT_POST, 'passwd', FILTER_SANITIZE_STRING );
            resetPassword($user, $pw, 'user_master');
            break;

        case 'getURLList' :
            getURLList("");
            break;

        case 'deleteHistoryRecord' :
            $historyId = filter_input( INPUT_POST, 'hid', FILTER_SANITIZE_NUMBER_INT );
            deleteHistoryIdAndUrls( $historyId );
            break;

        case 'getListByUser' :
            getListByUser( );
            break;

        case 'getHistoryList' :
            $user = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_STRING );
            getHistoryList( $user );
            break;

        case 'deleteForCancelled' :
            deleteForCancelled();
            break;

        case 'getNewHistoryId' :
            $userId = filter_input( INPUT_POST, 'userid', FILTER_SANITIZE_NUMBER_INT );
            $purpose = filter_input( INPUT_POST, 'purpose', FILTER_SANITIZE_NUMBER_INT );
            getNewHistoryId($userId, $purpose);
            break;

        case 'clearScumDatas' :
            clearScumDatas();

        default:
            break;
    }
}

//***************************************************************
//****   Functions for Command ( Event Handler )             ****
//***************************************************************
function logout()
{
    session_start();
    unset($_SESSION['user']);
    unset($_SESSION['jsResult']);
}

// 現在登録されている違法サイト数を返す。
function getSiteCount()
{
    if ( $db = dbconnect()) {
        $query = "SELECT count(id) as cnt FROM urls; ";
        $result = $db->query($query);
        if ($result){
            $ret = $db->fetch_array($result);
            $sitecnt = $ret['cnt'];
        }
    }
   
    return "(登録数".$sitecnt."件)";
}

// 該当するサイトの一覧をテーブルで出力
function getSiteList( $user )
{ 
    if ( $db = dbconnect()) {
        $query = "SELECT 
                    id,
                    url
                 FROM newregister ";
        $result = $db->query($query);
        if ($result){
            echo "<table id='resulttable'>\n";

            while( $ret = $db->fetch_array($result) ){
                echo "<tr><td class='td1'>".urldecode($ret['url'])."</td></tr>\n";
            }
            echo "</table>\n";
        }
    }
    return;
}

// 該当するサイトの一覧をテーブルで出力
function getOtherList( $tablename )
{ 
    if ( $db = dbconnect()) {
        $query = "SELECT 
                    id,
                    url
                 FROM $tablename ";
        $result = $db->query($query);
        if ($result){
            echo "<table id='resulttable'>\n";

            while( $ret = $db->fetch_array($result) ){
                echo "<tr><td class='td1'>".urldecode($ret['url'])."</td></tr>\n";
            }
            echo "</table>\n";
        }
    }
    return;
}


function getNGDomainList()
{
    if ( $db = dbconnect()) {
        $query = "SELECT id, domainname, remark FROM ngdomainlist ORDER BY id desc; ";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr class='tr1'>\n";
                    echo "<td class='td1'>".urldecode($ret['domainname'])."</td>\n";
                    echo "<td class='td3'>".urldecode($ret['remark'])."</td>\n";
                    echo "<td class='td2'><div class='btn'><a href='' class='dellink' onclick='deldomain(".$ret['id'].")'>Del.</a></div></td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "※　登録ドメインはありません。";
            }
        }
    }
    $db->close();
    return;
}

function getUserList()
{
    $tblname = " user_master";
    if ( $db = dbconnect()) {
        $query = "SELECT id, name ,password FROM $tblname WHERE id <> 1; ";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr class='tr1'>\n";
                    echo "<td class='td3'>".$ret['name']."</td>\n";
	                echo "<td class='td3'>".$ret['password']."</td>\n";
                    echo "<td  class='td2'><div class='btn'><a href='' class='pwlink' onclick='resetpw(\"".$ret['id']."\")'>PW.</a></div></td>\n";
                    echo "<td  class='td2'><div class='btn'><a href='' class='dellink' onclick='deluser(\"".$ret['id']."\")'>Del.</a></div></td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "※　登録ユーザはありません。";
            }
        }
    }
    $db->close();
    return;
}

function getURLList( $sFilter )
{
    $tblname = " urls";
    if ( $db = dbconnect()) {
        if ( $sFilter != "" ){
            
        }
        
        $query = "SELECT id, s_url, s_user, DATE(d_checkeddate) as d_date FROM $tblname WHERE b_reported = FALSE ";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr>\n";
                    echo "<td>".urldecode($ret['s_url'])."</td>\n";
                    //echo "<td>".$ret['s_user']."</td>\n";
                    //echo "<td>".$ret['d_date']."</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "※　登録URLはありません。";
            }
        }
    }
    $db->close();
    return;
}

// NG Domain registration
function registerNGdomain()
{
    $domain = filter_input( INPUT_POST, "domain", FILTER_SANITIZE_STRING );
    $remark = filter_input( INPUT_POST, "remark", FILTER_SANITIZE_STRING );
    //$remark = mysql_real_escape_string($remark);
    // URL末尾の/を削除
    if( substr( $domain, -1) == "/" ){
        $domain = substr( $domain, 0, (strlen($domain) - 1) ); 
    }
    
    $tblname = " ngdomainlist";
    if ( $db = dbconnect()) {
        $query = "SELECT domainname FROM $tblname WHERE domainname='".urlencode($domain)."'";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "Already resistered.\n";
            } else {
                $query = "INSERT $tblname (
                        id, domainname, enabled, remark ) VALUES (
                        NULL, '".urlencode($domain)."', 1, '".$remark."' )";
                //echo "INSERT QUERY: $query <br />\n";
                $ins = $db->query($query);
                if(!$ins) {
                    echo "INSERT $tblname FAILED<br />\n";
                }
            }
        }
    }
    return;
}


// User Registration
function registerNewUser()
{
    $userid = filter_input( INPUT_POST, "userid", FILTER_SANITIZE_STRING );
    $passwd = filter_input( INPUT_POST, "passwd", FILTER_SANITIZE_STRING );
    
    $tblname = " user_master";
    if ( $db = dbconnect()) {
        $query = "SELECT name FROM $tblname WHERE name='".$userid."'";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo $userid." はすでに登録されています。\n";
            } else {
                $query = "INSERT $tblname (
                        id, name, password, valid ) VALUES (
                        NULL, '".$userid."','".$passwd."', 1 )";
                echo "INSERT QUERY: $query <br />\n";
                $ins = $db->query($query);
                if($ins) {
                    echo "INSERT $tblname OK<br />\n";
                }else {
                    echo "INSERT $tblname FAILED<br />\n";
                }
            }
        }
    }
    return;
}

function updateUser( $id, $table )
{
    if ( $db = dbconnect()) {
        $que = "UPDATE $table SET user_id= 1 WHERE user_id=".$id;
        $result = $db->query( $que );
    }
    $db->close();
    return;
}

function delitem( $id, $table )
{
    if ( $db = dbconnect()) {
        if ( !$result = $db->deleterec($table, $id) ){
            echo "Delete failed.<br />\n";
        }
    }
    $db->close();
    return;       
}

function resetPassword( $userid, $passwd, $table )
{
    if ( $db = dbconnect()) {
        $que = "UPDATE $table SET password='".$passwd."' WHERE id=".$userid;
        if ( !$result = $db->query( $que ) ){
            echo "Reset password failed.<br />\n";
        }
    }
    $db->close();
    return;
}

function getLatestRegister( $user )
{
    $retstr = "";
    if ( $db = dbconnect()) {
        $querymain = "SELECT t1.id, t1.register_date, t1.purpose_id
            FROM register_history t1
            WHERE t1.user_id = $user
            ORDER BY t1.register_date DESC ";
//echo "QUE:".$query."<br />\n";
        $result = $db->query($querymain);
        if ($result){
            if ($db->num_rows($result) > 0) {
                $retmain = $db->fetch_array($result);
                $queryurlcnt = "SELECT COUNT(id) as cnt FROM urls t3 WHERE t3.register_history_id = ".$retmain['id'];
                $resulturlcnt = $db->query($queryurlcnt);
                $returlcnt = $db->fetch_array($resulturlcnt);
                $querypurpose = "SELECT t2.purpose_name FROM purpose_master t2 WHERE t2.id = ".$retmain['purpose_id'];
                $resultpurpose = $db->query($querypurpose);
                $retpurpose = $db->fetch_array($resultpurpose);
                $retstr = $returlcnt['cnt']."件 on ".$retmain['register_date']." : for ".$retpurpose['purpose_name'];
            }
        }
    }
    $db->close();
    return $retstr;
}

function getHistory_Detail( $hid )
{
    if ( $db = dbconnect()) {
//        if ($hid != 0 ){
//            $sWhere = " WHERE t1.register_history_id = ".$hid;
//        }else{
//            $sWhere = "";
//        }
        $sWhere = " WHERE t1.register_history_id = ".$hid;
        $query = "SELECT t1.id, t1.s_url
            FROM urls t1 ".$sWhere." ORDER BY t1.id ";
//echo "QUE:".$query."<br />\n";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr>\n";
                    //echo "<td>".$ret['s_user']."</td>\n";
                    echo "<td width='890px'>".urldecode($ret['s_url'])."</td>\n";
                    //echo "<td>".mb_convert_encoding($ret['s_url'], 'utf-8', 'utf-8' )."</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "※　登録URLはありません。";
            }
        }
    }
    $db->close();
    return;    
}

function deleteHistoryIdAndUrls( $hid )
{
    delitem( $hid, 'register_history');
    
    if ( $db = dbconnect()) {
        $que = "DELETE FROM urls WHERE register_history_id=".$hid;
        if ( !$db->query($que) ){
            return FALSE;
        }
    }
    $db->close();
    return TRUE;
}

function getListByUser()
{
    if ( $db = dbconnect()) {
        $query = "
        SELECT
            'administrator' u_name,
            0 u_id,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id=0 AND t2.purpose_id=1 ) gh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id=0 AND t2.purpose_id=2 ) uh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id=0 AND t2.purpose_id=3 ) th_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id=0 AND t2.purpose_id=4 ) fh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id=0 AND t2.purpose_id=5 ) ph_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = 0 AND t2.purpose_id=1 ) gu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = 0 AND t2.purpose_id=2 ) uu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = 0 AND t2.purpose_id=3 ) tu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = 0 AND t2.purpose_id=4 ) fu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = 0 AND t2.purpose_id=5 ) pu_cnt
        UNION
        SELECT 
            t1.name	u_name,
            t1.id	u_id,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id = t1.id AND t2.purpose_id=1 ) gh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id = t1.id AND t2.purpose_id=2 ) uh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id = t1.id AND t2.purpose_id=3 ) th_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id = t1.id AND t2.purpose_id=4 ) fh_cnt,
            (SELECT COUNT(t2.id) FROM register_history t2 WHERE t2.user_id = t1.id AND t2.purpose_id=5 ) ph_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = t1.id AND t2.purpose_id=1 ) gu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = t1.id AND t2.purpose_id=2 ) uu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = t1.id AND t2.purpose_id=3 ) tu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = t1.id AND t2.purpose_id=4 ) fu_cnt,
            (SELECT COUNT(t3.id) FROM urls t3 INNER JOIN register_history t2 ON t2.id = t3.register_history_id  WHERE t2.user_id = t1.id AND t2.purpose_id=5 ) pu_cnt
        FROM
            user_master t1
        ORDER by gh_cnt DESC, uh_cnt DESC, gu_cnt DESC, uu_cnt DESC
        ";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list_m'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr>\n";
                    echo "<td class='c1'><a onclick='getUserRegisterDetail(".$ret['u_id'].")'> ".$ret['u_name']."</a></td>\n";
                    echo "<td class='c2'>".$ret['gh_cnt']."</td>\n";
                    echo "<td class='c3'>".$ret['gu_cnt']."</td>\n";
                    echo "<td class='c2'>".$ret['uh_cnt']."</td>\n";
                    echo "<td class='c3'>".$ret['uu_cnt']."</td>\n";
                    echo "<td class='c2'>".$ret['th_cnt']."</td>\n";
                    echo "<td class='c3'>".$ret['tu_cnt']."</td>\n";
                    echo "<td class='c2'>".$ret['fh_cnt']."</td>\n";
                    echo "<td class='c3'>".$ret['fu_cnt']."</td>\n";
                    echo "<td class='c2'>".$ret['ph_cnt']."</td>\n";
                    echo "<td class='c3'>".$ret['pu_cnt']."</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "※　ユーザが登録されていません。";
            }
        }
    }
    $db->close();
    return;
}

function getHistoryList( $userId )
{

    if ( $db = dbconnect()) {
        $query = "SELECT t1.id, t1.register_date, t1.purpose_id 
            FROM register_history t1
            WHERE t1.user_id = $userId
            ORDER BY t1.register_date DESC
        ";
//echo "QUE:".$query."<br />\n";
        $result = $db->query($query);
        if ($result){
            if ($db->num_rows($result) > 0) {
                echo "<table class='list_l'>\n";
                while( $ret = $db->fetch_array($result) ){
                    echo "<tr>\n";
                    //echo "<td>".$ret['s_user']."</td>\n";
                    echo "<td class='c1'><a onclick='dispDetail(".$ret["id"].")'>".$ret['register_date']."</a></td>\n";
                    if ( $dbroop = dbconnect()) {
				        $queryurlcount = "SELECT COUNT(s_url) as urlcnt FROM urls WHERE register_history_id = ".$ret['id'];
				        $queryurl = "SELECT s_url as surl FROM urls WHERE register_history_id = ".$ret['id']." LIMIT 1 ";
				        $querypurpose = "SELECT t2.purpose_name FROM purpose_master t2 WHERE t2.id = ".$ret['purpose_id'];
	 			    }
	 			    
	 			    $resultpurpose = $dbroop->query($querypurpose);
 			        $returlpur = $dbroop->fetch_array($resultpurpose);
                    echo "<td class='c2'>".$returlpur['purpose_name']."</td>\n";

	 			    $resulturlcount = $dbroop->query($queryurlcount);
 			        $returlcount = $dbroop->fetch_array($resulturlcount);
                    echo "<td class='c3'>".$returlcount["urlcnt"]."</td>\n";

 	 			    $resulturl = $dbroop->query($queryurl);
 			        $returl = $dbroop->fetch_array($resulturl);
                    echo "<td class='c4'>".substr(urldecode($returl['surl']), 0, 70)."</td>\n";
                    echo "<td class='c5'><div class='btn'><a class='dellink' onclick='deleterecord(".$ret["id"].")'>削除</a></div></td>\n";
                    echo "</tr>\n";
	 			    $dbroop->close();
                }
                echo "</table>\n";
            } else {
                echo "※　登録実績はありません。";
            }
        }
    }
    $db->close();
    return;    
}

function deleteForCancelled()
{
    if ( $db = dbconnect()) {
        // get Latest history ID
        $query = "SELECT MAX(id) as hid FROM register_history ";
        $result = $db->query( $query );
        if ( $result ){
            $row = $db->fetch_array($result);
            $history_id = $row['hid'] + 1;
        } else {
            return false;
        }
        
        // delete urls from record
        delitem( $history_id, 'urls');
    }
}

function getNewHistoryId( $user_id, $purpose )
{
    if ( $db = dbconnect()) {
        // get Latest history ID
        $query = "SELECT MAX(id) as hid FROM register_history ";
        $result = $db->query( $query );
        if ( $result ){
            $row = $db->fetch_array($result);
            $history_id = $row['hid'] + 1;
        } else {
            return false;
        }

        date_default_timezone_set('Canada/Pacific');
        $tm = localtime( time(), true);
        $timestr = sprintf("%d-%02d-%02d %02d:%02d:%02d",(1900+$tm['tm_year']), (1+$tm['tm_mon']), $tm['tm_mday'], $tm['tm_hour'], $tm['tm_min'], $tm['tm_sec'] );
        $query = "
                INSERT register_history (
                id,
                user_id,
                purpose_id,
                register_date
                ) VALUES (
                $history_id,
                $user_id ,
                $purpose ,
                '$timestr'
                )";

        if (!$result = $db->query( $query )){
            $history_id = 0;
        }
        
    }
    echo $history_id;
    flush();
    return;
}

