<?php

namespace Flexy\SystemBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Kernel;

use Flexy\SystemBundle\Entity\Section;

/**
 * Exception Listener
 */
class ExceptionListener
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var TimedTwigEngine
     */
    private $templating;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Event handler that renders custom pages in case of a NotFoundHttpException (404)
     * or a AccessDeniedHttpException (403).
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ('dev' == $this->kernel->getEnvironment()) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {

            $name = '404 Error';
            $section = $this->entityManager->getRepository('FlexySystemBundle:Section')->findOneBy(array('name' => $this->translator->trans($name)));

            if (!$section) {
                $section = $this->createSectionEntity($name);
            }

            $this->setSection($section);

            $response = $this->templating->renderResponse('FlexySystemBundle:Frontend/Exception:404.html.twig');
            $response->setStatusCode(404);

        } elseif ($exception instanceof AccessDeniedHttpException) {

            $name = '403 Error';
            $section = $this->entityManager->getRepository('FlexySystemBundle:Section')->findOneBy(array('name' => $this->translator->trans($name)));

            if (!$section) {
                $section = $this->createSectionEntity($name);
            }

            $this->setSection($section);

            $response = $this->templating->renderResponse('FlexySystemBundle:Frontend/Exception:403.html.twig');
            $response->setStatusCode(403);
        } else {

            $name = '500 Error';
            $section = $this->entityManager->getRepository('FlexySystemBundle:Section')->findOneBy(array('name' => $this->translator->trans($name)));

            if (!$section) {
                $section = $this->createSectionEntity($name);
            }

            $this->setSection($section);

            $response = $this->templating->renderResponse('FlexySystemBundle:Frontend/Exception:500.html.twig');
            $response->setStatusCode(500);
        }

        $event->setResponse($response);
    }

    /**
     * Create a new Sectoin entity
     *
     * @param $name
     *
     * @return Section
     */
    private function createSectionEntity($name)
    {
        $locales = $this->entityManager->getRepository('FlexySystemBundle:Locale')->findAll();

        $section = new Section();
        $section->setContainer($this->container);
        $section->setApp($this->getFrontendApp());

        foreach($locales as $locale) {
            $section->setCurrentLocale($locale->getCode());
            $section->setName($this->translator->trans($name, array(), null, $locale->getCode()));
            $section->setActive(true);
        }

        $this->entityManager->persist($section);
        $this->entityManager->flush();

        $section->setCurrentLocale($this->request->getLocale());

        return $section;
    }

    /**
     * Set the Flexy Request
     *
     * @param $section
     */
    private function setFlexyRequest($section)
    {
        $app = $this->getFrontendApp();

        $flexyRequest = array(
            'sectionId' => $section->getId(),
            'appId' => $app->getId(),
            'appPrefix' => null,
            'appName' => $app->getName(),
            'appSlug' => $app->getSlug(),
            'sectionSlug' => $section->getSlug(),
            'sectionsPath' => $section->getSlug(),
            'mappedRouteName' => null
        );

        $this->request->attributes->set('_flexyEnabled', true);
        $this->request->attributes->set('_flexyRequest', $flexyRequest);
    }

    /**
     * Set the current Section in the Cores and Request
     *
     * @param $section
     */
    private function setSection($section)
    {
        $this->setFlexyRequest($section);
        $this->container->get('flexy_system.core')->setApplicationCore($this->container->get('flexy_frontend.core'));
        $this->container->get('flexy_system.core')->setCurrentAppName('frontend');
        $this->container->get('flexy_frontend.core')->addNavigationElement($section);
    }

    /**
     * Get the Frontend App
     *
     * @return \Flexy\SystemBundle\Entity\App
     */
    private function getFrontendApp()
    {
        return $this->entityManager->getRepository('FlexySystemBundle:App')->find(2);
    }

    /**
     * @param Kernel $kernel
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param TimedTwigEngine $templating
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request = null)
    {
        $this->request = $request;
    }

    /**
     * @param Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}
