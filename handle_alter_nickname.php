<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 沒有輸入帳號或密碼或新的暱稱（type 1 error)
  if (!is_set('changed_nickname','post') || !is_set('username','post') || !is_set('password','post')) {
    header('Location:alter_nickname.php?nicknameErrCode=1');
    die('資料不齊全');
  }

  // 檢查登入狀態
  check_login();
  $username = $_SESSION['username'];

  // 比對帳號是否和登入者相同
  if ($username !== $_POST['username']) {
    // 帳號錯誤（type 2 error）
    header('Location:index.php?nicknameErrCode=2');
    die('帳號錯誤');
  }

  $password = $_POST['password'];
  $changed_nickname = $_POST['changed_nickname'];

  if (password_verify($password,  getdata_from_username($username)['password'])) {
    // 更新暱稱
    $stmt = $conn->prepare('UPDATE boching_board_comment_users SET nickname=? WHERE username=?');
    $stmt->bind_param('ss', $changed_nickname, $username);
    $result = $stmt->execute();
    header('Location:index.php');
  } else {
    // 沒有在資料庫撈到密碼->密碼錯誤（type 3 error）
    header('Location:index.php?nicknameErrCode=3');
    die('密碼錯誤');    
  }
?>