<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id=$id");
}
echo 'ok';
?>