<?php

namespace Unifik\SystemBundle\Lib;

use Unifik\SystemBundle\Lib\BaseEntity;
use Unifik\SystemBundle\Lib\NavigationItemInterface;

class NavigationItem implements NavigationItemInterface
{
    /**
     * @var BaseEntity $entity
     */
    protected $entity;

    /**
     * @var array $children
     */
    protected $children;

    /**
     * @var mixed $parent
     */
    protected $parent;

    /**
     * @var bool $selected
     */
    protected $selected;

    /**
     * @var integer $level
     */
    protected $level;

    public function __construct()
    {
        $this->children = array();
    }

    public function __toString()
    {
        return $this->entity->__toString();
    }

    /**
     * Set Entity
     *
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get Entity
     *
     * @return BaseEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set Children
     *
     * @param $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * Get Children
     *
     * Merges the Childrens and the Section Hooks
     *
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add Children
     *
     * @param $children
     */
    public function addChildren($children)
    {
        $this->children[] = $children;
    }

    /**
     * Had Children
     *
     * @return int
     */
    public function hasChildren()
    {
        return count($this->getChildren());
    }

    /**
     * Remove Children
     *
     * @param $object
     */
    public function removeChildren($object)
    {
        foreach ($this->children as $key => $children) {
            if ($children == $object) {
                unset($this->children[$key]);
            }
        }
    }

    /**
     * Replace a child object with a new one
     *
     * @param $oldChild
     * @param $newChild
     */
    public function replaceChild($oldChild, $newChild)
    {
        foreach ($this->children as $key => $child) {
            if ($child == $oldChild) {
                $this->children[$key] = $newChild;
            }
        }
    }

    /**
     * Checks if a child objet exists in the children list
     *
     * @param $child
     *
     * @return bool
     */
    public function containsChild($child)
    {
        foreach ($this->children as $key => $existingChild) {
            if ($existingChild == $child) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set Parent
     *
     * @param $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get Parent
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set Selected
     *
     * @param boolean $selected
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    /**
     * Get Selected
     *
     * @return boolean
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Is Selected
     *
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * Get Level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set Level
     *
     * @param integer $level The Level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Gets the Route of the entity
     *
     * Backward Compatibility function, should not be used
     *
     * @param string $suffix The suffix to be concatenated after the Route
     *
     * @return string
     */
    public function getRoute($suffix = '')
    {
        return $this->entity->getRoute($suffix);
    }

    /**
     * Get the Route Params
     *
     * Backward Compatibility function, should not be used
     *
     * @param array $params Params to get
     *
     * @return array
     */
    public function getRouteParams($params = array())
    {
        return $this->entity->getRouteParams($params);
    }
}
