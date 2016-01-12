<?php

namespace Bigfoot\Bundle\MediaBundle\Subscriber;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Menu Subscriber
 */
class MenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorage
     */
    private $security;

    /**
     * @param TokenStorage $security
     */
    public function __construct(TokenStorage $security)
    {
        $this->security = $security;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MenuEvent::GENERATE_MAIN => array('onGenerateMain', 7)
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $builder = $event->getSubject();

        $builder
            ->addChild(
                'media',
                array(
                    'label'          => 'Media',
                    'url'            => '#',
                    'attributes' => array(
                        'class' => 'parent',
                    ),
                    'linkAttributes' => array(
                        'class' => 'dropdown-toggle',
                        'icon'  => 'picture',
                    )
                ),
                array(
                    'children-attributes' => array(
                        'class' => 'submenu'
                    )
                )
            )
            ->addChildFor(
                'media',
                'media_metadata',
                array(
                    'label'  => 'Metadata',
                    'route'  => 'admin_portfolio_metadata',
                    'extras' => array(
                        'routes' => array(
                            'admin_portfolio_metadata_new',
                            'admin_portfolio_metadata_edit'
                        )
                    ),
                    'linkAttributes' => array(
                        'icon' => 'list',
                    )
                )
            );
    }
}
