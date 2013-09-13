<?php

namespace Egzakt\SystemBundle\Lib;

class EntityRoute
{

    /**
     * @var string
     */
    private $app;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $route;

    /**
     * @var string
     */
    private $routeProperty;

    /**
     * @var string
     */
    private $entityProperty;

    /**
     * @param string $app
     * @param string $entity
     * @param string $route
     * @param string $routeProperty
     * @param string $entityProperty
     */
    public function __construct($app, $entity, $route, $routeProperty, $entityProperty)
    {
        $this->app = $app;
        $this->entity = $entity;
        $this->route = $route;
        $this->routeProperty = $routeProperty;
        $this->entityProperty = $entityProperty;
    }

    /**
     * @return string
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getRouteProperty()
    {
        return $this->routeProperty;
    }

    /**
     * @return string
     */
    public function getEntityProperty()
    {
        return $this->entityProperty;
    }

    /**
     * @param string $app
     * @param string $entity
     * @return bool
     */
    public function equals($app, $entity)
    {
        return $this->getApp() === $app && $this->getEntity() === $entity;
    }

}