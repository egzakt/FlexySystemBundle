<?php

namespace Egzakt\SystemBundle\Generator;

use \Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * EntityTranslation Generator
 */
class EntityTranslationGenerator extends \Doctrine\ORM\Tools\EntityGenerator
{

    /**
     * Generate EntityTranslation Class
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata Metadata Info
     *
     * @return string
     */
    public function generateEntityClass(ClassMetadataInfo $metadata, array $fields = array())
    {
        parent::setFieldVisibility('protected');

        return parent::generateEntityClass($metadata);
    }
}