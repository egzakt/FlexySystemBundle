<?php

namespace Egzakt\SystemBundle\Controller\Backend\Role;

use Egzakt\SystemBundle\Entity\RoleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Entity\Role;
use Egzakt\SystemBundle\Form\Backend\RoleType;
use Egzakt\SystemBundle\Lib\NavigationElement;

/**
 * Role Controller.
 */
class RoleController extends BaseController
{

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @var bool
     */
    private $isDeveloper;

    /**
     * @var array
     */
    private $rolesAdmin;

    /**
     * @var RoleRepository;
     */
    private $roleRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        // Check if the current User has the privileges
        if (!$this->getSecurity()->isGranted(Role::ROLE_BACKEND_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $this->createAndPushNavigationElement('Roles', 'egzakt_system_backend_role');

        // Add/remove some behaviors if Admin
        $this->isAdmin = $this->getSecurity()->isGranted(Role::ROLE_BACKEND_ADMIN);
        $this->isDeveloper = $this->getSecurity()->isGranted(Role::ROLE_DEVELOPER);
        $this->rolesAdmin = array(Role::ROLE_BACKEND_ADMIN, Role::ROLE_DEVELOPER);

        $this->roleRepository = $this->getRepository('EgzaktSystemBundle:Role');

    }

    /**
     * Lists all Role entities.
     *
     * @return Response
     */
    public function indexAction()
    {

        $excludedRoles = array(Role::ROLE_BACKEND_ACCESS);
        if ( !$this->isDeveloper() ) {
            $excludedRoles[] = Role::ROLE_DEVELOPER;
        }

        $roles = $this->getRoleRepository()->findAllExcept($excludedRoles);

        return $this->render('EgzaktSystemBundle:Backend/Role/Role:list.html.twig', array('roles' => $roles));
    }

    /**
     * Displays a form to edit an existing Role entity.
     *
     * @param $id
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id, Request $request)
    {
        $entity = $this->getRoleRepository()->findOrCreate($id);

        if ( $entity->hasCode(Role::ROLE_DEVELOPER) && !$this->isDeveloper() ) {
            throw new NotFoundHttpException();
        }

        $this->pushNavigationElement($entity);

        $form = $this->createForm( new RoleType(), $entity, array('admin' => $entity->hasOneCode($this->rolesAdmin)) );

        if ($request->getMethod() == 'POST') {

            $form->submit($request);

            if ($form->isValid()) {

                if ( !$entity->hasOneCode($this->rolesAdmin) ) {
                    $entity->setBackendCode();
                }

                $this->getRoleRepository()->persistAndFlush($entity);

                $this->setSuccessFlash(
                    $this->translate('%entity% has been updated.', array('%entity%' => $entity) )
                );

                return $this->redirectIf(
                    $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_role'),
                    $this->generateUrl('egzakt_system_backend_role_edit', array(
                        'id' => $entity->getId() ? : 0
                        )
                    )
                );

            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Role/Role:edit.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView()
            )
        );
    }

    /**
     * Deletes a Role entity
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @throws \Exception
     */
    public function deleteAction($id)
    {
        $role = $this->getRoleRepository()->findOrThrow($id);

        if ($this->get('request')->get('message')) {

            $isDeletable = $role->isDeletable();
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $role
            ));

            return new JsonResponse( array(
                'template' => $template,
                'isDeletable' => $isDeletable
                )
            );
        }

        // Don't delete some roles
        if (!$role->isDeletable()) {
            throw new \Exception('You can\'t delete this role.');
        }

        $this->getRoleRepository()->removeAndFlush($role);

        $this->setSuccessFlash(
            $this->translate('%entity% has been deleted.', array('%entity%' => $role) )
        );

        return $this->redirect($this->generateUrl('egzakt_system_backend_role'));
    }

    /**
     * @return RoleRepository
     */
    protected function getRoleRepository()
    {
        return $this->roleRepository;
    }

    /**
     * @return bool
     */
    protected function isDeveloper()
    {
        return $this->isDeveloper;
    }

}
