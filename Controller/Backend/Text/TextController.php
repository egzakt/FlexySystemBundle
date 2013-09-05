<?php

namespace Egzakt\SystemBundle\Controller\Backend\Text;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Form\Backend\TextMainType;
use Egzakt\SystemBundle\Form\Backend\TextStaticType;

/**
 * Text controller.
 *
 * @throws NotFoundHttpException
 */
class TextController extends BaseController
{

    /**
     * @var \Egzakt\SystemBundle\Entity\TextRepository
     */
    private $textRepository;
    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->textRepository = $this->getRepository('EgzaktSystemBundle:Text');
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

        $mainEntities = $this->getTextRepository()->findNonStaticBySection($section);
        $staticEntities = $this->getTextRepository()->findStaticBySection($section);

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
     * @param integer $id The ID
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $section = $this->getSection();

        $text = $this->getTextRepository()->findOrCreate($id, $section);

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

                $this->getTextRepository()->persistAndFlush($text);
                $this->invalidateRouter();
                $this->setSuccessFlash('The text has been updated.');

                $this->redirectIf(
                    $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_text'),
                    $this->generateUrl('egzakt_system_backend_text_edit', array( 'id' => $text->getId() ?: 0 ) )
                );

            } else {
                $this->setErrorFlash('Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Text/Text:edit.html.twig', array(
            'text' => $text,
            'form' => $form->createView(),
        ));
    }

    /**
     * Delete a Text entity.
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse|Response
     *
     * @throws NotFoundHttpException
     */
    public function deleteAction(Request $request, $id)
    {
        $text = $this->getTextRepository()->findOrThrow($id);

        if ($request->get('message')) {
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $text
            ));

            return $this->sendJson(
                array(
                    'template' => $template,
                    'isDeletable' => $text->isDeletable()
                )
            );
        }

        $this->getTextRepository()->removeAndFlush($text);
        $this->setSuccessFlash('The text has been deleted.');
        $this->invalidateRouter();

        return $this->redirect($this->generateUrl('egzakt_system_backend_text'));
    }


    /**
     * Set order on a Text entity.
     *
     * @return Response
     */
    public function orderAction()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {

            $i = 0;
            $elements = explode(';', trim($this->getRequest()->request->get('elements'), ';'));

            foreach ($elements as $element) {

                $element = explode('_', $element);
                $entity = $this->getTextRepository()->find($element[1]);

                if ($entity) {
                    $entity->setOrdering(++$i);
                    $this->getTextRepository()->persistAndFlush($entity);
                }

            }

            $this->invalidateRouter();
        }

        return $this->send();
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\TextRepository
     */
    protected function getTextRepository()
    {
        return $this->textRepository;
    }


}
