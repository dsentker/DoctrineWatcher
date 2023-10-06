<?php

namespace Watcher;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Watcher\EventListener\FlushListener;
use Watcher\EventListener\LoadListener;

/**
 * Class Watcher
 *
 * @package Watcher
 */
class Watcher
{

    public static function createEventManager(UpdateHandler $handler): EventManager
    {
        $listener = FlushListener::createWithHandler($handler);
        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::onFlush), $listener);
        $eventManager->addEventListener(array(Events::postLoad), new LoadListener());

        return $eventManager;
    }

    /**
     * @deprecated This method is deprecated and will be removed in doctrine/annotations 2.0. Annotations will be autoloaded in 2.0.
     */
    public static function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/WatchedField.php');

    }
}