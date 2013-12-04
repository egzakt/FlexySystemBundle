<?php

namespace Flexy\Frontend\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Flexy\SystemBundle\Entity\Section;
use Flexy\SystemBundle\Entity\SectionTranslation;

class LoadSectionData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Load
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $metadata = $manager->getClassMetaData('Flexy\\SystemBundle\\Entity\\Section');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $sectionHome = new Section();
        $sectionHome->setId(1);
        $sectionHome->setContainer($this->container);
        $sectionHome->setApp($manager->merge($this->getReference('app-frontend')));

        $sectionHomeFr = new SectionTranslation();
        $sectionHomeFr->setLocale($manager->merge($this->getReference('locale-fr'))->getCode());
        $sectionHomeFr->setName('Accueil');
        $sectionHomeFr->setActive(true);
        $sectionHomeFr->setTranslatable($sectionHome);

        $sectionHomeEn = new SectionTranslation();
        $sectionHomeEn->setLocale($manager->merge($this->getReference('locale-en'))->getCode());
        $sectionHomeEn->setName('Home');
        $sectionHomeEn->setActive(true);
        $sectionHomeEn->setTranslatable($sectionHome);

        $manager->persist($sectionHome);
        $manager->persist($sectionHomeFr);
        $manager->persist($sectionHomeEn);

        $section404 = new Section();
        $section404->setId(2);
        $section404->setContainer($this->container);
        $section404->setApp($manager->merge($this->getReference('app-frontend')));

        $section404Fr = new SectionTranslation();
        $section404Fr->setLocale($manager->merge($this->getReference('locale-fr'))->getCode());
        $section404Fr->setName('Erreur 404');
        $section404Fr->setActive(true);
        $section404Fr->setTranslatable($section404);

        $section404En = new SectionTranslation();
        $section404En->setLocale($manager->merge($this->getReference('locale-en'))->getCode());
        $section404En->setName('404 Error');
        $section404En->setActive(true);
        $section404En->setTranslatable($section404);

        $manager->persist($section404);
        $manager->persist($section404Fr);
        $manager->persist($section404En);

        $section403 = new Section();
        $section403->setId(3);
        $section403->setContainer($this->container);
        $section403->setApp($manager->merge($this->getReference('app-frontend')));

        $section403Fr = new SectionTranslation();
        $section403Fr->setLocale($manager->merge($this->getReference('locale-fr'))->getCode());
        $section403Fr->setName('Erreur 403');
        $section403Fr->setActive(true);
        $section403Fr->setTranslatable($section403);

        $section403En = new SectionTranslation();
        $section403En->setLocale($manager->merge($this->getReference('locale-en'))->getCode());
        $section403En->setName('403 Error');
        $section403En->setActive(true);
        $section403En->setTranslatable($section403);

        $manager->persist($section403);
        $manager->persist($section403Fr);
        $manager->persist($section403En);

        $section500 = new Section();
        $section500->setId(4);
        $section500->setContainer($this->container);
        $section500->setApp($manager->merge($this->getReference('app-frontend')));

        $section500Fr = new SectionTranslation();
        $section500Fr->setLocale($manager->merge($this->getReference('locale-fr'))->getCode());
        $section500Fr->setName('Erreur 500');
        $section500Fr->setActive(true);
        $section500Fr->setTranslatable($section500);

        $section500En = new SectionTranslation();
        $section500En->setLocale($manager->merge($this->getReference('locale-en'))->getCode());
        $section500En->setName('500 Error');
        $section500En->setActive(true);
        $section500En->setTranslatable($section500);

        $manager->persist($section500);
        $manager->persist($section500Fr);
        $manager->persist($section500En);
        
        $manager->flush();

        $this->addReference('section-home', $sectionHome);
        $this->addReference('section-403', $section403);
        $this->addReference('section-404', $section404);
        $this->addReference('section-500', $section500);
    }

    /**
     * Get Order
     *
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
