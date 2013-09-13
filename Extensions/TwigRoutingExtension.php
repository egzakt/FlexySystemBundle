<?php

namespace Egzakt\SystemBundle\Extensions;

use Egzakt\SystemBundle\Lib\Core;
use Egzakt\SystemBundle\Lib\EntityRouting;
use Egzakt\SystemBundle\Lib\RouterAutoParametersHandler;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class TwigRoutingExtension extends \Twig_Extension
{

    /**
     * @var Router
     */
    private $router;

    /**
     * @var EntityRouting
     */
    private $entityRouting;

    /**
     * @var RouterAutoParametersHandler
     */
    private $paramsHandler;

    /**
     * @var Core
     */
    private $systemCore;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    public function __construct(Core $systemCore, EntityRouting $er, Router $router, RouterAutoParametersHandler $ph, PropertyAccessorInterface $pai)
    {
        $this->systemCore = $systemCore;
        $this->router = $router;
        $this->entityRouting = $er;
        $this->accessor = $pai;
        $this->paramsHandler = $ph;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            'entitypath' => new \Twig_Function_Method($this, 'entityPath'),
        );
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'egzakt_system_routing_extension';
    }

    /**
     * Return the URL of an entity mapped by the EntityRoute service.
     *
     * @param $entity
     * @param string|null $extraRoute
     * @param array $extraParams
     * @return string
     */
    public function entityPath($entity, $extraRoute = null, $extraParams = array())
    {
        $mapping = $this->getMapping($entity);
        $value = $this->getAccessor()->getValue($entity, $mapping->getEntityProperty());

        $routeParams = $this->generateRouteParams(
            array($mapping->getRouteProperty() => $value),
            $extraParams
        );

        if ('backend' === $mapping->getApp() ) {
            $r = $this->getRouter()->generate(
                $this->generateRouteName($mapping->getRoute(), $extraRoute),
                $this->getParamsHandler()->inject($routeParams)
            );
        } else {
            $r = $this->getRouter()->generate(
                $this->generateRouteName($mapping->getRoute(), $extraRoute),
                $routeParams
            );
        }
        return $r;

    }

    /**
     * @param $initialRoute
     * @param string|null $extraRoute
     * @return string
     */
    protected function generateRouteName($initialRoute, $extraRoute = null)
    {
        $route = $initialRoute;

        if (null !== $extraRoute) {
            if (substr($extraRoute, 0, 1) !== '_') {
                $route.= '_';
            }
            $route.= $extraRoute;
        }

        return $route;
    }

    /**
     * @param $initialParams
     * @param array $extraParams
     * @return array
     */
    protected function generateRouteParams($initialParams, $extraParams = array())
    {
        $params = $initialParams;

        if ( null !== $extraParams ) {
            $params = array_merge($initialParams, $extraParams);
        }

        return $params;
    }

    /**
     * @param $entity
     * @return EntityRoute
     */
    protected function getMapping($entity)
    {
        return $this->getEntityRouting()->get(
            $this->getSystemCore()->getCurrentAppName(),
            get_class($entity)
        );
    }

    /**
     * @return Core
     */
    protected function getSystemCore()
    {
        return $this->systemCore;
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        return $this->router;
    }

    /**
     * @return EntityRouting
     */
    protected function getEntityRouting()
    {
        return $this->entityRouting;
    }

    /**
     * @return PropertyAccessorInterface
     */
    protected function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * @return RouterAutoParametersHandler
     */
    protected function getParamsHandler()
    {
        return $this->paramsHandler;
    }

}