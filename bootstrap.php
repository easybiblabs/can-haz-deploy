<?php
$configFile = __DIR__ . '/etc/config.php';
if (!file_exists($configFile)) {
    die("Please setup configuration!");
}
$config = require $configFile;

$config['github']['releaseUrl'] = 'https://github.com/%s/releases/tag/%s';
$config['travis']['badgeUrl'] = 'https://magnum.travis-ci.com/%s.svg?token=%s&branch=%s';

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("Please composer install!");
}
require $autoloader;

use Aws\OpsWorks\OpsWorksClient;
use Doctrine\Common\Cache;
use ImagineEasy\CanHazDeploy;

$repositories = [];

$cache = new Cache\FilesystemCache(__DIR__ . '/var/cache');
$http = new CanHazDeploy\Http($cache);
$github = new CanHazDeploy\Github($http, $config['github']);
$travis = new CanHazDeploy\Travis($http, $config['github']['access_token']);
$opsworks = new CanHazDeploy\OpsWorks(
    OpsWorksClient::factory($config['opsworks']['config']),
    $config['opsworks']['stacks'],
    $cache
);
