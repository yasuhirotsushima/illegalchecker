<?php 

$page_id = filter_input(INPUT_POST, 'page_id', FILTER_SANITIZE_NUMBER_INT);
$user_id = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);

echo '<div class="menuitems" id="loggedin">ようこそ、'.$user_id."</div>\n";
    
switch($page_id) {
    case 1 :        // Admin Page
        if ($user_id == 'administrator' ){
            echo '<div class="menuitems"><a href="index.php">HOME</a></div>';
            echo '<div class="menuitems"><a href="history.php">登録履歴</a></div>';
        }
        break;

    case 2 :        // Index page.
        if ($user_id == 'administrator' ){
            echo '<div class="menuitems"><a href="history.php">登録履歴</a></div>';
            echo '<div class="menuitems"><a href="adminpage.php">Adminページ</a></div>';
        } else {
            echo '<div class="menuitems"><a href="history.php">登録履歴</a></div>';            
        }
        break;

    case 3 :        // History page.
        if ($user_id == 'administrator' ){
            echo '<div class="menuitems"><a href="index.php">HOME</a></div>';
            echo '<div class="menuitems"><a href="adminpage.php">Adminページ</a></div>';
        } else {
            echo '<div class="menuitems"><a href="index.php">HOME</a></div>';
        }
        break;
}

echo '<div class="menuitems" id="logout"><a href="#" onclick="logout()">ログアウト</a></div>'."\n";

?>
