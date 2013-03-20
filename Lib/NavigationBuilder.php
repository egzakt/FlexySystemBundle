<?php

namespace Egzakt\SystemBundle\Lib;

use Symfony\Component\DependencyInjection\Container;

use Egzakt\SystemBundle\Lib\NavigationInterface;
use Egzakt\SystemBundle\Entity\Section;

/**
 * Navigation Builder
 *
 * @throws \Exception
 */
class NavigationBuilder
{
    /**
     * @var array
     */
    protected $elements;          // A collection of wrapped elements representing multi-level hierarchical data

    /**
     * @var object
     */
    protected $selectedElement;   // The element to be set as selected in the navigation tree

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var mixed $coreElements
     */
    protected $coreElements;

    /**
     * Launch the build of the navigation tree from the first level
     */
    public function build()
    {
        $elements = $this->buildLevel($this->elements);
        $this->elements = $elements;
    }

    /**
     *  Recursively build the navigation elements from a certain level
     *
     * @param array $elements An array of wrapped objects (NavigationItem)
     * @param null  $parent   The parent Element (for recursivity)
     * @param int   $level    The actual level (for recursivity)
     *
     * @throws \Exception
     *
     * @return array
     */
    private function buildLevel($elements, $parent = null, $level = 1)
    {
        foreach ($elements as $key => $element) {

            if (false == $element instanceof NavigationInterface) {
                throw new \Exception(get_class($element) . ' need to implement the NavigationInterface to be usable in the ' . get_class($this));
            }

            if (!$element->getParent()) {
                $element->setParent($parent);
            }

            $element->setSelected(get_class($element->getEntity()) == get_class($this->selectedElement) && $element->getEntity()->getId() == $this->selectedElement->getId());

            // TODO Remplacer le 1 par le ID de la section 'Accueil'
            if (get_class($element->getEntity()) == 'Egzakt\Backend\SectionBundle\Entity\Section' && $element->getEntity()->getId() == 1 && $element->isSelected() && ($this->container->get('egzakt_system.core')->getCurrentAppName() != 'backend')) {
                unset($elements[$key]);
                continue;
            }

            // This is the currently selected element
            // We set every parents of the currently selected element as selected from the current level back to level 1
            if ($element->isSelected()) {

                $parent = $element->getParent();

                while ($parent && $parent->getEntity()->getId()) {
                    $parent->setSelected(true);
                    $parent = $parent->getParent();
                }
            }

            // This element have some children, we start the same process on the children collection
            if ($element->hasChildren()) {
                $this->buildLevel($element->getChildren(), $element, ($level + 1));
            }

        }

        return $elements;
    }

    /**
     * Set Elements
     *
     * @param array $elements An array of elements
     */
    public function setElements($elements)
    {
        // Wrap elements in a NavigationItem Wrapper Class
        $wrappedElements = $this->buildNavigationItems($elements);

        $this->elements = $wrappedElements;
    }

    /**
     * Get Elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Build Navigation Items
     *
     * Wraps all navigation elements in a NavigationItem Wrapper Class
     *
     * @param array $elements
     * @param NavigationItem $parentElement
     *
     * @return array
     */
    protected function buildNavigationItems(array $elements, $parentElement = null)
    {
        $wrappedElements = array();

        foreach($elements as $element) {
            // Create the NavigationItem wrapper
            $wrappedElement = $this->buildNavigationItem($element);

            // Set the parent element and add the children to this parent element
            if ($parentElement) {
                $wrappedElement->setParent($parentElement);
                $parentElement->addChildren($wrappedElement);
            }

            // Recursive call
            if ($element->hasChildren()) {
                $this->buildNavigationItems($element->getChildren(), $wrappedElement);
            }

            $wrappedElements[] = $wrappedElement;
        }

        return $wrappedElements;
    }

    /**
     * Build Navigation Item
     *
     * Wraps a single navigation element in a NavigationItem Wrapper Class
     *
     * @param mixed $element
     *
     * @return NavigationItem
     */
    protected function buildNavigationItem($element)
    {
        $wrappedElement = $this->container->get('egzakt_system.navigation_item');
        $wrappedElement->setEntity($element);

        return $wrappedElement;
    }

