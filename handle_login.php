<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 沒有輸入帳號或密碼（type 1 error）
  if (!is_set('username','post') || !is_set('password','post')) {
    header('Location:index.php?loginErrCode=1');
    die('資料不齊全');
  }

  // 搜尋存放已註冊帳密的資料庫
  $username = $_POST['username'];
  $password = $_POST['password'];
  $sql = 'SELECT password FROM boching_board_comment_users WHERE username=?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s',$username);
  $result = $stmt->execute();

  if (!$result) {
    die('Error. '.$conn->error);
  }

  $result = $stmt->get_result();
  // 找到資料庫的帳號是正確的
  if ($result->num_rows === 1) {
    if (password_verify($password,$result->fetch_assoc()['password'])) {
      // 給一組新的通行證並存入 username 變數
      session_regenerate_id();
      $_SESSION['username'] = $username;
      header('Location:index.php');
    } else {
      // 沒有在資料庫撈到密碼->密碼錯誤（type 3 error）
      header('Location:index.php?loginErrCode=3');
    }
  } else {
      // 沒有在資料庫撈到帳號->帳號錯誤（type 2 error）
      header('Location:./index.php?loginErrCode=2');    
  }
?>