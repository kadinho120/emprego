<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Auth;
Auth::requireLogin();

// index.php is now just a controller that loads the responsive view
include __DIR__ . '/views/index.view.php';