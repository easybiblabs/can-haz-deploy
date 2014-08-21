<?php
namespace ImagineEasy\CanHazDeploy;

use chobie\VersionSorter;

class Github
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Http
     */
    private $http;

    /**
     * @param Http  $http
     * @param array $config
     */
    public function __construct(Http $http, array $config)
    {
        $this->http = $http;
        $this->config = $config;
    }

    /**
     * @param string $tagsUrl
     *
     * @return array
     */
    public function getBranches($tagsUrl)
    {
        $tagsResponse = $this->http->request($tagsUrl);

        $branches = [];

        foreach (array_merge($tagsResponse, [['name' => 'master']]) as $tag) {
            $branches[] = $tag['name'];
        }

        return VersionSorter::rsort($branches);
    }

    /**
     * @param string $org
     *
     * @return array
     */
    public function getRepositories($org)
    {
        $url = sprintf($this->config['repoUrl'], $org);
        return $this->http->request($url);
    }
}
