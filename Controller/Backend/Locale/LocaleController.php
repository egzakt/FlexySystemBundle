<?php

namespace Egzakt\SystemBundle\Controller\Backend\Locale;

use Egzakt\SystemBundle\Entity\LocaleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Egzakt\SystemBundle\Lib\Backend\BaseController;
use Egzakt\SystemBundle\Form\Backend\LocaleType;

/**
 * Locale Controller
 */
class LocaleController extends BaseController
{

    /**
     * @var LocaleRepository
     */
    private $localeRepository;



    /**
     * Init
     */
    public function init()
    {
        parent::init();

        $this->localeRepository = $this->getRepository('EgzaktSystemBundle:Locale');
    }

    /**
     * Lists all locale entities.
     *
     * @return Response
     */
    public function listAction()
    {
        $locales = $this->getLocaleRepository()->findAll();

        return $this->render('EgzaktSystemBundle:Backend/Locale/Locale:list.html.twig', array(
            'locales' => $locales
        ));
    }

    /**
     * Displays a form to edit an existing locale entity or create a new one.
     *
     * @param integer $id The id of the Locale to edit
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {

        $locale = $this->getLocaleRepository()->findOrCreate($id);
        $form = $this->createForm(new LocaleType(), $locale);

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $this->getLocaleRepository()->persistAndFlush($locale);

                $this->setSuccessFlash(
                    $this->translate('%entity% has been updated.', array('%entity%' => $locale) )
                );

                return $this->redirectIf( $request->request->has('save'),
                    $this->generateUrl('egzakt_system_backend_locale'),
                    $this->generateUrl($locale->getRoute(), $locale->getRouteParams())
                );

            }
        }

        return $this->render('EgzaktSystemBundle:Backend/Locale/Locale:edit.html.twig', array(
            'locale' => $locale,
            'form' => $form->createView()
        ));
    }

    /**
     * Delete a Locale entity.
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
        $locale = $this->getLocaleRepository()->findOrThrow($id);

        if ($request->get('message')) {
            $template = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig', array(
                'entity' => $locale
            ));

            return new JsonResponse(array(
                'template' => $template,
                'isDeletable' => $locale->isDeletable()
                )
            );
        }

        $this->getLocaleRepository()->removeAndFlush($locale);

        // Call the translator before we flush the entity so we can have the real __toString()
        $this->setSuccessFlash(
            $this->translate('%entity% has been deleted.',
                array('%entity%' => $locale != '' ? $locale : $locale->getEntityName())
            )
        );

        return $this->redirect($this->generateUrl('egzakt_system_backend_locale'));
    }

    /**
     * @return LocaleRepository
     */
    protected function getLocaleRepository()
    {
        return $this->localeRepository;
    }

}
