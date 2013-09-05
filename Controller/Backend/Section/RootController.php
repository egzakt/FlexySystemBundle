<?php

namespace Egzakt\SystemBundle\Controller\Backend\Section;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Finder\Finder;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Form\Backend\RootSectionType;

/**
 * RootSection Controller
 */
class RootController extends BaseController
{
    /**
     * @var \Egzakt\SystemBundle\Entity\NavigationRepository
     */
    private $navigationRepository;

    /**
     * @var \Egzakt\SystemBundle\Entity\SectionRepository
     */
    private $sectionRepository;

    /**
     * @var \Egzakt\SystemBundle\Entity\AppRepository
     */
    private $appRepository;

    /**
     * @var \Egzakt\SystemBundle\Entity\SectionNavigationRepository
     */
    private $sectionNavRepository;

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->createAndPushNavigationElement('Sections', 'egzakt_system_backend_section_root', array(
            'appSlug' => $this->getApp()->getSlug()
        ));

        $this->navigationRepository = $this->getRepository('EgzaktSystemBundle:Navigation');
        $this->sectionRepository = $this->getRepository('EgzaktSystemBundle:Section');
        $this->appRepository = $this->getRepository('EgzaktSystemBundle:App');
        $this->sectionNavRepository = $this->getRepository('EgzaktSystemBundle:SectionNavigation');
    }

    /**
     * Lists all root sections by navigation
     *
     * @return Response
     */
    public function listAction()
    {
        $navigations = $this->getNavigationRepository()->findHaveSections($this->getApp());
        $sections = $this->getSectionRepository()->findRootsWithoutNavigation($this->getApp());

        return $this->render('EgzaktSystemBundle:Backend/Section/Root:list.html.twig', array(
            'navigations' => $navigations,
            'withoutNavigation' => $sections,
            'managedApp' => $this->getApp()
        ));
    }

    /**
     * Displays a form to edit an existing Section entity or create a new one.
     *
     * @param integer $id The id of the Section to edit
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {
        $entity = $this->getSectionRepository()->findOrCreate($id, $this->getApp());
        $this->pushNavigationElement($entity);

        $form = $this->createForm(new RootSectionType(), $entity, array('current_section' => $entity, 'managed_app' => $this->getApp()));

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $appBackend = $this->getAppRepository()->findOneByName('backend');
                $currentApp = $this->getApp();
                $navBar = $this->getNavigationRepository()->find(NavigationRepository::SECTION_MODULE_BAR_ID);
                $this->getSectionRepository()->mergeAndFlush($entity, $currentApp, $navBar, $appBackend);

                $this->invalidateRouter();

                $this->setSuccessFlash(
                    $this->translate('%entity% has been updated.', array('%entity%' => $entity) )
                );

                $this->redirectIf( $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_section_root', array('appSlug' => $this->getApp()->getSlug()) ),
                    $this->generateUrl('egzakt_system_backend_section_root_edit', array(
                        'id' => $entity->getId() ? : 0,
                        'appSlug' => $this->getApp()->getSlug()
                        )
                    )
                );

            } else {
                $this->setErrorFlash('Some fields are invalid.');
            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Section/Root:edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'managedApp' => $this->getApp()
        ));
    }

    /**
     * Deletes a RootSection entity.
     *
     * @param Request $request T
     *
     * @param integer $id The ID of the RootSection to delete
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

            return new JsonResponse( array(
                'template' => $template,
                'isDeletable' => $section->isDeletable()
                )
            );
        }

        $this->setSuccessFlash(
            $this->translate('%entity% has been deleted.',
                array('%entity%' => $section->getName() != '' ? $section->getName() : $section->getEntityName())
            )
        );

        $this->getSectionRepository()->removeAndFlush($section);
        $this->invalidateRouter();

        return $this->redirect($this->generateUrl('egzakt_system_backend_section_root', array('appSlug' => $this->getApp()->getSlug())));
    }

    /**
     * Set order on RootSection entities.
     *
     * @return Response
     */
    public function orderAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {

            $i = 0;
            $elements = explode(';', trim($this->getRequest()->request->get('elements'), ';'));

            // Get the navigation id
            preg_match('/_(.)*-/', $elements[0], $matches);
            $navigationId = $matches[1];
            $navigation = $this->getNavigationRepository()->getReference($navigationId);

            foreach ($elements as $element) {

                $sectionId = preg_replace('/(.)*-/', '', $element);
                $section = $this->getSectionRepository()->getReference($sectionId);

                $entity = $this->getSectionNavigationRepository()->findWith($section, $navigation);

                if ($entity) {
                    $entity->setOrdering(++$i);
                    $this->getSectionNavigationRepository()->persistAndFlush($entity);
                }
            }
            $this->invalidateRouter();

        }

        return new Response('');
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\NavigationRepository
     */
    protected function getNavigationRepository()
    {
        return $this->navigationRepository;
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\SectionRepository
     */
    protected function getSectionRepository()
    {
        return $this->sectionRepository;
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\AppRepository
     */
    protected function getAppRepository()
    {
        return $this->appRepository;
    }

    /**
     * @return \Egzakt\SystemBundle\Entity\SectionNavigationRepository
     */
    protected function getSectionNavigationRepository()
    {
        return $this->sectionNavRepository;
    }

}
