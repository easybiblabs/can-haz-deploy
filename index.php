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
$config['github']['issuesUrl'] = 'https://api.github.com/repos/%s/%s/issues?labels=deployment&state=all';
$config['github']['repoUrl'] = 'https://api.github.com/orgs/%s/repos?per_page=100&type=all';
$config['github']['releaseUrl'] = 'https://github.com/%s/releases/tag/%s';

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("Please composer install!");
}
require $autoloader;

use ImagineEasy\CanHazDeploy;

$repositories = [];

$http = new CanHazDeploy\Http;
$github = new CanHazDeploy\Github($http, $config['github']);
$travis = new CanHazDeploy\Travis($http, $config['github']['access_token']);

foreach ($config['github']['organizations'] as $org) {

    $deployTickets = $github->getDeployTickets($org);
    $repositories = $github->getRepositories($org['name']);
?>
        <h1>Can haz deploy [<?=$org['name']?>]?</h1>
        <div class="row">

<?php
    foreach ($repositories as $repository) {

        if (true == $repository['fork']) {
            continue;
        }

        if (true === $repository['has_issues']) {
            continue;
        }

        if (false === $travis->isEnabled($repository['full_name'])) {
            continue;
        }

        $dataGroup = sprintf('%s-accordion', $repository['name']);

?>
            <div class="col-md-2">
                <h2><?=$repository['name']?></h2>

                <div class="panel-group" id="<?=$dataGroup?>">
<?php
        $branches = $github->getBranches($repository['tags_url']);

        $branchCounter = 0;
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

            /**
             * @desc Collapse from the 3th release on!
             */
            $class = ' in';
            if ($branchCounter > $config['display']['collapse']) {
                $class = '';
            }

            /**
             * @desc Skip older releases!
             */
            if ($branchCounter > $config['display']['skip']) {
                break;
            }

            $deployTicket = $github->findDeployTicket($actual, $deployTickets);
            $dataTarget = sprintf('%s-%s', $repository['name'], str_replace('.', '', $actual));
?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#<?=$dataGroup?>" href="#<?=$dataTarget?>"><?=$actual?></a>
                            </h4>
                        </div>
                        <div id="<?=$dataTarget?>" class="panel-collapse collapse<?=$class?>">
                            <ul>
                                <li><img src="<?=$badgeUrl?>" /></li>
                                <li><?=sprintf('<a href="%s" target="_blank">', $releaseUrl)?>Github release</a></li>
                            <?php if (false !== $deployTicket): ?>
                                <li><a href="<?=$deployTicket['url'];?>"><?=$deployTicket['title']?></a></li>
                            <?php endif; ?>
                            </ul>
                        </div>
                    </div>
<?php
            $branchCounter++;
        }
?>

                </div>
            </div>
<?php
    }
}
?>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    </body>
</html>
