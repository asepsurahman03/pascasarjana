<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
if(isLoggedIn()){logActivity('Logout','auth','');session_destroy();}
header('Location:'. BASE_URL . '/login');exit;
