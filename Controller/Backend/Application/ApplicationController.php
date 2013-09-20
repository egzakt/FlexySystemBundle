<?php

namespace Egzakt\SystemBundle\Controller\Backend\Application;

use Egzakt\SystemBundle\Lib\Backend\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Egzakt\SystemBundle\Entity\App;
use Egzakt\SystemBundle\Entity\AppRepository;
use Egzakt\SystemBundle\Form\Backend\ApplicationType;

/**
 * Application Controller
 */
class ApplicationController extends CrudController
{
    /**
     * @var AppRepository
     */
    protected $appRepository;

    /**
     * @inheritdoc
     */
    protected function getEntityClassname()
    {
        return 'Egzakt\\SystemBundle\\Entity\\App';
    }

    /**
     * @inheritdoc
     */
    protected function getBaseRoute()
    {
        return 'egzakt_system_backend_application';
    }

    /**
     * Init
     */
    public function init()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_DEVELOPER')) {
            throw new AccessDeniedHttpException();
        }

        parent::init();

        $this->createAndPushNavigationElement('Applications', 'egzakt_system_backend_application');

        $this->appRepository = $this->getEm()->getRepository('EgzaktSystemBundle:App');
    }

    /**
     * Lists all root sections by navigation
     *
     * @return Response
     */
    public function listAction()
    {
        $applications = $this->appRepository->findAllExcept(AppRepository::BACKEND_APP_ID);

        return $this->render('EgzaktSystemBundle:Backend/Application/Application:list.html.twig', array(
            'applications' => $applications
        ));
    }

    public function editAction(Request $request, $id)
    {
        $entity = $this->appRepository->find($id);

        if (false == $entity) {
            $entity = new App();
            $entity->setContainer($this->container);
        }

        $this->pushNavigationElement($entity);

        $form = $this->createForm(new ApplicationType(), $entity);

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $this->getEm()->persist($entity);
                $this->getEm()->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans(
                    '%entity% has been updated.',
                    array('%entity%' => $entity))
                );

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_system_backend_application'));
                }

                return $this->redirect($this->generateUrl($entity->getRoute(), $entity->getRouteParams()));
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Application/Application:edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

}
