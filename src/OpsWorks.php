<?php
namespace ImagineEasy\CanHazDeploy;

use Aws\OpsWorks\OpsWorksClient;

class OpsWorks
{
    private $client;

    public function __construct(OpsWorksClient $client, array $stacks)
    {
        $this->client = $client;
        $this->stacks = $stacks;
    }

    /**
     * @param string $org
     *
     * @return array
     */
    public function getApps($org)
    {
        static $apps = [];
        if (!empty($apps)) {
            return $apps;
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

        return $apps;
    }

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
