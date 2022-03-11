<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 沒有帶 id
  if (!is_set('id','get')) {
    header('Location:index.php');
    die('failed');
  } else {
    $id = intval($_GET['id']);
  }

  // 檢查是不是有登錄過了
  check_login();
  $username = $_SESSION['username'];

  // 檢查是不是管理員或是否是自己的留言
  if (!(is_selfcomment($id) || getdata_from_username($username)['identity'] === 1)) {
    header('Location:index.php');
    die('權限不足');
  }
  
  // 檢查有沒有被水桶
  if (!has_permission($username, 'editable')) {
    header('Location:index.php');
    die('您已被水桶，請洽管理員');
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>留言板</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./normalize.css">
  <link rel="stylesheet" href="./modal.css">
</head>

<body>
  <header class="warning"><strong>注意！本站為練習用網站，因教學用途刻意忽略資安的實作，註冊時請勿使用任何真實的帳號或密碼。</strong></header>
  <main class="board">
    <a class="form__button" href="./index.php">回留言板</a>
    <h1 class="board__title">編輯留言</h1>
    <?php 
      if (is_set('errCode','get')) {
        $code = $_GET['errCode'];
        $msg = 'Error';
        if ($code === '1') {
          $msg = '請勿輸入空值';
        }
        echo '<div class="errmsg">' . $msg . '</div>';
      }
    ?>
    <form class="form" method="POST" action="handle_update_comment.php">
    <?php
      $sql = 'SELECT * FROM boching_board_comments WHERE id=? AND is_deleted IS NULL';
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
      } else {
        header('Location:index.php');
        die('查無留言');
      }
    ?>
      <div class="comment__container">
        <textarea class="comment__area" rows=5 name="content"><?php echo escape($row['content']); ?></textarea>
        <input type= "hidden" type="text" name="id" value="<?php echo escape($row['id']); ?>" />
        <button class="form__button submit">送出</button>
      </div>
    </form>
  </main>
</body>
<script src="./index.js"></script>
<script>
  const commentArea = document.querySelector('.comment__area')
  if (document.querySelector('.errmsg')) {
    const errmsg = document.querySelector('.errmsg')
    focusinRemoveErrmsg(commentArea, errmsg);
  }
</script>
</html>

