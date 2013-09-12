<?php

namespace Egzakt\SystemBundle\Lib;

use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;
use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListenerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SectionTranslationSluggableListener
 */
class SectionTranslationSluggableListener extends SluggableListener implements SluggableListenerInterface
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

    /**
     * Get Select Query Builder
     *
     * Returns the Select QueryBuilder that will check for a similar slug in the table
     * The slug will be valid when the Query returns 0 rows.
     *
     * @param string $slug
     * @param mixed $entity
     * @param EntityManager $em
     *
     * @return QueryBuilder
     */
    public function getSelectQueryBuilder($slug, $entity, EntityManager $em)
    {
        $translatable = $entity->getTranslatable();

        $queryBuilder = $em->createQueryBuilder()
                ->select('DISTINCT(s.slug)')
                ->from('Egzakt\SystemBundle\Entity\SectionTranslation', 's')
                ->innerJoin('s.translatable', 't')
                ->where('s.slug = :slug')
                ->andWhere('s.locale = :locale')
                ->setParameters([
                        'slug' => $slug,
                        'locale' => $entity->getLocale()
                ]);

        // On update, look for other slug, not the current entity slug
        if ($translatable->getId()) {
            $queryBuilder->andWhere('t.id <> :id')
                ->setParameter('id', $translatable->getId());
        }

        // Only look for slug on the same level
        if ($translatable->getParent()) {
            $queryBuilder->andWhere('t.parent = :parent')
                ->setParameter('parent', $translatable->getParent());
        }

        return $queryBuilder;
    }

}