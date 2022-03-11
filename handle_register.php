<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 沒輸入帳密或暱稱（type 1 error）
  if (!is_set('nickname','post') || !is_set('username','post') || !is_set('password','post')) {
    header('Location:index.php?registerErrCode=1');
    die('資料不齊全');
  }
  
  // 在 user 資料庫新增帳密資訊
  $nickname = $_POST['nickname'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
  $sql = 'INSERT INTO boching_board_comment_users (nickname, username,password) VALUES (?, ?, ?)';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss',$nickname, $username,$password);
  $result = $stmt->execute();
  
  // 帳號重複（type 2 error）
  if ($result) {
    // 協助登入
    // 給一組新的通行證並存入 username 變數
    session_regenerate_id();
    $_SESSION['username'] = $username;
    header("Location:index.php"); 
  } else if ($conn->errno === 1062) {
    header('Location:index.php?registerErrCode=2');
    die('帳號重複');
  } else {
    die('Error. '. $conn->error);
  }
?>