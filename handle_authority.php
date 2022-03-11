<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 沒有輸入值（type 1 error)
  if ( !(is_set('id','post') && is_set('editable','post') && is_set('deletable','post') && is_set('addable','post') && is_set('identity','post')) ) {
    die('failed');
  }

  // 檢查是不是有登錄過了
  check_login();
  $username = $_SESSION['username'];
  
  // 檢查是不是管理員
  if (getdata_from_username($username)['identity'] !== 1) {
    header('Location:index.php');
    die('權限不足');
  }

  // 更新權限
  $id = intval($_POST['id']);
  $editable = intval($_POST['editable']);
  $deletable = intval($_POST['deletable']);
  $addable = intval($_POST['addable']);
  $identity = intval($_POST['identity']);  
  $sql = 'UPDATE boching_board_comment_users SET editable=?, deletable=?, addable=?, identity=? WHERE id=?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('iiiii', $editable, $deletable, $addable, $identity, $id);
  $result = $stmt->execute();
  if (!$result) {
    die('Error.' . $conn->error);
  }

  header('Location:admin.php')
?>