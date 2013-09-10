<?php

namespace Egzakt\SystemBundle\Lib;

use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;

class SectionSluggableListener extends SluggableListener {

    /**
     * Get Sluggable Fields
     *
     * Returns the list of sluggable fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return array('name');
    }

}