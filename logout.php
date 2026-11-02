<?php
require 'config.php';
log_aktivitas('Logout dari sistem');
$_SESSION = [];
session_destroy();
redirect('login.php');
