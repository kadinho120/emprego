<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;

Auth::init();

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Controller logic for login.php
// login.php is now a thin controller that serves the view. 
// Most logic is handled by AJAX via login-process.php

include __DIR__ . '/views/login.view.php';