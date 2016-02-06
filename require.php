<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'peak');
define('DB_USER', 'root');
define('DB_PASS', 123);
define('FB_APP_ID', 36690848899);

session_start();

include 'classes/connection.php';
include 'classes/user.php';
include 'classes/user_wallet.php';
include 'classes/statistics.php';
include 'functions.php';

Connection::create(DB_HOST, DB_NAME, DB_USER, DB_PASS);
