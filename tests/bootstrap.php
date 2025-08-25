<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;

require dirname(__DIR__) . '/vendor/autoload.php';

define('PLUGIN_ROOT', dirname(__DIR__));
define('ROOT', PLUGIN_ROOT . DS . 'tests' . DS . 'test_app');
define('TMP', sys_get_temp_dir() . DS . 'LatteViewTmp' . DS);
define('CACHE', TMP . 'cache' . DS);
define('APP', ROOT . DS . 'src' . DS);
define('APP_DIR', 'src');
define('CAKE_CORE_INCLUDE_PATH', PLUGIN_ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);
define('WWW_ROOT', PLUGIN_ROOT . DS . 'webroot' . DS);
define('TESTS', __DIR__ . DS);
define('CONFIG', TESTS . 'config' . DS);

require_once CAKE_CORE_INCLUDE_PATH . DS . 'src' . DS . 'I18n' . DS . 'functions_global.php';

Configure::write('debug', true);

Configure::write('App', [
    'encoding' => 'UTF-8',
    'namespace' => 'TestApp',
    'paths' => [
        'templates' => [ROOT . DS . 'templates' . DS],
    ],
]);

if (!is_dir(TMP)) {
    mkdir(TMP, 0770, true);
}

if (!is_dir(CACHE)) {
    mkdir(CACHE, 0770, true);
}

$cache = [
    'default' => [
        'engine' => 'File',
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => '_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
];

Cache::setConfig($cache);
