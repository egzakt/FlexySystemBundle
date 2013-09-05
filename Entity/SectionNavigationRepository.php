<?php

namespace Egzakt\SystemBundle\Entity;

use Egzakt\SystemBundle\Lib\BaseEntityRepository;


class SectionNavigationRepository extends BaseEntityRepository
{


    public function findWith(Section $section, Navigation $navigation)
    {
        return $this->findOneBy( array(
            'section' => $section->getId(),
            'navigation' => $navigation->getId()
            )
        );
    }
}
