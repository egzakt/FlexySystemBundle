<?php

namespace Egzakt\SystemBundle\Controller\Backend\Text;

use Egzakt\SystemBundle\Entity\Text;
use Egzakt\SystemBundle\Form\Backend\TextMainType;
use Egzakt\SystemBundle\Form\Backend\TextStaticType;
use Egzakt\SystemBundle\Lib\Backend\CrudController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TextController extends CrudController
{

    /**
     * @inheritdoc
     */
    protected function getEntityClassname()
    {
        return 'Egzakt\\SystemBundle\\Entity\\Text';
    }

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->createAndPushNavigationElement('Text list', 'egzakt_system_backend_text');
    }

    /**
     * Lists all Text entities.
     *
     * @return Response
     */
    public function indexAction()
    {
        $section = $this->getSection();

        $mainEntities = $this->getEm()->getRepository('EgzaktSystemBundle:Text')->findBy(array(
            'section' => $section->getId(),
            'static' => false
        ), array(
            'ordering' => 'ASC'
        ));

        $staticEntities = $this->getEm()->getRepository('EgzaktSystemBundle:Text')->findBy(array(
            'section' => $section->getId(),
            'static' => true
        ), array(
            'ordering' => 'ASC'
        ));

        return $this->render('EgzaktSystemBundle:Backend/Text/Text:list.html.twig', array(
            'mainEntities' => $mainEntities,
            'staticEntities' => $staticEntities,
            'truncateLength' => 100
        ));
    }

    /**
     * Displays a form to edit or create a Text entity.
     *
     * @param Request $request
     * @param integer $id      The ID
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $section = $this->getSection();

        $text = $this->getEm()->getRepository('EgzaktSystemBundle:Text')->find($id);

        if (false == $text) {
            $text = new Text();
            $text->setContainer($this->container);
            $text->setSection($section);
        }

        $this->getCore()->addNavigationElement($text);

        if ($text->isStatic()) {
            $formType = new TextStaticType();
        } else {
            $formType = new TextMainType();
        }

        $form = $this->createForm($formType, $text);

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $em = $this->getEm();
                $em->persist($text);
                $em->flush();

                $this->get('egzakt_system.router_invalidator')->invalidate();

                $this->get('session')->getFlashBag()->add('success', 'The text has been updated.');

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('egzakt_system_backend_text'));
                }

                return $this->redirect($this->generateUrl('egzakt_system_backend_text_edit', array(
                    'id' => $text->getId() ?: 0
                )));
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Text/Text:edit.html.twig', array(
            'text' => $text,
            'form' => $form->createView(),
        ));
    }


}
