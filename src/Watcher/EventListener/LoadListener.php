<?php

namespace Watcher\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Watcher\Entity\LogAccessor;
use Watcher\Repository\EntityLogRepository;

/**
 * Class LoadListener
 *
 * @package Watcher\EventListener
 */
class LoadListener
{

    /** @var string */
    private $entityLogClassname;

    public function __construct(string $entityLogClassname)
    {
        if(!class_exists($entityLogClassname)) {
            throw new \RuntimeException(sprintf('Invalid entity log classname "%s": Must be a full-qualified class name of the EntityLog.', $entityLogClassname));
        }
        $this->entityLogClassname = $entityLogClassname;
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof LogAccessor) {

            /** @var EntityLogRepository $logRepo */
            $logRepo = $args->getObjectManager()->getRepository($this->entityLogClassname);
            $entity->setLogs($logRepo->getLogsFromEntity($entity));
        }


    }
}