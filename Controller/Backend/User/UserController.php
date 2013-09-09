<?php

namespace Egzakt\SystemBundle\Controller\Backend\User;

use Egzakt\SystemBundle\Entity\RoleRepository;
use Egzakt\SystemBundle\Entity\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Entity\User;
use Egzakt\SystemBundle\Form\Backend\UserType;
use Egzakt\SystemBundle\Entity\Role;

/**
 * User controller.
 */
class UserController extends BaseController
{
    /**
     * @var bool
     */
    private $isDeveloper;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->roleRepository = $this->getRepository('EgzaktSystemBundle:Role');
        $this->userRepository = $this->getRepository('EgzaktSystemBundle:User');

        // Check if the current User has the privileges
        if (!$this->getSecurity()->isGranted(Role::ROLE_BACKEND_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $this->createAndPushNavigationElement('Users', 'egzakt_system_backend_user');

        // Check if the current User has the privileges
        if (!$this->getSecurity()->isGranted(Role::ROLE_BACKEND_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $this->isDeveloper = $this->getSecurity()->isGranted(Role::ROLE_DEVELOPER);
    }

    /**
     * Lists all User entities.
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

        return $this->render('EgzaktSystemBundle:Backend/User/User:list.html.twig', array('roles' => $roles));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @param integer $id The ID of the User to edit
     * @param Request $request The Request
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {
        $user = $this->getUserRepository()->findOrCreate($id);
        $this->pushNavigationElement($user);

        $form = $this->createForm(new UserType(), $user, array(
            'validation_groups' => $user->getId() ? 'edit' : 'new',
            'self_edit' => $user == $this->getUser(),
            'developer' => $this->isDeveloper
        ));

        if ($request->getMethod() == 'POST') {

            $previousEncodedPassword = $user->getPassword();

            $form->submit($request);

            if ($form->isValid()) {

                // All Users are automatically granted the ROLE_BACKEND_ACCESS Role
                $backendAccessRole = $this->getRoleRepository()->findRoleOrCreate(Role::ROLE_BACKEND_ACCESS);
                $user->addRole($backendAccessRole);

                // New password set
                $encoder = null;
                if ($form->get('password')->getData()) {
                    $encoder = $this->getPasswordEncoder($user);
                }
                $user->encodePassword($encoder, $previousEncodedPassword);

                $this->getUserRepository()->persistAndFlush($user);
                $this->setSuccessFlash($this->translate('%entity% has been updated.', array('%entity%' => $user)) );


                return $this->redirectIf(
                    $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_user'),
                    $this->generateUrl('egzakt_system_backend_user_edit', array( 'id' => $user->getId() ?: 0 ) )
                );

                /*
                return $this->redirectIf( $request->request->has('save'),
                    'egzakt_system_backend_user',
                    array('egzakt_system_backend_user_edit', array('id' => $user->getId() ?: 0))
                );
                */

            }
        }

        return $this->render('EgzaktSystemBundle:Backend/User/User:edit.html.twig', array(
            'user' => $user,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a User entity.
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUserRepository()->findOrThrow($id);
        $connectedUser = $this->getUser();

        if ($request->get('message')) {

            if ($connectedUser instanceof User && $connectedUser->equals($user) ) {
                $isDeletable = false;
                $template = $this->translate('You can\'t delete yourself.');
            } else {
                $isDeletable = $user->isDeletable();
                $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                    'entity' => $user
                ));
            }

            return new JsonResponse(
                array(
                    'template' => $template,
                    'isDeletable' => $isDeletable
                )
            );
        }

        if ($connectedUser instanceof User && !$connectedUser->equals($user) ) {

            // Call the translator before we flush the entity so we can have the real __toString()
            $this->setSuccessFlash(
                $this->translate('%entity% has been deleted.',
                    array('%entity%' => $user != '' ? $user : $user->getEntityName())
                )
            );

            $this->getUserRepository()->removeAndFlush($user);
        }

        //return $this->redirect($this->generateUrl('egzakt_system_backend_user'));
        return $this->redirectTo('egzakt_system_backend_user');
    }


    /**
     * @return RoleRepository
     */
    protected function getRoleRepository()
    {
        return $this->roleRepository;
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * @return bool
     */
    protected function isDeveloper()
    {
        return $this->isDeveloper;
    }

    protected function getPasswordEncoder($user)
    {
        return $this->getService('security.encoder_factory')->getEncoder($user);
    }

}
