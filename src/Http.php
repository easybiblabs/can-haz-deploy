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
     * @throws \RuntimeException
     */
    public function request($url, $context)
    {
        $response = file_get_contents($url, false, $this->getContext($context));

        $matches = array();
        preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches);
        if ($matches[1] < 400) {
            return json_decode($response, true);
        }

        switch ($matches[1]) {
            case 401:
                $msg = "Unauthorized for {$url}!";
                break;
            default:
                $msg = "Error occurred: {$matches[1]}";
        }
        throw new \RuntimeException($msg, $matches[1]);
    }

    private function getContext($context)
    {
        return stream_context_create($context);
    }
}
