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

    private $urlIssues = 'https://api.github.com/repos/%s/%s/issues?labels=%s&state=all&sort=updated&per_page=100';
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
     * @param string $app
     *
     * @return array|false
     */
    public function findDeployTicket($release, array $tickets, $app)
    {
        foreach ($tickets as $ticket) {
            if (false === strpos($ticket['title'], $release)) {
                continue;
            }

            $labels = implode(',', $ticket['labels']);
            if (false === strpos($labels, $app)) {
                continue;
            }

            return $ticket;
        }

        return false;
    }

    /**
     * @param string $tagsUrl
     *
     * @return \Generator
     */
    public function getBranches($tagsUrl)
    {
        $tagsResponse = $this->http->request($tagsUrl, $this->context);

        $branches = [];

        foreach (array_merge($tagsResponse, [['name' => 'master']]) as $tag) {
            $branches[] = $tag['name'];
        }

        $branches = VersionSorter::rsort($branches);
        foreach ($branches as $branch) {
            yield $branch;
        }
    }

    /**
     * @param array $org['name','issues']
     *
     * @return array
     */
    public function getDeployTickets(array $org)
    {
        $url = sprintf($this->urlIssues, $org['name'], $org['issues'], $this->config['release_label']);
        $ticketResponse = $this->http->request($url, $this->context);

        $tickets = [];

        foreach ($ticketResponse as $ticket) {

            $labels = [];

            foreach ($ticket['labels'] as $label) {
                $labels[] = $label['name'];
            }

            $tickets[] = [
                'labels' => $labels,
                'state' => $ticket['state'],
                'title' => $ticket['title'],
                'url' => $ticket['html_url'],
            ];
        }

        return $tickets;
    }

    /**
     * @param string $org
     *
     * @return \Generator
     */
    public function getRepositories($org)
    {
        $url = sprintf($this->urlRepo, $org);
        foreach ($this->http->request($url, $this->context, 86400) as $repository) {
            if (true == $repository['fork']) {
                continue;
            }

            if (true === $repository['has_issues']) {
                continue;
            }

            yield $repository;
        }
    }
}
