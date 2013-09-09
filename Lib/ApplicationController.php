<?php

namespace Egzakt\SystemBundle\Lib;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Egzakt\SystemBundle\Lib\Core;
use Egzakt\SystemBundle\Entity\Section;

abstract class ApplicationController extends Controller implements BaseControllerInterface
{

    /**
     * Called from KernelListener
     */
    public function init()
    {

    }

    /**
     * @deprecated Use getService()
     * @param string $id
     * @return object
     */
    public function get($id)
    {
        return parent::get($id);
    }

    /**
     * @param $id
     * @return object
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return Core
     */
    abstract protected function getCore();

    /**
     * @return SystemCore
     */
    protected function getSystemCore()
    {
        return $this->getService('egzakt_system.core');
    }

    /**
     * Get the current section entity
     *
     * @return Section
     */
    public function getSection()
    {
        return $this->getCore()->getSection();
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
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this->getService('session')->getFlashBag()->add($type, $message);
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
     * Has flash messages for a given type?
     *
     * @param string $type
     * @return boolean
     */
    protected function hasFlash($type) {
        return $this->getService('session')->getFlashBag()->has($type);
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
     * Translate a text using a translator.
     *
     * @param $text
     * @param array $args
     * @return mixed
     */
    protected function translate($text, $args = array())
    {
        return $this->getService('translator')->trans($text, $args);
    }

    /**
     * Get the Security Object.
     * @return SecurityContext
     */
    protected function getSecurity()
    {
        return $this->getService('security.context');
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
        $this->getService('mailer')->send($message);
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

}