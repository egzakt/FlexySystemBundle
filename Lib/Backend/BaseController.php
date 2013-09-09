<?php

namespace Egzakt\SystemBundle\Lib\Backend;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Egzakt\SystemBundle\Entity\App;
use Egzakt\SystemBundle\Entity\Section;
use Egzakt\SystemBundle\Lib\BaseControllerInterface;
use Egzakt\SystemBundle\Lib\NavigationElement;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;

use \Swift_Message;

/**
 * Base Controller for all Egzakt backend bundles
 */
abstract class BaseController extends Controller implements BaseControllerInterface
{
    /**
     * Init
     */
    public function init()
    {
        // base implementation
    }

    /**
     * Return the core
     *
     * @return Core
     */
    public function getCore()
    {
        return $this->container->get('egzakt_backend.core');
    }

    /**
     * Return the Backend Core.
     *
     * @deprecated Use getCore.
     *
     * @return BackendCore
     */
    public function getBackendCore()
    {
        return $this->getCore();
    }

    /**
     * Return the system core
     *
     * @return Core
     */
    public function getSystemCore()
    {
        return $this->container->get('egzakt_system.core');
    }

    /**
     * Get the Section entity
     *
     * @return Section
     */
    public function getSection()
    {
        return $this->getCore()->getSection();
    }

    /**
     * Get the Bundle Name
     * @deprecated
     * @return string
     */
    public function getBundleName()
    {
        trigger_error('getBundleName is deprecated.', E_USER_DEPRECATED);

        return $this->getCore()->getBundleName();
    }

    /**
     * Get the current app entity
     *
     * @return App
     */
    public function getApp()
    {
        return $this->getCore()->getApp();
    }

    /**
     * Get the current app name
     *
     * @return string
     */
    public function getCurrentAppName()
    {
        return $this->getSystemCore()->getCurrentAppName();
    }

    /**
     * Get the Entity Manager
     * @deprecated
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Helper method to create a navigation element
     *
     * @param $name
     * @param $route
     * @param array $routeParams
     *
     * @return NavigationElement
     */
    protected function createNavigationElement($name, $route, $routeParams = array())
    {
        $navigationElement = new NavigationElement();
        $navigationElement->setContainer($this->container);
        $navigationElement->setName($name);
        $navigationElement->setRouteBackend($route);
        $navigationElement->setRouteBackendParams($routeParams);

        return $navigationElement;
    }

    /**
     * Push a navigation element on top on the navigation element stack
     *
     * @param $element
     *
     * @deprecated Use pushNavigationElement instead
     */
    protected function addNavigationElement($element)
    {
        trigger_error('addNavigationElement is deprecated. Use pushNavigationElement instead.', E_USER_DEPRECATED);

        $this->pushNavigationElement($element);
    }

    /**
     * Push a navigation element on top on the navigation element stack.
     *
     * @param $element
     */
    protected function pushNavigationElement($element)
    {
        $this->getCore()->addNavigationElement($element);
    }

    /**
     * Helper method to create and push a navigation element to the navigation stack.
     *
     * @param $name
     * @param $route
     * @param array $routeParams
     */
    protected function createAndPushNavigationElement($name, $route, $routeParams = array())
    {
        $navigationElement = $this->createNavigationElement($name, $route, $routeParams);
        $this->pushNavigationElement($navigationElement);
    }

    /**
     * @inheritdoc
     */
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate(
            $route,
            $this->get('egzakt_system.router_auto_parameters_handler')->inject($parameters),
            $referenceType
        );
    }

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Set a success message flash.
     * @param $message
     */
    protected function setSuccessFlash($message)
    {
        $this->addFlash('success', $message);
    }

    /**
     * Set an error message flash.
     * @param $message
     */
    protected function setErrorFlash($message)
    {
        $this->addFlash('error', $message);
    }

    /**
     * @param $classname
     * @return BaseEntityRepository
     */
    protected function getRepository($classname)
    {
        return $this->getDoctrine()->getRepository($classname);
    }

    /**
     * Invalidate the router, refresh the cache.
     */
    protected function invalidateRouter()
    {
        $this->get('egzakt_system.router_invalidator')->invalidate();
    }

    /**
     * Redirect user depending of the condition. If it's true, second argument is used. Else it's the third.
     *
     * @param $condition
     * @param $ifTrue
     * @param $ifFalse
     * @return RedirectResponse
     */
    protected function redirectIf($condition, $ifTrue, $ifFalse)
    {
        return $this->redirect( $condition ? $ifTrue : $ifFalse );
    }

    /**
     * Translate a text using a translator.
     *
     * @param $text
     * @param array $args
     * @return mixed
     */
    protected function translate($text, $args = array())
    {
        return $this->get('translator')->trans($text, $args);
    }

    /**
     * Get the Security Object.
     * @return SecurityContext
     */
    protected function getSecurity()
    {
        return $this->get('security.context');
    }

    /**
     * Create a new e-mail.
     *
     * @param $subject
     * @param $from
     * @param $to
     * @return Swift_Message
     */
    protected function createMail($subject, $from, $to)
    {
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to);
        return $message;
    }

    /**
     * Send an email.
     * @param Swift_Message $message
     */
    protected function sendMail(Swift_Message $message)
    {
        $this->get('mailer')->send($message);
    }

}
