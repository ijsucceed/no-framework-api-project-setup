<?php
/**
 * Load the app settings
 */

declare( strict_types = 1 );

require __DIR__ . '/../vendor/autoload.php';

use \Curl\Curl;

error_reporting(E_ALL);

// Load enviroment variables
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

require 'functions.php';

app_allow_cors();

/**
 * Display beautiful errors on a test server.
 * 
 * And prevent errors from showing when live.
 */
$whoops = new \Whoops\Run;
if (getenv('ENV') !== 'live') {
    $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
    $whoops->prependHandler(function($e) {
        app_false_response( 'server error', 500);
    });
}
$whoops->register();

require 'controllers/Request.php';
require 'controllers/AuthRequest.php';
require 'controllers/UserRequest.php';
require 'controllers/ServiceRequest.php';
require 'models/User.php';
require 'models/Flutterwave.php';
require 'models/Lists.php';
require 'models/Mail.php';
require 'routes.php';

			