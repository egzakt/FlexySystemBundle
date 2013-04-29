<?php

namespace Egzakt\SystemBundle\Listener;

use Egzakt\LdapBundle\Event\LdapUserCreationEvent;
use Egzakt\SystemBundle\Entity\Role;

use Doctrine\ORM\EntityManager;

/**
 * Class LdapUserCreationListener
 */
class LdapUserCreationListener
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $roles;

    /**
     * Construct
     *
     * @param EntityManager $em
     * @param array $roles
     */
    public function __construct(EntityManager $em, array $roles)
    {
        $this->em = $em;
        $this->roles = $roles;
    }

    public function onLdapUserCreation(LdapUserCreationEvent $event)
    {
        $user = $event->getUser();
        $roles = array();

        $user->setActive(true);

        foreach($this->roles as $role) {
            $userRole = $this->em->getRepository('EgzaktSystemBundle:Role')->findOneBy(array('roleName' => $role));

            // The Role was found
            if ($userRole) {
                $roles[] = $userRole;
            } else {
                // Create a new Role
                $userRole = new Role();
                $userRole->setRoleName($role);
                $this->em->persist($userRole);

                $roles[] = $userRole;
            }
        }

        // This method must be declared in the User Entity because we need to add default Roles.
        // The UserInterface doesn't handle this part.
        $user->setUserRoles($roles);
    }

}