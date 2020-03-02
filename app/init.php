<?php

// Composer autoloader
require_once '../vendor/autoload.php';

session_start();
// config file
require_once 'core/Config.php';

// db file
require_once 'database.php';

// third-party files
//require_once 'third-party/ImageResize.php';  use \Gumlet\ImageResize;


// core files
require_once 'core/App.php';
require_once 'core/Helpers.php';
require_once 'core/Controller.php';
require_once 'core/Session.php';