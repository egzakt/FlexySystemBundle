<?php

namespace Egzakt\SystemBundle\Entity;

use Egzakt\SystemBundle\Lib\BaseEntityRepository;

/**
 * NavigationRepository
 */
class NavigationRepository extends BaseEntityRepository
{
    const SECTION_BAR_ID = 1;
    const SECTION_MODULE_BAR_ID = 2;
    const GLOBAL_MODULE_BAR_ID = 3;
    const APP_MODULE_BAR_ID = 4;

    /**
     * @param App $app
     * @return mixed
     */
    public function findHaveSections(App $app = null)
    {
        $query = $this->createQueryBuilder('n')
            ->select('n', 'sn', 's', 'st')
            ->innerJoin('n.sectionNavigations', 'sn')
            ->innerJoin('sn.section', 's')
            ->leftJoin('s.translations', 'st')
            ->orderBy('n.id', 'ASC')
            ->addOrderBy('sn.ordering', 'ASC');

        if ( isset($app) ) {
            $query->where('n.app = :appId');
            $query->setParameter('appId', $app->getId());
        }

        return $this->processQuery($query);
    }
}