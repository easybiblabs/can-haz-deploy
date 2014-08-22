can-haz-deploy
==============

A dashboard to check if releases are clean and where they are deployed.

 * `composer install`
 * configure `etc/config.php`
 * use PHP webserver

## caching

Delete var/cache/* to reset the cache.

 * Github repositories: cached for a day
 * Github issues/tags: cached for 5 minutes
 * Travis-CI token/repository status (enabled): cached for a day
