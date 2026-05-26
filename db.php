<?php
session_start();

$db_host = 'sql2.7m.pl';
$db_user = 'itzfrenz648_itzfrenz';
$db_pass = 'ofcThisIsNotTheRealPass';
$db_name = 'itzfrenz648_itzfrenz';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS voters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        voter_id VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS candidates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        position VARCHAR(50) DEFAULT 'N/A',
        party VARCHAR(50) DEFAULT 'N/A'
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        voter_id VARCHAR(50) NOT NULL,
        candidate_id INT NOT NULL,
        FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
        FOREIGN KEY (voter_id) REFERENCES voters(voter_id) ON DELETE CASCADE
    )");

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function setFlash($msg, $type = 'success') {
    $_SESSION['msg'] = $msg;
    $_SESSION['msg_type'] = $type;
}

function getFlash() {
    if (isset($_SESSION['msg'])) {
        $msg = ['text' => $_SESSION['msg'], 'type' => $_SESSION['msg_type']];
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        return $msg;
    }
    return null;
}
?>