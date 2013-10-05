<?php

namespace Flexy\SystemBundle\Entity;

use Flexy\DoctrineBehaviorsBundle\Model as FlexyORMBehaviors;

/**
 * RoleTranslation
 */
class RoleTranslation
{
    use FlexyORMBehaviors\Translatable\Translation;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}