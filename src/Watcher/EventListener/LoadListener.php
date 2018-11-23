<?php

namespace Watcher\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Watcher\Entity\LogAccessor;
use Watcher\Repository\EntityLogRepository;

/**
 * Class LoadListener
 *
 * @package Watcher\EventListener
 */
class LoadListener
{

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        /** @var EntityLogRepository $logRepo */
        $logRepo = $args->getEntityManager()->getRepository(get_class($entity));

        if ($entity instanceof LogAccessor) {
            $entity->setLogs($logRepo->getLogsFromEntity($entity));
        }

    }
}