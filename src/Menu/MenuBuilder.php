<?php


namespace App\Menu;


use Knp\Menu\FactoryInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Survos\LandingBundle\Menu\LandingMenuBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder extends LandingMenuBuilder
{

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');

        $menu->addChild('Home', ['route' => 'survos_landing'])
            ->setAttribute('icon', 'fas fa-home');

        $menu->addChild('admin', ['route' => 'easyadmin']);
        $menu->addChild('kden_reader', ['route' => 'kden_reader']);
        $menu->addChild('kden_writer', ['route' => 'kden_writer']);
        $menu->addChild('kden_dtd', ['route' => 'kden_dtd']);
        $menu->addChild('projects', ['route' => 'project_crud_index'])->setAttribute('icon', 'fas fa-list');

        // ... add more children

        return $this->cleanupMenu($menu);
    }

    public function projectMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');

        $menu->addChild('Transcripts', ['route' => 'transcript_index'])->setAttribute('icon', 'fas fa-list');

        $menu->addChild('admin', ['route' => 'easyadmin']);

        // ... add more children


    }



}