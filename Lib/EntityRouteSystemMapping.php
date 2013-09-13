<?php

namespace Egzakt\SystemBundle\Lib;

class EntityRouteSystemMapping implements EntityRouteMappingInterface
{

    /**
     * @inheritdoc
     */
    public function map(EntityRoutingBuilder $builder)
    {

        $builder->add('backend', 'Egzakt\\SystemBundle\\Entity\\User', 'egzakt_system_backend_user', 'id');
        $builder->add('backend', 'Egzakt\\SystemBundle\\Entity\\Role', 'egzakt_system_backend_role', 'id');
        $builder->add('backend', 'Egzakt\\SystemBundle\\Entity\\App', 'egzakt_system_backend_app', 'id');
        $builder->add('backend', 'Egzakt\\SystemBundle\\Entity\\Section', 'egzakt_system_backend_section', 'id');

        $builder->add('frontend', 'Egzakt\\SystemBundle\\Entity\\Section', 'egzakt_system_frontend_section', 'sectionsPath');


    }
}