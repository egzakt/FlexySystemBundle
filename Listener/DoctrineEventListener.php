<?php

namespace Egzakt\SystemBundle\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\NoResultException;

use Egzakt\SystemBundle\Lib\BaseEntity;
use Egzakt\SystemBundle\Lib\BaseTranslationEntity;
use Egzakt\SystemBundle\Lib\RouterInvalidator;

/**
 * Doctrine Event Listener
 */
class DoctrineEventListener
{
    /**
     * @var RouterInvalidator
     */
    private $routerInvalidator;

    /**
     * @var ArrayCollection
     */
    private $invalidators;

    /**
     * @param RouterInvalidator $ri
     */
    public function __construct(RouterInvalidator $ri)
    {
        $this->routerInvalidator = $ri;
        $this->invalidators = new ArrayCollection();
        $this->invalidators->add('Egzakt\\SystemBundle\\Entity\\Text');
        $this->invalidators->add('Egzakt\\SystemBundle\\Entity\\Section');
        $this->invalidators->add('Egzakt\\SystemBundle\\Entity\\App');
        $this->invalidators->add('Egzakt\\SystemBundle\\Entity\\Mapping');
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->invalidateRouter($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->invalidateRouter($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->invalidateRouter($entity);
    }

    /**
     * Pre Persist
     *
     * @param LifecycleEventArgs $args Arguments
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof BaseEntity) {

            // Set the value of the ordering field for new entities
            if (!$entity->getId() && method_exists($entity, 'getOrdering')) {
                // Only if an ordering is not set yet
                if (!$entity->getOrdering()) {
                    $repo = $entityManager->getRepository(get_class($entity));
                    $query = $repo->createQueryBuilder('o')
                        ->orderBy('o.ordering', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery();

                    try {
                        $maxOrderingEntity = $query->getSingleResult();
                        $nextOrdering = $maxOrderingEntity->getOrdering() + 1;
                    } catch (NoResultException $e) {
                        $nextOrdering = 1;
                    }

                    $entity->setOrdering($nextOrdering);
                }
            }
        }

        if ($entity instanceof BaseTranslationEntity) {
            if (method_exists($entity, 'getTranslatable')) {
                $translatable = $entity->getTranslatable();
                if ( method_exists($translatable, 'setUpdatedAt') ) {
                    $entity->getTranslatable()->setUpdatedAt(new \DateTime());
                }
            }
        }
    }

    /**
     * On Flush
     *
     * Check if a translation entity is persisted
     * If so, we update the translation's translatable entity updatedAt field with the current timestamp
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() AS $entity) {
            if ($entity instanceof BaseTranslationEntity) {
                // Translation entity has a translatable property
                if (method_exists($entity, 'getTranslatable')) {
                    // Check if the translatable entity has an updatedAt property
                    if (method_exists($entity->getTranslatable(), 'setUpdatedAt')) {
                        // Create a new changeSets : array('updatedAt' => array(oldValue, newValue))
                        $changeSets = array('updatedAt' => array($entity->getTranslatable()->getUpdatedAt(), new \DateTime()));

                        // Apply the changeSets to this entity as Extra Update because
                        // recomputing the changes on the entity will override the changes
                        // made on this entity BEFORE the onFlush method (form values)
                        $unitOfWork->scheduleExtraUpdate($entity->getTranslatable(), $changeSets);
                    }
                }
            }
        }

    }

    /**
     * Invalidate the router if the entity is in the "Invalidators" list.
     * @param $entity
     */
    protected function invalidateRouter($entity)
    {
        if ($this->invalidators->contains(get_class($entity))) {
            $this->routerInvalidator->invalidate();
        }
    }

}
