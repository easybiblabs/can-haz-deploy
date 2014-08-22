<?php
namespace ImagineEasy\CanHazDeploy;

class Http
{
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
