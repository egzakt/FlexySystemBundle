<?php

namespace Egzakt\SystemBundle\Entity;

use Symfony\Component\Validator\ExecutionContextInterface;

use Doctrine\ORM\Mapping as ORM;
use Egzakt\DoctrineBehaviorsBundle\Model as EgzaktORMBehaviors;

/**
 * TextTranslation
 */
class TextTranslation
{
    use EgzaktORMBehaviors\Translatable\Translation;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $text
     */
    protected $text;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $anchor
     */
    protected $anchor;

    /**
     * @var boolean $active
     */
    protected $active;

    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
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

    /**
     * Set anchor
     *
     * @param string $anchor
     */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    /**
     * Get anchor
     *
     * @return string
     */
    public function getAnchor()
    {
        return $this->anchor;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Validate the sub-fields of a collapsable text
     *
     * @param ExecutionContextInterface $context The Execution Context
     */
    public function isCollapsableValid(ExecutionContextInterface $context)
    {
        if ($this->translatable->getCollapsable() && false == $this->getName()) {
            $context->addViolationAt('name', 'A collapsable text must have a name');
        }
    }
}
