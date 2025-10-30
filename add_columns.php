<?php
require_once 'config.php';

$sql = "ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0, ADD COLUMN last_attempt DATETIME NULL;";
if ($conn->query($sql)) {
    echo 'Database updated successfully';
} else {
    echo 'Error: ' . $conn->error;
}
?>
