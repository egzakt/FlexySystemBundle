<?php

namespace Egzakt\SystemBundle\Lib;

interface EntityRouteMappingInterface
{

    /**
     * @param EntityRoutingBuilder $builder
     */
    public function map(EntityRoutingBuilder $builder);

}