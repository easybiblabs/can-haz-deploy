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

    private $urlIssues = 'https://api.github.com/repos/%s/%s/issues?labels=deployment&state=all';
    private $urlRepo = 'https://api.github.com/orgs/%s/repos?per_page=100&type=all';

    /**
     * @param Http  $http
     * @param array $config
     */
    public function __construct(Http $http, array $config)
    {
        $this->http = $http;
        $this->config = $config;

        $this->context = $this->http->buildContext(
            'GET',
            $http->buildHeaders(
                [
                    'User-Agent: can-haz-deploy',
                    "Authorization: token {$this->config['access_token']}",
                ]
            )
        );
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
        $url = sprintf($this->urlIssues, $org['name'], $org['issues']);
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
        $url = sprintf($this->urlRepo, $org);
        return $this->http->request($url, $this->context, 86400);
    }
}
