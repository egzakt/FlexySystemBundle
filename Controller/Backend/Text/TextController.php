<?php

namespace Egzakt\SystemBundle\Controller\Backend\Text;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Entity\Text;
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
        $repository = $this->getRepository('EgzaktSystemBundle:Text');
        $section = $this->getSection();

        $mainEntities = $repository->findNonStaticBySection($section);
        $staticEntities = $repository->findStaticBySection($section);

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
        $repository = $this->getRepository('EgzaktSystemBundle:Text');
        $section = $this->getSection();

        $text = $repository->findOrCreate($id, $section);

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

                $repository->persistAndFlush($text);
                $this->invalidateRouter();
                $this->addFlash('success', 'The text has been updated.');

                $this->redirectIf(
                    $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_text'),
                    $this->generateUrl('egzakt_system_backend_text_edit', array( 'id' => $text->getId() ?: 0 ) )
                );

            } else {
                $this->addFlash('error', 'Some fields are invalid.');
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
        $repository = $this->getRepository('EgzaktSystemBundle:Text');
        $text = $repository->findOrThrow($id);

        if ($request->get('message')) {
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $text
            ));

            return new JsonResponse(
                array(
                    'template' => $template,
                    'isDeletable' => $text->isDeletable()
                )
            );
        }

        $repository->removeAndFlush($text);
        $this->addFlash('success', 'The text has been deleted.');
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

        $repository = $this->getRepository('EgzaktSystemBundle:Text');

        if ($this->getRequest()->isXmlHttpRequest()) {

            $i = 0;
            $elements = explode(';', trim($this->getRequest()->request->get('elements'), ';'));

            foreach ($elements as $element) {

                $element = explode('_', $element);
                $entity = $repository->find($element[1]);

                if ($entity) {
                    $entity->setOrdering(++$i);
                    $repository->persistAndFlush($entity);
                }

            }

            $this->invalidateRouter();
        }

        return new Response('');
    }

}
