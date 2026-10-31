<?php
require_once __DIR__ . '/../../app/helpers.php';
$_SESSION = [];
session_destroy();
redirect('admin/login.php');
