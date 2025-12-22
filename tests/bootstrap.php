<?php
declare(strict_types=1);

$findRoot = function () {
    $root = dirname(__DIR__);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(__DIR__, 2);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }

    $root = dirname(__DIR__, 3);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }
};

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
$pluginRoot = dirname(__DIR__);
define('ROOT', $findRoot());
define('APP_DIR', 'TestApp');
define('WEBROOT_DIR', 'webroot');
define('APP', $pluginRoot . DS . 'tests' . DS . 'TestApp' . DS);
define('CONFIG', $pluginRoot . DS . 'tests' . DS . 'TestApp' . DS . 'config' . DS);
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', $pluginRoot . DS . 'tests' . DS);
define('TEST_APP', TESTS . 'TestApp' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/cakephp/cakephp/src/functions.php';
require ROOT . '/vendor/autoload.php';

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\TestSuite\Fixture\SchemaLoader;

Configure::write('App', [
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'paths' => [
        'plugins' => [TEST_APP . 'Plugin' . DS],
        'templates' => [TEST_APP . 'templates' . DS],
    ],
]);
Configure::write('debug', true);

function ensureDirectoryExists(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

ensureDirectoryExists(TMP . 'cache/models');
ensureDirectoryExists(TMP . 'cache/persistent');
ensureDirectoryExists(TMP . 'cache/views');
ensureDirectoryExists(TMP . 'sessions');
ensureDirectoryExists(TMP . 'tests');
ensureDirectoryExists(LOGS);

$cache = [
    'default' => [
        'engine' => 'File',
        'path' => CACHE,
    ],
    '_cake_translations_' => [
        'className' => 'File',
        'prefix' => 'blaze_test_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => 'blaze_test_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
];

Cache::setConfig($cache);
Configure::write('Session', [
    'defaults' => 'php',
]);

Configure::write('App.encoding', 'utf8');

if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'timezone' => 'UTC',
]);

ConnectionManager::alias('test', 'default');

Configure::write('Authentication', [
    'unauthenticatedRedirect' => false,
    'queryParam' => 'redirect',
]);

Configure::write('BlazeCast', [
    'app_id' => 'test-app',
    'key' => 'test-key',
    'secret' => 'test-secret',
    'ping_interval' => 30,
    'activity_timeout' => 120,
    'allowed_origins' => ['*'],
    'max_message_size' => 10000,
]);

putenv('PUSHER_APP_KEY=test-key');
putenv('PUSHER_APP_SECRET=test-secret');

$loader = new SchemaLoader();
$loader->loadInternalFile(TESTS . 'schema.php');

$error = [
    'errorLevel' => E_ALL,
    'skipLog' => [],
    'log' => true,
    'trace' => true,
    'ignoredDeprecationPaths' => [],
];
(new ErrorTrap($error))->register();
