EntityRouting
=========================

This service is composed with 3 things :
 - a service
 - a mapper
 - a twig function

The "EntityRouting" service will be responsible to bind Entity with Routes. This way, we can use a twig function and pass our entity to get the route.
Let's see how this work.

### The Twig Template

In the backend, we write our templates like this :

```html
<td class="edit">
    <a href="{{ path('egzakt_system_backend_user_edit', { 'id': entity.id }) }}" title="Modifier">
        <img src="{{ asset('bundles/egzaktsystem/backend/images/buttons/edit.png') }}" width="15" height="15" alt="" />
    </a>
</td>
<td class="delete">
    <a href="{{ path('egzakt_system_backend_user_delete', { 'id': entity.id }) }}" rel="{{ path('egzakt_system_backend_user_delete', { 'id': entity.id, 'message': true }) }}" title="{% trans %}Delete{% endtrans %}">
        <img src="{{ asset('bundles/egzaktsystem/backend/images/buttons/delete.png') }}" width="12" height="15" alt="" />
    </a>
</td>
```

This is 100% correct, we call path() to get the url of a route by passing its name and parameters if needed.
But if we go in detail, we see that our route share the same "prefix" : "egzakt_system_backend_user". So, we are repeating the same thing over and over.
And we all know that coding the same thing is a bad concept.

This is the final solution of all problems :

```html
<td class="edit">
    <a href="{{ entitypath(entity, 'edit' }) }}" title="Modifier">
        <img src="{{ asset('bundles/egzaktsystem/backend/images/buttons/edit.png') }}" width="15" height="15" alt="" />
    </a>
</td>
<td class="delete">
    <a href="{{ entitypath(entity, 'delete' }) }}" rel="{{ entitypath(entity, 'delete', {'message': true}) }}" title="{% trans %}Delete{% endtrans %}">
        <img src="{{ asset('bundles/egzaktsystem/backend/images/buttons/delete.png') }}" width="12" height="15" alt="" />
    </a>
</td>
```

It's shorter and easy to understand. The code says : "Give me the path of this entity with XX action". And this is exactly what we want.
To make this possible, we need something which can bind our entities and our routes. This is where the mapper is needed.

### The Mapper

The Mapper is a class who contains the logic for binding entities to routes. As an exemple :

```php
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
    }

}
```

Each mapper class needs to implements "Egzakt\SystemBundle\Lib\EntityRouteMappingInterface". This interface requires one method : ::map(), and a builder is passed as a parameter.

In this ::map() method, we will add an entity-route mapping into the builder by calling ::add() on the builder.
::add() require at least 4 parameters :
 - the application name
 - the entity class name
 - the route name
 - the variable name in the route like the Id or a slug.

You can pass a 5th parameter which is the property name of the class used to link with the variable name in the route, but it's set to "slug" in the frontend app, and "id" for the other application by default.

In our example, we bound the User class with the "egzakt_system_backend_user" route. We assume that all actions against User class will use the prefix "egzakt_system_backend_user" route.
We can have "egzakt_system_backend_user_edit", "egzakt_system_backend_user_delete", "egzakt_system_backend_user_add", etc... Don't worry about the last "_" in the route, it's automatically handled by the service.

### The Configuration file

This mapping has to be registered inside our service like this :
```yaml
    egzakt_system.entity_route_system_builder:
        class: Egzakt\SystemBundle\Lib\EntityRouteSystemMapping
        tags:
            - { name: egzakt_system.entity_route }
```

-------------------

And now, you can use entitypath() inside your Twig templates.
