<?php
require_once 'config.php';

$result = $conn->query('SELECT username FROM users');
echo "Users in database:\n";
while($row = $result->fetch_assoc()) {
    echo $row['username'] . PHP_EOL;
}
?>
