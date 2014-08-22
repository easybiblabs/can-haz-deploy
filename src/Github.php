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
     * @var array
     */
    private $context;

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

        $this->context = [
            'http' => [
                'header' => 'User-Agent: can-haz-deploy' . "\n" . "Authorization: token {$this->config['access_token']}",
                'ignore_errors' => true
            ],
        ];
    }

    /**
     * @param string $release
     * @param array  $tickets
     *
     * @return array|false
     */
    public function findDeployTicket($release, array $tickets)
    {
        foreach ($tickets as $ticket) {
            if (false !== strpos($ticket['title'], $release)) {
                return $ticket;
            }
        }

        return false;
    }

    /**
     * @param string $tagsUrl
     *
     * @return array
     */
    public function getBranches($tagsUrl)
    {
        $tagsResponse = $this->http->request($tagsUrl, $this->context);

        $branches = [];

        foreach (array_merge($tagsResponse, [['name' => 'master']]) as $tag) {
            $branches[] = $tag['name'];
        }

        return VersionSorter::rsort($branches);
    }

    /**
     * @param array $org['name','issues']
     *
     * @return array
     */
    public function getDeployTickets(array $org)
    {
        $url = sprintf($this->config['issuesUrl'], $org['name'], $org['issues']);
        $ticketResponse = $this->http->request($url, $this->context);

        $tickets = [];
        foreach ($ticketResponse as $ticket) {
            $tickets[] = [
                'title' => $ticket['title'],
                'url' => $ticket['html_url'],
                'state' => $ticket['state'],
            ];
        }

        return $tickets;
    }

    /**
     * @param string $org
     *
     * @return array
     */
    public function getRepositories($org)
    {
        $url = sprintf($this->config['repoUrl'], $org);
        return $this->http->request($url, $this->context);
    }
}
