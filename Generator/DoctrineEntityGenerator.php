<?php

namespace Unifik\SystemBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Unifik\SystemBundle\Generator\Tools\Export\ClassMetadataExporter;

/**
 * Unifik Backend Doctrine Entity Generator
 */
class DoctrineEntityGenerator extends \Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator
{

    private $filesystem;
    private $registry;

    /**
     * __construct
     *
     * @param Filesystem        $filesystem
     * @param RegistryInterface $registry
     */
    public function __construct(Filesystem $filesystem, RegistryInterface $registry)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
    }

    public function __generate(BundleInterface $bundle, $entity, $format, array $fields, $withRepository, $timestampable)
    {
        // configure the bundle (needed if the bundle does not contain any Entities yet)
        $config = $this->registry->getManager(null)->getConfiguration();
        $config->setEntityNamespaces(array_merge(
            array($bundle->getName() => $bundle->getNamespace().'\\Entity'),
            $config->getEntityNamespaces()
        ));

        // Rebuild fields based on the i18n attribute
        $entityFields = array();
        $entityTranslationFields = array();
        $isSluggable = false;
        $isI18nSluggable = false;
        foreach ($fields as $field) {
            if (substr(strtolower($field['i18n']), 0, 1) == 'y') { // Simulate all Yes combinations
                $push = true;
                unset($field['i18n']);

                // Slug field won't be added to the ClassMetadata, it will be done with the Sluggable Trait
                if ($field['fieldName'] == 'slug') {
                    $isI18nSluggable = true;
                    $push = false;
                }

                if ($push) {
                    array_push($entityTranslationFields, $field);
                }
            } else {
                $push = true;
                unset($field['i18n']);

                // Slug field won't be added to the ClassMetadata, it will be done with the Sluggable Trait
                if ($field['fieldName'] == 'slug') {
                    $isSluggable = true;
                    $push = false;
                }

                if ($push) {
                    array_push($entityFields, $field);
                }
            }
        }
        $hasI18n = count($entityTranslationFields) > 0 ? true : false;

        $this->generateEntity($bundle, $entity, $format, $entityFields, $fields, $withRepository, $hasI18n, $timestampable, $isSluggable);
        if ($hasI18n) {
            $this->generateEntityTranslation($bundle, $entity, $format, $entityTranslationFields, $fields, $isI18nSluggable);
        }

        // Entity not nullable fields? Add it to the validation.yml file
        $notNullableFields = [];
        foreach($entityFields as $field) {
            if (isset($field['nullable']) && !$field['nullable']) {
                $notNullableFields[] = $field['fieldName'];
            }
        }

        if ($notNullableFields) {
            $this->addEntityValidationConstraint($bundle->getNamespace().'\\Entity\\' . $entity, $notNullableFields, $format, $bundle);
        }

        // Entity not nullable fields? Add it to the validation.yml file
        $notNullableFields = [];
        foreach($entityTranslationFields as $field) {
            if (isset($field['nullable']) && !$field['nullable']) {
                $notNullableFields[] = $field['fieldName'];
            }
        }

        if ($notNullableFields) {
            $this->addEntityValidationConstraint($bundle->getNamespace().'\\Entity\\' . $entity . 'Translation', $notNullableFields, $format, $bundle);
        }
    }

    /**
     * Add a validation constraint to the validation.yml file
     *
     * @param $entityNamespace
     * @param $fields
     * @param $format
     * @param $bundle
     *
     * @return boolean
     */
    protected function addEntityValidationConstraint($entityNamespace, $fields, $format, $bundle)
    {
        if (!in_array($format, ['yml', 'xml'])) {
            return false;
        }

        $target = sprintf(
            '%s/Resources/config/validation.%s',
            $bundle->getPath(),
            $format
        );

        // For XML
        $renderHeader = true;

        $current = '';
        if (file_exists($target)) {
            $current = file_get_contents($target);

            // Don't add the same entity twice
            if (false !== strpos($current, $entityNamespace . ':')) {
                return false;
            }

            // for XML
            if (false !== strpos($current, 'constraint-mapping')) {
                $renderHeader = false;
            }
        }

        $content = $this->render(sprintf('entity/validation.%s.twig', $format), array(
            'render_header' => $renderHeader,
            'namespace' => $entityNamespace,
            'fields' => $fields
        ));

        $content = $current . $content;

        if (false === file_put_contents($target, $content)) {
            return false;
        }

        return true;
    }

    public function generateEntity(BundleInterface $bundle, $entity, $format, array $entityFields, array $fields, $withRepository, $hasI18n, $isTimestampable, $isSluggable)
    {
        $entityClass = $this->registry->getAliasNamespace($bundle->getName()).'\\'.$entity;
        $entityPath = $bundle->getPath().'/Entity/'.str_replace('\\', '/', $entity).'.php';
        if (file_exists($entityPath)) {
            throw new \RuntimeException(sprintf('Entity "%s" already exists.', $entityClass));
        }

        $class = new ClassMetadataInfo($entityClass);
        if ($withRepository) {
            $class->customRepositoryClassName = $entityClass.'Repository';
        }
        $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
        $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);

        if (!$hasI18n) {
            $class->mapField(array('fieldName' => 'active', 'type' => 'boolean', 'nullable' => true));
        }

        foreach ($entityFields as $field) {
            $class->mapField($field);
        }

        $entityGenerator = $this->getEntityGenerator($bundle);
        if ('annotation' === $format) {
            $entityGenerator->setGenerateAnnotations(true);
            $entityGenerator->generateEntityClass($class, $bundle, $entity, $fields, $hasI18n, $isTimestampable, $isSluggable);
            $mappingPath = $mappingCode = false;
        } else {
            $cme = new ClassMetadataExporter();
            $exporter = $cme->getExporter('yml' == $format ? 'yaml' : $format);
            $mappingPath = $bundle->getPath().'/Resources/config/doctrine/'.str_replace('\\', '.', $entity).'.orm.'.$format;

            if (file_exists($mappingPath)) {
                throw new \RuntimeException(sprintf('Cannot generate entity when mapping "%s" already exists.', $mappingPath));
            }

            $mappingCode = $exporter->exportClassMetadata($class, 2);
            $entityGenerator->setGenerateAnnotations(false);
            $entityGenerator->generateEntityClass($class, $bundle, $entity, $fields, $hasI18n, $isTimestampable, $isSluggable);
        }

        if ($mappingPath) {
            $this->filesystem->mkdir(dirname($mappingPath));
            file_put_contents($mappingPath, $mappingCode);
        }

        if ($withRepository) {
            $path = $bundle->getPath().str_repeat('/..', substr_count(get_class($bundle), '\\'));
            $this->getRepositoryGenerator($hasI18n)->writeEntityRepositoryClass($class->customRepositoryClassName, $path);
        }
    }

    public function generateEntityTranslation(BundleInterface $bundle, $entity, $format, array $entityFields, array $fields, $isSluggable)
    {
        $entityTranslation = $entity . 'Translation';

        $entityClass = $this->registry->getAliasNamespace($bundle->getName()).'\\'.$entityTranslation;
        $entityPath = $bundle->getPath().'/Entity/'.str_replace('\\', '/', $entityTranslation).'.php';
        if (file_exists($entityPath)) {
            throw new \RuntimeException(sprintf('Entity "%s" already exists.', $entityClass));
        }

        $class = new ClassMetadataInfo($entityClass);
        $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
        $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);

        foreach ($entityFields as $field) {
            $class->mapField($field);
        }

        $class->mapField(array('fieldName' => 'active', 'type' => 'boolean', 'nullable' => true));

        $entityGenerator = $this->getEntityTranslationGenerator();
        if ('annotation' === $format) {
            $entityGenerator->setGenerateAnnotations(true);
            $entityGenerator->generateEntityClass($class, $bundle, $entityTranslation, $fields, $isSluggable);
            $mappingPath = $mappingCode = false;
        } else {
            $cme = new ClassMetadataExporter();
            $exporter = $cme->getExporter('yml' == $format ? 'yaml' : $format);
            $mappingPath = $bundle->getPath().'/Resources/config/doctrine/'.str_replace('\\', '.', $entityTranslation).'.orm.'.$format;

            if (file_exists($mappingPath)) {
                throw new \RuntimeException(sprintf('Cannot generate entity when mapping "%s" already exists.', $mappingPath));
            }

            $mappingCode = $exporter->exportClassMetadata($class, 2);
            $entityGenerator->setGenerateAnnotations(false);
            $entityGenerator->generateEntityClass($class, $bundle, $entityTranslation, $fields, $isSluggable);
        }

        if ($mappingPath) {
            $this->filesystem->mkdir(dirname($mappingPath));
            file_put_contents($mappingPath, $mappingCode);
        }
    }

    public function isReservedKeyword($keyword)
    {
        return $this->registry->getConnection()->getDatabasePlatform()->getReservedKeywordsList()->isKeyword($keyword);
    }

    protected function getSkeletonDirs()
    {
        $skeletonDirs = array();

        $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
        $skeletonDirs[] = __DIR__ . '/../Resources';

        return $skeletonDirs;
    }

    /**
     * Get Entity Generator
     *
     * @param BundleInterface $bundle
     *
     * @return \Unifik\SystemBundle\Generator\EntityGenerator
     */
    protected function getEntityGenerator(BundleInterface $bundle = null)
    {
        $entityGenerator = new \Unifik\SystemBundle\Generator\EntityGenerator();
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setSkeletonDirs($this->getSkeletonDirs());

        return $entityGenerator;
    }

    /**
     * Get EntityTranslation Generator
     *
     * @return \Unifik\SystemBundle\Generator\EntityTranslationGenerator
     */
    protected function getEntityTranslationGenerator()
    {
        $entityGenerator = new \Unifik\SystemBundle\Generator\EntityTranslationGenerator();
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setSkeletonDirs($this->getSkeletonDirs());

        return $entityGenerator;
    }

    /**
     * Get Repository Generator
     *
     * @param bool $hasI18n
     *
     * @return \Unifik\SystemBundle\Generator\EntityRepositoryGenerator
     */
    protected function getRepositoryGenerator($hasI18n = false)
    {
        $entityRepositoryGenerator = new \Unifik\SystemBundle\Generator\EntityRepositoryGenerator();
        $entityRepositoryGenerator->setSkeletonDirs($this->getSkeletonDirs());
        $entityRepositoryGenerator->setHasI18n($hasI18n);

        return $entityRepositoryGenerator;
    }
}
