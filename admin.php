<?php
  session_start();
  require_once('conn.php');
  require_once('utils.php');

  // 檢查是否登入
  $username = NULL;
  $nickname = NULL;
  $identity = NULL;
  check_login();
  $username = $_SESSION['username'];
  $nickname = getdata_from_username($username)['nickname'];
  $identity = intval(getdata_from_username($username)['identity']);

  // 檢查是否為管理員
  if ($identity !== 1) {
    header('Location:index.php');    
    die('權限不符');
  }

  // 查詢特定使用者資料或一般顯示
  if (is_set('username','get')) {
    $stmt = $conn->prepare('SELECT * FROM boching_board_comment_users WHERE username=?');
    $stmt->bind_param('s', $_GET['username']);
  } else {
    $page = 1;
    if (is_set('page','get')) {
      $page = intval($_GET['page']);
    }
    $items_per_page = 3;
    $offset = ($page - 1) * $items_per_page;
    $stmt = $conn->prepare('SELECT * FROM boching_board_comment_users ORDER BY id DESC limit ? offset ?');
    $stmt->bind_param('ii', $items_per_page, $offset);
  }
  $result = $stmt->execute();
  if (!$result) {
    die('Error.' . $conn->error);
  }
  $result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>留言板</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="normalize.css">
  <link rel="stylesheet" href="admin.css">
</head>

<body>
  <header class="warning"><strong>注意！本站為練習用網站，因教學用途刻意忽略資安的實作，註冊時請勿使用任何真實的帳號或密碼。</strong></header>
  <main class="board">
    <a class="form__button" href="index.php">回留言板</a>
    <?php if (isset($username)) {
      echo '<a class="form__button" href="admin.php">管理後台</a>';
    } ?>
    <h1 class="board__title">Control Panel</h1>
    <?php if (isset($nickname)) { ?>
      <div class="nickname__container">
        <span><strong><?php echo escape($nickname); ?></strong> 歡迎回來</span>
      </div>
    <?php } ?>
    <form class="search-form" method="GET" action="admin.php">
      搜尋帳號：<input type="text" name="username"/>
      <input type="submit" value="搜尋"/>
    </form>
    <div class="table-container">
      <table>
        <tr>
          <th>id</th>
          <th>帳號</th>
          <th>暱稱</th>
          <th>編輯留言</th>
          <th>刪除留言</th>
          <th>新增留言</th>
          <th>身分</th>
          <th></th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <form method="POST" action="handle_authority.php">
            <input type="hidden" name="id" value="<?php echo escape($row['id']); ?>"></input>
            <tr>
              <td class="id-sample sample"><?php echo escape($row['id']); ?></td>
              <td class="username-sample sample"><?php echo escape($row['username']); ?></td>
              <td class="nickname-sample sample"><?php echo escape($row['nickname']); ?></td>
              <td class="edit-sample sample">
                <select name="editable">
                  <?php 
                    echo $row['editable'] === 1 ? '<option value="1" selected>開啟</option><option value="0">關閉</option>' : '<option value="1">開啟</option><option value="0" selected>關閉</option>';
                  ?>
                </select>
              </td>
              <td class="deletable-sample sample">
                <select name="deletable">
                  <?php 
                    echo $row['deletable'] === 1 ? '<option value="1" selected>開啟</option><option value="0">關閉</option>' : '<option value="1">開啟</option><option value="0" selected>關閉</option>';
                  ?>
                </select>
              </td>
              <td class="addable-sample sample">
                <select  name="addable">
                  <?php 
                    echo $row['addable'] === 1 ? '<option value="1" selected>開啟</option><option value="0">關閉</option>' : '<option value="1">開啟</option><option value="0" selected>關閉</option>';
                  ?>
                </select>
              </td>
              <td class="identity-sample sample">              
                <select  name="identity">
                  <?php 
                    echo $row['identity'] === 1 ? '<option value="1" selected>管理員</option><option value="2">一般</option>' : '<option value="1">管理員</option><option value="2" selected>一般</option>';
                  ?>
                </select>
              </td>
              <td>
                <input type="submit" />
              </td>
            </tr>
          </form>
        <?php } ?>
      </table>
    </div>
    <?php if (!is_set('username','get')) { ?>
      <?php
        $stmt = $conn->prepare('SELECT COUNT(id) FROM boching_board_comment_users');
        $result = $stmt->execute();
        if (!$result) {
          die('Error:' . $conn->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_count = $row['COUNT(id)'];
        $total_page = ceil($total_count/$items_per_page);
      ?>
      <div class="bottom">
        <div class="page_info">
          <span>共有 <?php echo escape($total_count); ?> 筆資料</span>
          <span>第 <?php echo escape($page); ?> 頁</span>
        </div>
        <div class="pagination_container">
          <ul class="pagination">
          <?php 
            $pages_per_pagination = 5;
            $pagination = ceil($page/$pages_per_pagination);
            $pagination_offset = ($pagination - 1) * $pages_per_pagination + 1;
            $total_pagination = ceil($total_page/$pages_per_pagination);
          ?>
          <?php if ($pagination != 1) { ?>
              <li><a href="admin.php?page=<?php echo $pagination_offset - $pages_per_pagination; ?>">«</a><li>
          <?php } ?>
          <?php for ($i = $pagination_offset; $i < ($pagination_offset + $pages_per_pagination); $i++) { ?>
            <?php if ($i > $total_page) {
              break;
            }
            ?>
            <li><a <?php if (intval($i) === $page) {echo 'class="active"';}?> href="<?php echo "admin.php?page=".$i; ?>"><?php echo $i; ?></a></li>
          <?php } ?>
          <?php  if ($pagination < $total_pagination) { ?>
              <li><a href="admin.php?page=<?php echo $pagination_offset + $pages_per_pagination; ?>">»</a><li>
          <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>
  </main>

</body>
</html>