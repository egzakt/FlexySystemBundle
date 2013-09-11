<?php

namespace Egzakt\SystemBundle\Lib;

use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;
use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListenerInterface;

/**
 * Class SectionSluggableListener
 */
class SectionSluggableListener extends SluggableListener implements SluggableListenerInterface
{

    /**
     * Get Entity Name
     *
     * Returns the name of the entity having a slug field which to map the SluggableListener
     *
     * @return array
     */
    public function getEntityName()
    {
        return 'Egzakt\SystemBundle\Entity\SectionTranslation';
    }

}