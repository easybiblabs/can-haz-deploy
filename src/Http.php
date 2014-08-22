<?php
namespace ImagineEasy\CanHazDeploy;

class Http
{
    private $accessToken;

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * @param string $url
     * @param array  $context
     *
     * @return array
     */
    public function requestGet($url, $context)
    {
        $response = file_get_contents($url, false, $this->getContext($context));
        return json_decode($response, true);
    }

    private function getContext($context)
    {
        return stream_context_create($context);
    }
}
