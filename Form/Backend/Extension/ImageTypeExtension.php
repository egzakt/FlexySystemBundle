<?php

namespace Egzakt\SystemBundle\Form\Backend\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImageTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('image_path', 'image_filter'));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if(array_key_exists('image_path', $options)){
            $parentData = $form->getParent()->getData();

            if(null !== $parentData){
                $accessor = PropertyAccess::getPropertyAccessor();
                $imagePath = $accessor->getValue($parentData, $options['image_path']);
                $imageFilter = array_key_exists('image_filter', $options) ? $options['image_filter'] :'default_icon';
            }else{
                $imagePath = null;
                $imageFilter = null;
            }

            $view->vars['image_path'] = $imagePath;
            $view->vars['image_filter'] = $imageFilter;
        }
    }
}