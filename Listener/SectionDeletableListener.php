<?php

namespace Flexy\SystemBundle\Listener;

use Doctrine\ORM\EntityManager;

use Flexy\SystemBundle\Lib\BaseDeletableListener;

class SectionDeletableListener extends BaseDeletableListener
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritedDoc
     */
    public function isDeletable($entity)
    {
        // Childrens
        if (count($entity->getChildren()) > 0) {
            $this->addError('This section has one or more subsections.');
        }

        // Internally tagged
        if (count($entity->getInternalTags())) {
            $this->addError('This section is protected by the system and therefore cannot be deleted.');
        }

        return $this->validate();
    }
}
