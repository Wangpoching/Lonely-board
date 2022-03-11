<?php
    session_start();
    // 把 ssid 對應的 variables 都清空
    session_destroy();
    header('Location:index.php');
?>