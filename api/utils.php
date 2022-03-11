<?php
  require_once('conn.php');
  
  // 輸入使用者名稱得到有相關資料的陣列
  function getdata_from_username($username) {
    global $conn;
    $sql = 'SELECT * FROM boching_board_comment_users WHERE username=?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row;
  }

  //  逃脫字元 （防止 XSS)
  function escape($str) {
    return htmlspecialchars($str , ENT_QUOTES);
  }

  // 檢查各類權限 
  function has_permission($username, $action) {
    if (getdata_from_username($username)['identity'] === 1) {
      return True;
    } else {
      return getdata_from_username($username)[$action] === 1;
    }
  }

  // 檢查發布者是否與登入者相同
  function is_selfcomment($id) {
    global $conn;
    $sql = 'SELECT * FROM boching_board_comments WHERE id=? AND is_deleted IS NULL';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {
      return False;
    }
    $row = $result->fetch_assoc();
    return $_SESSION['username'] === $row['username'];
  }

  // 檢查輸入是否被設置且不為空
  function is_set($input) {
    return (isset($input) && (strlen($input) !== 0));
  }
?>