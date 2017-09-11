<?php

namespace Watcher\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Watcher\Entity\EntityLog;
use Watcher\Entity\LogAccessor;
use Watcher\Repository\EntityLogRepository;

class LoadListener
{

    public function postLoad(LifecycleEventArgs $args)
    {

        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        /** @var EntityLogRepository $logRepo */
        $logRepo = $em->getRepository(EntityLog::class);

        if ($entity instanceof LogAccessor) {
            $entity->setLogs($logRepo->getLogsFromEntity($entity));
        }


    }
}