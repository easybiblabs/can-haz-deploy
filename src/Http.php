<?php
namespace ImagineEasy\CanHazDeploy;

class Http
{
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
     *
     * @return array
     */
    public function request($url, $context)
    {
        $response = file_get_contents($url, false, $this->getContext($context));
        return json_decode($response, true);
    }

    private function getContext($context)
    {
        return stream_context_create($context);
    }
}
