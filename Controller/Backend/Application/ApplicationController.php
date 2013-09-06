<?php

namespace Egzakt\SystemBundle\Controller\Backend\Application;

use Egzakt\SystemBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Finder\Finder;

use Egzakt\SystemBundle\Entity\AppRepository;
use Egzakt\SystemBundle\Form\Backend\ApplicationType;
use Egzakt\SystemBundle\Lib\Backend\BaseController;

/**
 * Application Controller
 */
class ApplicationController extends BaseController
{
    /**
     * @var AppRepository
     */
    protected $appRepository;

    /**
     * Init
     */
    public function init()
    {
        if (false === $this->getSecurity()->isGranted(Role::ROLE_DEVELOPER)) {
            throw new AccessDeniedHttpException();
        }

        parent::init();

        $this->createAndPushNavigationElement('Applications', 'egzakt_system_backend_application');

        $this->appRepository = $this->getRepository('EgzaktSystemBundle:App');
    }

    /**
     * Lists all root sections by navigation
     *
     * @return Response
     */
    public function listAction()
    {
        $applications = $this->getAppRepository()->findAllExcept(AppRepository::BACKEND_APP_ID);

        return $this->render('EgzaktSystemBundle:Backend/Application/Application:list.html.twig', array(
            'applications' => $applications
        ));
    }

    /**
     *
     *
     * @param integer $applicationId
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction($applicationId, Request $request)
    {
        $entity = $this->getAppRepository()->findOrCreate($applicationId);
        $this->pushNavigationElement($entity);

        $form = $this->createForm(new ApplicationType(), $entity);

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $this->getAppRepository()->persistAndFlush($entity);
                $this->invalidateRouter();

                $this->setSuccessFlash(
                    $this->translate('%entity% has been updated.', array('%entity%' => $entity) )
                );

                return $this->redirectIf( $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_application'),
                    $this->generateUrl($entity->getRoute(), $entity->getRouteParams())
                );
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Application/Application:edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a App entity.
     *
     * @param Request $request
     * @param integer $applicationId
     *
     * @throws NotFoundHttpException
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction(Request $request, $applicationId)
    {
        $application = $this->getAppRepository()->findOrThrow($applicationId);

        if ($request->get('message')) {
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $application
            ));

            return new JsonResponse(
                array(
                'template' => $template,
                'isDeletable' => $application->isDeletable()
                )
            );
        }

        $this->getAppRepository()->removeAndFlush($application);
        $this->invalidateRouter();

        $this->setSuccessFlash(
            $this->translate('%entity has been deleted.', array('%entity%' => $application) )
        );

        return $this->redirect($this->generateUrl('egzakt_system_backend_application'));
    }

    /**
     * @return AppRepository
     */
    protected function getAppRepository()
    {
        return $this->appRepository;
    }

}
