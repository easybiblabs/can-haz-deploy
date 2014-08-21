<?php
namespace ImagineEasy\CanHazDeploy;

class Http
{
    private $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param $url
     *
     * @return array
     */
    public function request($url)
    {
        $response = file_get_contents($url, false, $this->getContext());
        return json_decode($response, true);
    }

    private function getContext()
    {
        return stream_context_create(
            [
                'http' => [
                    'header' => 'User-Agent: can-haz-deploy' . "\n" . "Authorization: token {$this->accessToken}",
                    'ignore_errors' => true
                ],
            ]
        );
    }
}
