<?php

namespace Egzakt\SystemBundle\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class EntityRoutingBuilder
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ArrayCollection
     */
    private $routes;

    const entityPropertyFrontend = 'slug';
    const entityPropertyDefault = 'id';

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->routes = new ArrayCollection();
    }

    /**
     * @param $app
     * @param $entity
     * @param $route
     * @param $routeProperty
     * @param $entityProperty
     * @throws Exception
     */
    public function add($app, $entity, $route, $routeProperty, $entityProperty = null)
    {
        $oApp = $this->getEntityManager()->getRepository('EgzaktSystemBundle:App')->findOneBy(array('name' => $app));
        if (null === $oApp) {
            throw new Exception('Unknown app : '.$app);
        }

        try {
            $this->getEntityManager()->getClassMetadata($entity);
        } catch (Exception $e) {
            throw new Exception('Unknown entity : '.$entity);
        }

        if (null === $entityProperty) {
            switch ($app) {
                case 'frontend':
                    $entityProperty = EntityRoutingBuilder::entityPropertyFrontend;
                    break;
                default:
                    $entityProperty = EntityRoutingBuilder::entityPropertyDefault;
            }
        }

        $alias = $app.$entity.$route;
        $mapping = $this->getAlias($alias);
        if (null !== $mapping) {
            throw new Exception('Route mapping already exists for "'.$app.' / '.$entity.'" with route : "'.$mapping->getRoute().'" ');
        }

        $route = new EntityRoute($alias, $app, $entity, $route, $routeProperty, $entityProperty);
        $this->getRoutes()->add($route);
    }

    /**
     * @param $alias
     * @param $app
     * @param $entity
     * @param $route
     * @param $routeProperty
     * @param null $entityProperty
     * @throws Exception
     */
    public function addAlias($alias, $app, $entity, $route, $routeProperty, $entityProperty = null)
    {
        $oApp = $this->getEntityManager()->getRepository('EgzaktSystemBundle:App')->findOneBy(array('name' => $app));
        if (null === $oApp) {
            throw new Exception('Unknown app : '.$app);
        }

        try {
            $this->getEntityManager()->getClassMetadata($entity);
        } catch (Exception $e) {
            throw new Exception('Unknown entity : '.$entity);
        }

        if (null === $entityProperty) {
            switch ($app) {
                case 'frontend':
                    $entityProperty = EntityRoutingBuilder::entityPropertyFrontend;
                    break;
                default:
                    $entityProperty = EntityRoutingBuilder::entityPropertyDefault;
            }
        }

        $mapping = $this->getAlias($alias);
        if (null !== $mapping) {
            throw new Exception('Route mapping already exists for "'.$app.' / '.$entity.'" with route : "'.$mapping->getRoute().'" ');
        }

        $route = new EntityRoute($alias, $app, $entity, $route, $routeProperty, $entityProperty);
        $this->getRoutes()->add($route);
    }

    /**
     * Get an entity-route
     *
     * @param $alias
     * @return EntityRoute|null
     */
    public function getAlias($alias)
    {

        foreach ($this->getRoutes() as $route) {
            if ($route->equals($alias)) {
                return $route;
            }
        }

        return null;
    }

    public function get($app, $entity)
    {
        foreach ($this->getRoutes() as $route) {
            if ($route->getApp() == $app && $route->getEntity() == $entity) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

}
