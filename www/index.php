<?php
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

            $deployTicket = $github->findDeployTicket($actual, $deployTickets, $repository['name']);
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
                            <?php if ('#' !== $releaseUrl): ?>
                                <li><?=sprintf('<a href="%s" target="_blank">', $releaseUrl)?>Github release</a></li>
                            <?php endif;
                            if (false !== $deployTicket):
                                $state = '<span class="glyphicon %s"></span> ';
                                if ('closed' == $deployTicket['state']) {
                                    $state = sprintf($state, 'glyphicon-ok');
                                } else {
                                    $state = sprintf($state, 'glyphicon-fire');
                                }
                            ?>
                                <li><?=$state?><a href="<?=$deployTicket['url'];?>"><?=$deployTicket['title']?></a></li>
                            <?php endif;
                            if (false !== ($app = $opsworks->getDeployed($org['name'], $actual, $repository['name']))):
                                echo '<li class="bg-success"><span class="glyphicon glyphicon-ok"></span> <a class="btn btn-xs" href="' . $app['url'] .'" target="_blank">Currently deployed!</a>';
                            endif;
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
