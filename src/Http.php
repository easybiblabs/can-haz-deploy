<?php
namespace ImagineEasy\CanHazDeploy;

use Doctrine\Common\Cache\Cache;

class Http
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $method
     * @param string $headers
     *
     * @return array
     */
    public function buildContext($method, $headers)
    {
        $allOptions = [
            'http' => [
                'header' => $headers,
                'ignore_errors' => true,
                'method' => $method,
            ]
        ];

        return $allOptions;
    }

    /**
     * @param array $headers
     *
     * @return string
     */
    public function buildHeaders(array $headers)
    {
        return implode("\r\n", $headers);
    }

    /**
     * @param string $url
     * @param array  $context
     * @param int    $ttl
     *
     * @return array
     * @throws \RuntimeException
     */
    public function request($url, $context, $ttl = 300)
    {
        $cacheId = $this->createCacheId($url, $context);
        if ($this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        $jsonResponse = file_get_contents($url, false, $this->getContext($context));

        $matches = array();
        preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches);
        if ($matches[1] < 400) {
            $response = json_decode($jsonResponse, true);
            $this->cache->save($cacheId, $response, $ttl);
            return $response;
        }

        $msg = sprintf("URL: %s", $url);
        switch ($matches[1]) {
            case 401:
                $msg .= "Unauthorized for {$url}!";
                break;
            default:
                $msg .= "Error occurred: {$matches[1]}";
        }
        throw new \RuntimeException($msg, $matches[1]);
    }

    private function createCacheId($url, $context)
    {
        return md5($url . serialize($context));
    }

    private function getContext($context)
    {
        return stream_context_create($context);
    }
}
