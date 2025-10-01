<?php
require_once '../classes/Database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();

$certificate_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$certificate_id) {
    header('Location: graduates.php');
    exit();
}

$db->query('UPDATE certificates SET is_released = 1, released_at = NOW() WHERE id = :id');
$db->bind(':id', $certificate_id);
$db->execute();

header('Location: graduates.php?msg=certificate_released');
exit();
?>
