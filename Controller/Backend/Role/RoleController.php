<?php

namespace Egzakt\SystemBundle\Controller\Backend\Role;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\CrudController;
use Egzakt\SystemBundle\Entity\Role;
use Egzakt\SystemBundle\Form\Backend\RoleType;

class RoleController extends CrudController
{

    /**
     * @var bool
     */
    protected $isAdmin;

    /**
     * @var bool
     */
    protected $isDeveloper;

    /**
     * @var array
     */
    protected $rolesAdmin;

    /**
     * @inheritdoc
     */
    protected function getEntityClassname()
    {
        return 'Egzakt\\SystemBundle\\Entity\\Role';
    }

    /**
     * @inheritdoc
     */
    protected function getBaseRoute()
    {
        return 'egzkat_system_backend_role';
    }

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        // Check if the current User has the privileges
        if (!$this->get('security.context')->isGranted('ROLE_BACKEND_ADMIN')) {
            throw new AccessDeniedHttpException();
        }

        $this->createAndPushNavigationElement('Roles', 'egzakt_system_backend_role');

        // Add/remove some behaviors if Admin
        $this->isAdmin = $this->get('security.context')->isGranted('ROLE_BACKEND_ADMIN');
        $this->isDeveloper = $this->get('security.context')->isGranted('ROLE_DEVELOPER');
        $this->rolesAdmin = array('ROLE_BACKEND_ADMIN', 'ROLE_DEVELOPER');
    }

    /**
     * Lists all Role entities.
     *
     * @return Response
     */
    public function indexAction()
    {
        if (!$this->isDeveloper) {
            $roles = $this->getEm()->getRepository('EgzaktSystemBundle:Role')->findAllExcept(array('ROLE_DEVELOPER', 'ROLE_BACKEND_ACCESS'));
        } else {
            $roles = $this->getEm()->getRepository('EgzaktSystemBundle:Role')->findAllExcept('ROLE_BACKEND_ACCESS');
        }

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
    public function editAction(Request $request, $id)
    {
        $entity = $this->getEm()->getRepository('EgzaktSystemBundle:Role')->find($id);

        if (!$entity) {
            $entity = new Role();
            $entity->setContainer($this->container);
        }

        // Not editable
        if ($entity->getRole() == 'ROLE_DEVELOPER' && !$this->isDeveloper) {
            throw new NotFoundHttpException();
        }

        $this->pushNavigationElement($entity);

        $form = $this->createForm(new RoleType(), $entity, array('admin' => in_array($entity->getRole(), $this->rolesAdmin)));

        if ($request->getMethod() == 'POST') {

            $form->submit($request);

            if ($form->isValid()) {

                // Set a Rolename for this Role
                if (!in_array($entity->getRole(), $this->rolesAdmin)) {
                    $roleName = 'ROLE_BACKEND_' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $entity->getName()));
                    $entity->setRole($roleName);
                }

                $this->getEm()->persist($entity);
                $this->getEm()->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans(
                    '%entity% has been updated.',
                    array('%entity%' => $entity))
                );

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_system_backend_role'));
                }

                return $this->redirect($this->generateUrl('egzakt_system_backend_role_edit', array(
                    'id' => $entity->getId() ? : 0
                )));
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Some fields are invalid.');
            }
        }

        return $this->render(
            'EgzaktSystemBundle:Backend/Role/Role:edit.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView()
            )
        );
    }

}
