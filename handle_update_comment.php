<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');
  
  // 沒有輸入值（type 1 error)
  if (!is_set('content','post')) {
    if (!is_set('id','post')) {
      die('failed');
    } else {
      header('Location:update_comment.php?errCode=1&id='.$_POST['id']);
      die('資料不齊全');
    }
  }

  // 檢查是不是有登錄過了
  check_login();
  $username = $_SESSION['username'];
  $id = intval($_POST['id']);

  // 檢查是不是管理員或者留言者本人
  if (!(is_selfcomment($id) || getdata_from_username($username)['identity'] === 1)) {
    header('Location:index.php');
    die('權限不足');
  }

  // 再檢查有沒有被水桶
  if (!has_permission($username, 'editable')) {
    header('Location:index.php');
    die('您已被水桶，請洽管理員');
  }
  
  // 更新留言
  $content = $_POST['content'];
  $sql = 'UPDATE boching_board_comments SET content=? WHERE id=? AND is_deleted IS NULL';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $content, $id);
  $result = $stmt->execute();
  if (!$result) {
    die('Error.' . $conn->error);
  }

  header('Location:index.php');
?>