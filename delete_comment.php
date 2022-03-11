<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  if (!is_set('id','post') || !is_set('csrf-token','post')) {
    header('Location:index.php');
    die('failed');
  } else {
    $id = intval($_POST['id']);
  }

  // 檢查是不是有登錄過了
  check_login();
  $username = $_SESSION['username'];

  // 檢查是不是管理員或者留言者本人
  if (!(is_selfcomment($id) || getdata_from_username($username)['identity'] === 1)) {
    header('Location:index.php');
    die('權限不足');
  }

  // 檢查是不是同站請求
  if ($_POST['csrf-token'] !== $_SESSION['csrf-token']) {
    header('Location:index.php');
    die('不是同站請求');
  }

  // 檢查有沒有編輯留言的權限
  if (!has_permission($username, 'deletable')) {
    header('Location:index.php');
    die('您已被水桶，請洽管理員');
  }

  // 刪除留言
  $sql = 'UPDATE boching_board_comments SET is_deleted=1 WHERE id=?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $id);
  $result = $stmt->execute();
  if (!$result) {
    die('Error.' . $conn->error);
  }

  header('Location:index.php')
?>