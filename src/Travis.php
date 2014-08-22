<?php
namespace ImagineEasy\CanHazDeploy;

class Travis
{
    private $api = 'https://api.travis-ci.com';

    private $accessToken;

    private $http;

    public function __construct(Http $http, $githubAccessToken)
    {
        $this->http = $http;
        $this->accessToken = $this->getAccessToken($githubAccessToken);
    }

    public function isEnabled($fullName)
    {
        $url = sprintf('%s/repos/%s', $this->api, $fullName);

        $context = $this->http->buildContext('GET', $this->getHeaders($this->accessToken));

        $repoResponse = $this->http->request($url, $context);
        if (null !== $repoResponse['repo']['last_build_started_at']) {
            return true;
        }

        return false;
    }

    private function getAccessToken($githubAccessToken)
    {
        $url = sprintf('%s/auth/github', $this->api);

        $context = [
            'http' => [
                'content' => json_encode(
                    [
                        "github_token" => $githubAccessToken
                    ]
                ),
                'header' => $this->getHeaders(),
                'ignore_errors' => true,
                'method' => 'POST',
            ]
        ];

        $tokenResponse = $this->http->request($url, $context);
        return $tokenResponse['access_token'];
    }

    private function getHeaders($accessToken = null)
    {
        $headers = [
            'Accept: application/vnd.travis-ci.2+json',
            'Content-Type: application/json',
            'User-Agent: can-haz-deploy',
        ];

        if (null !== $accessToken) {
            $headers[] = sprintf('Authorization: token %s', $accessToken);
        }

        return $this->http->buildHeaders($headers);
    }
}
