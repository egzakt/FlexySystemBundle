<?php

namespace Unifik\SystemBundle\Controller\Backend\Member;

use Symfony\Component\HttpFoundation\Response;

use Unifik\SystemBundle\Lib\Backend\BaseController;

/**
 * User Controller
 */
class NavigationController extends BaseController
{
    /**
     * Global Bundle Bar Action
     *
     * @param string $_masterRoute
     *
     * @return Response
     */
    public function globalModuleBarAction($_masterRoute)
    {
        $selected = (0 === strpos($_masterRoute, 'unifik_system_backend_member'));

        return $this->render('UnifikSystemBundle:Backend/Member/Navigation:global_bundle_bar.html.twig', array(
            'selected' => $selected
        ));
    }

}
