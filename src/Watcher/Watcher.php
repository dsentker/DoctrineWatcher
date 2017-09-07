<?php

namespace DSentker\Watcher;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use DSentker\Watcher\EventListener\FlushListener;

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
        AnnotationRegistry::registerLoader(function ($class) {
            $classNameParts = explode('\\', $class);
            $className = end($classNameParts);
            $path = __DIR__ . '/Annotations/' . $className . '.php';

            // file exists makes sure that the loader fails silently
            if (file_exists($path)) {
                require_once $path;
            }
        });

        return true;
    }
}