<?php
use ImagineEasy\CanHazDeploy\Presenter;

require dirname(__DIR__) . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
    </head>
    <body>
    <div class="container-fluid">
<?php
foreach ($config['github']['organizations'] as $org) {

    $deployTickets = $github->getDeployTickets($org);

?>
        <h1>Can haz deploy [<?=$org['name']?>]?</h1>
        <div class="row">

        <?php if (empty($deployTickets)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error</strong> Found no deploy tickets. Github or the cache is broken!
            </div>
        <?php endif; ?>
<?php

    $presenter = new Presenter($config);

    foreach ($github->getRepositories($org['name']) as $repository) {

        if (false === $travis->isEnabled($repository['full_name'])) {
            continue;
        }

        $repositoryName = $repository['name'];

        $dataGroup = $presenter
            ->setRepository($repositoryName)
            ->getDataGroup()
        ;
?>
            <div class="col-md-2">
                <h2><?=$repositoryName?></h2>

                <div class="panel-group" id="<?=$dataGroup?>">
<?php
        $tagsUrl = $repository['tags_url']. '?page=1&per_page=' . $config['display']['skip'];

        $branchCounter = 0;
        foreach ($github->getBranches($tagsUrl) as $actual) {

            $presenter->setBranch($actual);

            $badgeUrl = $presenter->getBadgeUrl($repository['full_name']);
            $releaseUrl = $presenter->getReleaseUrl($repository['full_name']);

            /**
             * @desc Collapse from the 3th release on!
             */
            $class = ' in';
            if ($branchCounter > $config['display']['collapse']) {
                $class = '';
            }

            $deployTicket = $github->findDeployTicket($actual, $deployTickets, $repositoryName);
            $dataTarget = $presenter->getDataTarget();
?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#<?=$dataGroup?>" href="#<?=$dataTarget?>">
                                    <?=$actual?>
                                </a>
                            </h4>
                        </div>
                        <div id="<?=$dataTarget?>" class="panel-collapse collapse<?=$class?>">
                            <ul>
                                <li><img src="<?=$badgeUrl?>" /></li>
                            <?php if ('#' !== $releaseUrl): ?>
                                <li><?=sprintf('<a href="%s" target="_blank">', $releaseUrl)?>Github release</a></li>
                            <?php endif;
                            if (false !== $deployTicket):
                                $state = $presenter->getDeployTicketState($deployTicket);
                            ?>
                                <li><?=$state?><a href="<?=$deployTicket['url'];?>"><?=$deployTicket['title']?></a></li>
                            <?php
                            endif;

                            if (false !== ($app = $opsworks->getDeployed($org['name'], $actual, $repositoryName))) {
                                echo '<li class="bg-success"><span class="glyphicon glyphicon-ok"></span> <a class="btn btn-xs" href="' . $app['url'] .'" target="_blank">Currently deployed!</a>';
                            }
                            ?>
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

        &copy; 2014 &amp; beyond: <a href="https://twitter.com/klimpong">Till Klamp&auml;ckel</a> for Imagine Easy Solutions LLC
        &mdash; <a href="https://github.com/easybiblabs/can-haz-deploy">Can Haz Deploy</a>
    </div>
    </body>
</html>
