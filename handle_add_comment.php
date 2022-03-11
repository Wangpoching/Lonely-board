<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');
  
  // 提交空的留言（type 1 error）
  if (!is_set('content','post')) {
    header('Location:index.php?errCode=1');
    die('資料不齊全');
  }

  // 檢查是不是有登錄過了
  check_login();
  $username = $_SESSION['username'];  

  // 檢查有沒有新增留言的權限
  if (!has_permission($username, 'addable')) {
    header('Location:index.php');     
    die('您已被水桶，請洽管理員');
  }

  // 將暱稱與留言寫入資料庫
  $content = $_POST['content'];
  $sql = 'INSERT INTO boching_board_comments (username, content) VALUES (?, ?)';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ss',$username, $content);
  $result = $stmt->execute();
  if (!$result) {
    die('Error. ' . $conn->error);
  }

  header('Location:index.php');
?>