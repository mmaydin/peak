<?php
$host = '127.0.0.1';
$dbname = 'peak';
$user = 'root';
$pass = '123';

session_start();

include 'classes/connection.php';

Connection::create($host, $dbname, $user, $pass);
include 'classes/user.php';
include 'classes/user_wallet.php';
include 'classes/statistics.php';
include 'functions.php';
