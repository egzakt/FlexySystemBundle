<?php

namespace Egzakt\SystemBundle\Extensions;

use Egzakt\SystemBundle\Lib\Core;
use Egzakt\SystemBundle\Lib\EntityRouting;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;

use Egzakt\SystemBundle\Lib\RouterAutoParametersHandler;

class RoutingExtension extends BaseRoutingExtension
{

    /**
     * @var RouterAutoParametersHandler
     */
    private $autoParametersHandler;

    /**
     * @var Core
     */
    private $systemCore;

    /**
     * @var EntityRouting
     */
    private $entityRouting;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    public function __construct(UrlGeneratorInterface $generator, RouterAutoParametersHandler $raph, Core $systemCore, EntityRouting $er, PropertyAccessorInterface $pai)
    {
        parent::__construct($generator);
        $this->autoParametersHandler = $raph;
        $this->systemCore = $systemCore;
        $this->entityRouting = $er;
        $this->accessor = $pai;

    }

    public function getFunctions()
    {
        $functions = parent::getFunctions();
        $functions[] = new \Twig_SimpleFunction('entitypath', array($this, 'entityPath'));
        return $functions;
    }

    /**
     * Overriden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getPath($name, $parameters = array(), $relative = false)
    {
        $parameters = $this->getParamsHandler()->inject($parameters);
        return parent::getPath($name, $parameters, $relative);
    }

    /**
     * Overriden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getUrl($name, $parameters = array(), $schemeRelative = false)
    {
        $parameters = $this->getParamsHandler()->inject($parameters);
        return parent::getPath($name, $parameters, $schemeRelative);
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
        if (null === $value) {
            $value = 0;
        }

        if (null === $extraRoute) {
            $routeParams = $this->generateRouteParams($extraParams);
        } else {
            $routeParams = $this->generateRouteParams(
                array($mapping->getRouteProperty() => $value),
                $extraParams
            );
        }

        if ('backend' === $mapping->getApp() ) {
            $r = $this->getPath(
                $this->generateRouteName($mapping->getRoute(), $extraRoute),
                $this->getParamsHandler()->inject($routeParams)
            );
        } else {
            $r = $this->getPath(
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
        return $this->autoParametersHandler;
    }

}
