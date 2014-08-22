<?php
namespace ImagineEasy\CanHazDeploy;

use Aws\OpsWorks\OpsWorksClient;
use Doctrine\Common\Cache\Cache;

class OpsWorks
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var OpsWorksClient
     */
    private $client;

    /**
     * @param OpsWorksClient $client
     * @param array          $stacks
     * @param Cache          $cache
     */
    public function __construct(OpsWorksClient $client, array $stacks, Cache $cache)
    {
        $this->client = $client;
        $this->stacks = $stacks;
        $this->cache = $cache;
    }

    /**
     * @param string $org Github organization
     *
     * @return array
     */
    public function getApps($org)
    {
        $apps = [];

        $cacheId = sprintf('OpsWorksStack%s', $org);
        if ($this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        foreach ($this->stacks[$org] as $stackId) {

            foreach ($this->client->getIterator('DescribeApps', ['StackId' => $stackId]) as $app) {

                if (!isset($app['Domains'])) {
                    continue;
                }

                $version = $app['AppSource']['Revision'];

                $apps[$version] = [
                    'id' => $app['AppId'],
                    'name' => $app['Name'],
                    'repo' => $app['AppSource']['Url'],
                    'version' => $version,
                    'url' => sprintf('http://', $app['Domains'][0]),
                ];

            }
        }

        $this->cache->save($cacheId, $apps);

        return $apps;
    }

    /**
     * @param string $org Github organization
     * @param string $tag The version
     * @param string $app The name of the app
     *
     * @return bool
     */
    public function getDeployed($org, $tag, $app)
    {
        $apps = $this->getApps($org);

        if (array_key_exists($tag, $apps)) {

            $repo = $apps[$tag]['repo'];
            if (false !== strpos($repo, $app)) {
                return $apps[$tag];
            }
        }

        return false;
    }
}
