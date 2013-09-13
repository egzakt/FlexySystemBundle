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
        $oApp = $this->em->getRepository('EgzaktSystemBundle:App')->findOneBy(array('name' => $app));
        if (null === $oApp) {
            throw new Exception('Unknown app : '.$app);
        }

        try {
            $this->em->getClassMetadata($entity);
        } catch (Exception $e) {
            throw new Exception('Unknown entity : '.$entity);
        }

        if (null === $entityProperty) {
            switch ( $app ) {
                case 'frontend':
                    $entityProperty = 'slug';
                    break;
                default:
                    $entityProperty = 'id';
            }
        }

        $route = new EntityRoute($app, $entity, $route, $routeProperty, $entityProperty);
        $this->getRoutes()->add($route);
    }

    /**
     * Get an entity-route
     *
     * @param $app
     * @param $entity
     * @return EntityRoute|null
     */
    public function get($app, $entity)
    {

        foreach ($this->getRoutes() as $route) {
            if ($route->equals($app, $entity)) {
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

}
