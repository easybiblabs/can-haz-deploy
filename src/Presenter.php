<?php
namespace ImagineEasy\CanHazDeploy;

class Presenter
{
    private $branch;

    private $config;

    private $repository;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $branch
     *
     * @return self
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
        return $this;
    }

    /**
     * @param string $name E.g. can-haz-deploy
     *
     * @return self
     */
    public function setRepository($name)
    {
        $this->repository = $name;
        return $this;
    }

    /**
     * @param string $fullName E.g. easybiblabs/can-haz-deploy
     *
     * @return string
     */
    public function getBadgeUrl($fullName)
    {
        return sprintf(
            $this->config['travis']['badgeUrl'],
            $fullName,
            $this->config['travis']['token'],
            $this->branch
        );
    }

    public function getDataGroup()
    {
        return sprintf('%s-accordion', $this->repository);
    }

    public function getDataTarget()
    {
        return sprintf('%s-%s', $this->repository, str_replace('.', '', $this->branch));
    }

    public function getDeployTicketState(array $deployTicket)
    {
        $state = '<span class="glyphicon %s"></span> ';
        if ('closed' == $deployTicket['state']) {
            $state = sprintf($state, 'glyphicon-ok');
        } else {
            $state = sprintf($state, 'glyphicon-fire');
        }
        return $state;
    }

    public function getReleaseUrl($fullName)
    {
        $url = sprintf(
            $this->config['github']['releaseUrl'],
            $fullName,
            $this->branch
        );

        if (false !== strpos($this->branch, 'master')) {
            $url = '#';
        }

        return $url;
    }
}