    /**
     * Apply Section Hooks
     *
     * Apply the Section Hooks on the wrapped elements
     *
     * @param $parentSection The Parent Section of the navigation, in case of a sub-navigation.
     */
    public function applySectionHooks($parentSection = null)
    {
        $sectionHooksService = $this->container->get('egzakt_system.section_hooks');

        // If the navigation is a Sub-Navigation, there may be hooks.
        // These elements will act as "sub-sections" of the Parent Section
        if ($parentSection) {
            $sectionHooksElements = $sectionHooksService->getSectionHookElements($sectionHooksService->getSectionHooksById($parentSection->getId()));

            // Merge the hooks with the Section items
            $this->elements = array_merge(
                $this->elements,
                $sectionHooksElements
            );
        }

        // Apply hooks on the Section items
        $sectionHooksService->process($this->elements);

        // Apply the selected state on the Section Hooks
        $this->findSelectedInSectionHooks();
    }

    /**
     * Find Selected In Section Hooks
     *
     * Sets the currentElement with the Core Element matching the Section Hook class
     */
    protected function findSelectedInSectionHooks()
    {
        // Get the Core Elements
        $coreElements = array_reverse($this->getCoreElements());

        if (count($coreElements)) {

            // Check if the first element (the last one added) is a custom element (not a Section entity)
            $firstCoreElement = $coreElements[0];
            if (!$this->isSectionElement($firstCoreElement)) {
                // Apply the selected state on the Section Hooks
                $this->findSelectedInSectionHooksLevel($this->elements, $coreElements);
            }

        }
    }

    /**
     * Find Selected In Section Hooks Level
     *
     * Recursive function to set the currentElement with the Core Element matching the Section Hook class
     *
     * @param $elements
     * @param $navigationElements
     *
     * @return mixed
     */
    protected function findSelectedInSectionHooksLevel($elements, $navigationElements)
    {
        // Loop through the NavigationItem elements
        foreach($elements as $element) {

            if ($this->isSectionElement($element->getEntity())) {
                // Section Hooks of a Section entity
                $sectionHooksElements = $element->getSectionHooks();
            } else {
                // This element is a Section Hook
                $sectionHooksElements = array($element);
            }

            if ($sectionHooksElements) {
                // Loop through the Core Elements
                foreach($navigationElements as $navigationElement) {
                    // If this element is the same class as our navigation elements
                    if (get_class($navigationElement) == get_class($sectionHooksElements[0]->getEntity())) {
                        // Set this element as the current element, otherwise the selected won't be applied
                        $this->setSelectedElement($navigationElement);
                        return $navigationElement;
                    }
                }
            }

            // Support for multi-level hooks (sub-hooks in hooks)
            if ($element->hasChildren()) {
                $this->findSelectedInSectionHooksLevel($element->getChildren(), $navigationElements);
            }

        }
    }

    /**
     * Is Section Element
     *
     * Whether this element is a Section entity or not
     *
     * @param $element
     *
     * @return bool
     */
    protected function isSectionElement($element) {
        return ($element instanceof Section || strstr(get_class($element), 'EgzaktBackendSectionBundleEntitySectionProxy'));
    }

    /**
     * Set selected element
     *
     * @param NavigationInterface $selectedElement The element to select
     */
    public function setSelectedElement(NavigationInterface $selectedElement)
    {
        $this->selectedElement = $selectedElement;
    }

    /**
     * Get selected Element
     *
     * @return object
     */
    public function getSelectedElement()
    {
        return $this->selectedElement;
    }

    /**
     * Set Container
     *
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Get Container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set Core Elements
     *
     * Core Elements are used apply the selected state on Section Hooks
     *
     * @param mixed $coreElements
     */
    public function setCoreElements($coreElements)
    {
        $this->coreElements = $coreElements;
    }

    /**
     * Get Core Elements
     *
     * @return mixed
     */
    public function getCoreElements()
    {
        return $this->coreElements ?: array();
    }
}