<?php
require __DIR__ . '/_bootstrap.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
