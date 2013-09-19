<?php

namespace Egzakt\SystemBundle\Lib;

class EntityRouting
{

    /**
     * @var EntityRoutingBuilder
     */
    private $builder;

    /**
     * @param EntityRoutingBuilder $erb
     */
    public function __construct(EntityRoutingBuilder $erb)
    {
        $this->builder = $erb;
    }

    /**
     * @param EntityRouteMappingInterface $mapping
     */
    public function registerMapping(EntityRouteMappingInterface $mapping)
    {
        $mapping->map($this->getBuilder());
    }

    /**
     * @param $app
     * @param $entity
     * @return EntityRoute|null
     */
    public function get($app, $entity)
    {
        return $this->getBuilder()->get($app, $entity);
    }

    public function getAlias($alias, $entity)
    {
        return $this->getBuilder()->getAlias($alias, $entity);
    }

    /**
     * @return EntityRoutingBuilder
     */
    protected function getBuilder()
    {
        return $this->builder;
    }

}
