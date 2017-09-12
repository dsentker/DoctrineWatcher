<?php

namespace Watcher;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Watcher\EventListener\FlushListener;
use Watcher\EventListener\LoadListener;

class Watcher
{

    /**
     * @param UpdateHandler $handler
     *
     * @return EventManager
     */
    public static function createEventManager(UpdateHandler $handler)
    {
        $listener = FlushListener::createWithHandler($handler);
        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::onFlush), $listener);
        $eventManager->addEventListener(array(Events::postLoad), new LoadListener());

        return $eventManager;
    }

    /**
     * @return bool
     */
    public static function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/WatchedField.php');

    }
}