<?php
require 'config.php';
if (empty($_SESSION['user'])) redirect('login.php');
redirect(BASE . $_SESSION['user']['role'] . '/dashboard.php');
