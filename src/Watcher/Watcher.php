<?php

namespace Watcher;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Watcher\EventListener\FlushListener;

class Watcher
{

    /**
     * @param UpdateHandler $handler
     *
     * @return EventManager
     */
    public static function createEventManager(UpdateHandler $handler)
    {
        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::onFlush), new FlushListener($handler));

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