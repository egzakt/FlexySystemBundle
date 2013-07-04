<?php

namespace Egzakt\SystemBundle\Controller\Backend\Role;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Navigation Controller
 */
class NavigationController extends BaseController
{
    /**
     * Global Bundle Bar Action
     *
     * @param string $masterRoute
     *
     * @return Response
     */
    public function globalModuleBarAction($masterRoute)
    {
        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $this->get('security.context')->isGranted('ROLE_BACKEND_ADMIN')) {
            return new Response();
        }

        $selected = (0 === strpos($masterRoute, 'egzakt_system_backend_role'));

        return $this->render('EgzaktSystemBundle:Backend/Role/Navigation:global_bundle_bar.html.twig', array(
            'selected' => $selected
        ));
    }
}