<?php

namespace Egzakt\SystemBundle\Controller\Backend\Section;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Entity\Mapping;
use Egzakt\SystemBundle\Form\Backend\SectionType;

/**
 * Section controller.
 *
 */
class SectionController extends BaseController
{
    /**
     * @var \Egzakt\SystemBundle\Entity\SectionRepository
     */
    private $sectionRepository;

    /**
     * @var \Egzakt\SystemBundle\Entity\NavigationRepository
     */
    private $navigationRepository;

    /**
     * @var \Egzakt\SystemBundle\Entity\AppRepository
     */
    private $appRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->sectionRepository = $this->getRepository('EgzaktSystemBundle:Section');
        $this->navigationRepository = $this->getRepository('EgzaktSystemBundle:Navigation');
        $this->appRepository = $this->getRepository('EgzaktSystemBundle:App');
    }


    /**
     * Lists all Section entities.
     *
     * @return Response
     */
    public function indexAction()
    {
        $entities = $this->getSectionRepository()->findByParent($this->getSection());

        return $this->render('EgzaktSystemBundle:Backend/Section/Section:list.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Displays a form to edit an existing Text entity.
     *
     * @param Request $request
     * @param integer $id The Section ID
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, $id)
    {
        $section = $this->getSection();
        $app = $this->getApp();

        $entity = $this->getSectionRepository()->findOrCreate($id, $app, $section);
        $this->getCore()->addNavigationElement($entity);
        $form = $this->createForm(new SectionType(), $entity, array('current_section' => $entity, 'managed_app' => $this->getApp()));

        if ('POST' === $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $appBackend = $this->getAppRepository()->findOneByName('backend');
                $currentApp = $this->getApp();
                $navBar = $this->getNavigationRepository()->find(NavigationRepository::SECTION_MODULE_BAR_ID);
                $this->getSectionRepository()->mergeAndFlush($entity, $currentApp, $navBar, $appBackend);

                $this->invalidateRouter();
                $this->setSuccessFlash(
                    $this->translate(
                        '%entity% has been updated.',
                        array('%entity%' => $entity)
                    )
                );

                $this->redirectIf(
                    $request->get('save'),
                    $this->generateUrl('egzakt_system_backend_section'),
                    $this->generateUrl('egzakt_system_backend_section_edit', array(
                            'id' => $entity->getId() ?: 0
                        )
                    )
                );

            } else {
                $this->setErrorFlash('Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Section/Section:edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a Section entity.
     *
     * @param Request $request
     * @param integer $id The ID of the Section to delete
     *
     * @throws NotFoundHttpException
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $section = $this->getSectionRepository()->findOrThrow($id);

        if ($request->get('message')) {
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $section
            ));

            return new JsonResponse(
                array(
                    'template' => $template,
                    'isDeletable' => $section->isDeletable()
                )
            );
        }

        // Call the translator before we flush the entity so we can have the real __toString()
        $this->setSuccessFlash(
            $this->translate('%entity% has been deleted.', array(
                '%entity%' => $section->getName() != '' ? $section->getName() : $section->getEntityName()
                )
            )
        );

        $this->getSectionRepository()->removeAndFlush($section);
        $this->invalidateRoute();

        return $this->redirect($this->generateUrl('egzakt_system_backend_section'));
    }


    /**
     * Set order on a Section entity.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function orderAction(Request $request)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {

            $i = 0;
            $elements = explode(';', trim($request->get('elements'), ';'));

            foreach ($elements as $element) {

                $element = explode('_', $element);
                $entity = $this->getSectionRepository()->find($element[1]);

                if ($entity) {
                    $entity->setOrdering(++$i);
                    $this->getSectionRepository->persistAndFlush($entity);
                }

            }

            $this->invalidateRouter();
        }

        return new Response('');
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\SectionRepository
     */
    protected function getSectionRepository()
    {
        return $this->sectionRepository;
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\NavigationRepository
     */
    protected function getNavigationRepository()
    {
        return $this->navigationRepository;
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\AppRepository
     */
    protected function getAppRepository()
    {
        return $this->appRepository;
    }

}
