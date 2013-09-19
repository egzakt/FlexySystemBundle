<?php

namespace Egzakt\SystemBundle\Lib\Backend;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class CrudController extends BaseController
{

    abstract protected function getEntityClassname();

    public function deleteAction(Request $request, $id)
    {
        $deleteService = $this->get('egzakt_system.deletable');

        $entity = $this->getEm()->getRepository($this->getEntityClassname())->find($id);
        if (null === $entity) {
            $this->createNotFoundException('Entity not found : '.$this->getEntityClassname().' with ID : '.$id);
        }

        if ($request->isXMLHttpRequest()) {
            $result = $deleteService->checkDeletable($entity);
            $output = $result->toArray();
            $output['template'] = $this->renderView('EgzaktSystemBundle:Backend/Core:delete_message.html.twig',
                array(
                    'entity' => $entity,
                    'result' => $result
                )
            );

            return new JsonResponse($output);
        }

        $routeService = $this->get('egzakt_system.entity_route');
        $mapping = $routeService->get($this->getAppName(), $this->getentityClassname());
        $baseRoute = $mapping->getRoute();

        $result = $deleteService->checkDeletable($entity);
        if ($result->isSuccess()) {
            $this->getEm()->remove($entity);
            $this->getEm()->flush();

            $this->addFlash('success', $this->get('translator')->trans(
                '%entity% has been deleted.',
                array('%entity%' => $entity)
            ));

            return $this->redirect($this->generateUrl($baseRoute));
        }

        return $this->redirect($this->generateUrl($baseRoute));

    }


    public function orderAction(Request $request)
    {

        $i = 0;
        $elements = explode(';', trim($this->getRequest()->get('elements'), ';'));

        foreach ($elements as $element) {

            $element = explode('_', $element);
            $entity = $this->getEm()->getRepository($this->getEntityClassName())->find($element[1]);

            if ($entity) {
                $entity->setOrdering(++$i);
                $this->getEm()->persist($entity);
            }

            $this->getEm()->flush();
        }

        return new JsonResponse('');

    }
}