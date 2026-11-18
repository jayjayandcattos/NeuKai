<?php
/**
 * NeuKai bootstrap.
 *
 * Every web entry point (and every file that can be requested directly)
 * requires this file first. It loads environment variables, starts a hardened
 * session, emits security headers and opens the database connection.
 */

if (defined('NEUKAI_BOOTSTRAPPED')) {
    return;
}
define('NEUKAI_BOOTSTRAPPED', true);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

require_once APP_PATH . '/config/env.php';
load_env_file(ROOT_PATH . '/.env');

require_once APP_PATH . '/config/helpers.php';

error_reporting(E_ALL);
ini_set('display_errors', env('APP_ENV', 'local') === 'local' ? '1' : '0');
ini_set('log_errors', '1');

date_default_timezone_set('Asia/Manila');

secure_session_start();
security_headers();

require_once APP_PATH . '/config/db.php';
