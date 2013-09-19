<?php

namespace Egzakt\SystemBundle\Controller\Backend\Member;

use Egzakt\SystemBundle\Lib\Backend\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Egzakt\SystemBundle\Entity\Member;
use Egzakt\SystemBundle\Form\Backend\MemberType;

/**
 * Member Controller
 */
class MemberController extends CrudController
{
    /**
     * Init
     */
    public function init()
    {
        parent::init();

//        $this->getCore()->addNavigationElement($this->getSectionBundle());
    }

    protected function getEntityClassname()
    {
        return 'Egzakt\\SystemBundle\\Entity\\Member';
    }

    /**
     * Lists all member entities.
     *
     * @return Response
     */
    public function listAction()
    {
        $members = $this->getEm()->getRepository('EgzaktSystemBundle:Member')->findAll();

        return $this->render('EgzaktSystemBundle:Backend/Member/Member:list.html.twig', array(
            'members' => $members
        ));
    }

    /**
     * Displays a form to edit an existing member entity or create a new one.
     *
     * @param integer $id      The id of the Member to edit
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {
        /**
         * @var $member Member
         */
        $member = $this->getEm()->getRepository('EgzaktSystemBundle:Member')->find($id);

        if (false == $member) {
            $member = new Member();
            $member->setContainer($this->container);
        }

        $form = $this->createForm(new MemberType(), $member);

        if ('POST' == $request->getMethod()) {

            $previousEncodedPassword = $member->getPassword();

            $form->bindRequest($request);

            if ($form->isValid()) {

//                // New password set
//                if ($form->get('password')->getData()) {
//                    $encoder = $this->get('security.encoder_factory')->getEncoder($member);
//                    $encodedPassword = $encoder->encodePassword($member->getPassword(), $member->getSalt());
//                } else {
//                    $encodedPassword = $previousEncodedPassword;
//                }
//
//                $member->setPassword($encodedPassword);

                $this->getEm()->persist($member);
                $this->getEm()->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans(
                    '%entity% has been updated.',
                    array('%entity%' => $member))
                );

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_system_backend_member'));
                }

                return $this->redirect($this->generateUrl($member->getRoute(), $member->getRouteParams()));
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Member/Member:edit.html.twig', array(
            'member' => $member,
            'form' => $form->createView()
        ));
    }


}
