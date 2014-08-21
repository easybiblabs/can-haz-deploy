<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
    </head>
    <body>
<?php
$configFile = __DIR__ . '/etc/config.php';
if (!file_exists($configFile)) {
    die("Please setup configuration!");
}
$config = require $configFile;

$config['travis']['badgeUrl'] = 'https://magnum.travis-ci.com/%s.svg?token=%s&branch=%s';
$config['github']['repoUrl'] = 'https://api.github.com/orgs/%s/repos?per_page=100&type=all';
$config['github']['releaseUrl'] = 'https://github.com/%s/releases/tag/%s';

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("Please composer install!");
}
require $autoloader;

use ImagineEasy\CanHazDeploy;

$repositories = [];

$http = new CanHazDeploy\Http($config['github']['access_token']);
$github = new CanHazDeploy\Github($http, $config['github']);

foreach ($config['github']['organizations'] as $org) {

    $repositories = $github->getRepositories($org);
?>
        <h1>Can haz deploy [<?=$org?>]?</h1>
        <div class="row">

<?php
    foreach ($repositories as $repository) {

        if (true == $repository['fork']) {
            continue;
        }

        if (true === $repository['has_issues']) {
            continue;
        }

?>
            <div class="col-md-2">
                <h2><?=$repository['name']?></h2>
                <ul class="list-group">
<?php
        $branches = $github->getBranches($repository['tags_url']);

        foreach ($branches as $actual) {
            $badgeUrl = sprintf(
                $config['travis']['badgeUrl'],
                $repository['full_name'],
                $config['travis']['token'],
                $actual
            );

            $releaseUrl = sprintf(
                $config['github']['releaseUrl'],
                $repository['full_name'],
                $actual
            );

            if (false !== strpos($actual, 'master')) {
                $releaseUrl = '#';
            }

            echo '<li class="list-group-item">';
            echo sprintf('<b><a href="%s" target="_blank">', $releaseUrl) . "{$actual}</a></b>: ";
            echo '<img src="' . $badgeUrl . '" />';
            echo '</li>';
        }
?>

                </ul>
            </div>
<?php
    }
}
?>
        </div>
    </body>
</html>
