<?php

namespace Egzakt\SystemBundle\Controller\Backend\User;

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
    protected $isDeveloper;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        // Check if the current User has the privileges
        if (!$this->get('security.context')->isGranted(Role::ROLE_BACKEND_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $this->createAndPushNavigationElement('Users', 'egzakt_system_backend_user');

        // Check if the current User has the privileges
        if (!$this->get('security.context')->isGranted(Role::ROLE_BACKEND_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $this->isDeveloper = $this->get('security.context')->isGranted(Role::ROLE_DEVELOPER);
    }

    /**
     * Lists all User entities.
     *
     * @return Response
     */
    public function indexAction()
    {

        $repository = $this->getRepository('EgzaktSystemBundle:Role');

        $excludedRoles = array(Role::ROLE_BACKEND_ACCESS);
        if ( !$this->isDeveloper ) {
            $excludedRoles[] = Role::ROLE_DEVELOPER;
        }

        $roles = $repository->findAllExcept($excludedRoles) ;

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
        $repository = $this->getRepository('EgzaktSystemBundle:User');
        $user = $repository->findOrCreate($id);

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
                $backendAccessRole = $repository->findRoleOrCreate(Role::ROLE_BACKEND_ACCESS);
                $user->addRole($backendAccessRole);

                // New password set
                if ($form->get('password')->getData()) {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    $encodedPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                } else {
                    $encodedPassword = $previousEncodedPassword;
                }

                $user->setPassword($encodedPassword);

                $repository->persistAndFlush($user);

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans(
                    '%entity% has been updated.',
                    array('%entity%' => $user))
                );

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_system_backend_user'));
                }

                return $this->redirect($this->generateUrl('egzakt_system_backend_user_edit', array(
                    'id' => $user->getId()
                )));
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Some fields are invalid.');
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
        $repository = $this->getRepository('EgzaktSystemBundle:User');
        $user = $repository->findOr404($id);
        $connectedUser = $this->getUser();

        if ($request->get('message')) {

            if ($connectedUser instanceof User && $connectedUser->getId() == $user->getId()) {
                $isDeletable = false;
                $template = $this->get('translator')->trans('You can\'t delete yourself.');
            } else {
                $isDeletable = $user->isDeletable();
                $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                    'entity' => $user
                ));
            }

            return new Response(json_encode(array(
                'template' => $template,
                'isDeletable' => $isDeletable
            )));
        }

        if ($connectedUser instanceof User && $connectedUser->getId() != $user->getId()) {

            // Call the translator before we flush the entity so we can have the real __toString()
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans(
                '%entity% has been deleted.',
                array('%entity%' => $user != '' ? $user : $user->getEntityName()))
            );

            $repository->removeAndFlush($user);
        }

        return $this->redirect($this->generateUrl('egzakt_system_backend_user'));
    }
}
