<?php

namespace Egzakt\SystemBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * Represent an egzakt application
 */
class App
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var int $order
     */
    private $order;

    /**
     * @var integer $ordering
     */
    private $ordering;

    /**
     * @var array
     */
    private $sections;

    /**
     * @var string $prefix
     */
    private $prefix;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

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
     * @param string $name The name of the App
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
     * Set order
     *
     * @param int $order The ordering number
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get the default route to entity
     *
     * @return string
     */
    public function getRoute()
    {
        return 'EgzaktBackendCoreBundle_app';
    }

    /**
     * Get slug
     *
     * @param array $params The params of the route
     *
     * @return array
     */
    public function getRouteParams($params = array())
    {
        $defaults = array('app_slug' => $this->getSlug());
        $params = array_merge($defaults, $params);

        return $params;
    }

    /**
     * Set slug
     *
     * @param string $slug The slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering The ordering number
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    /**
     * Get ordering
     *
     * @return integer
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Add sections
     *
     * @param Section $sections The section to add
     */
    public function addSection(Section $sections)
    {
        $this->sections[] = $sections;
    }

    /**
     * Get sections
     *
     * @return Collection
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Remove sections
     *
     * @param \Egzakt\SystemBundle\Entity\Section $sections
     */
    public function removeSection(\Egzakt\SystemBundle\Entity\Section $sections)
    {
        $this->sections->removeElement($sections);
    }
}