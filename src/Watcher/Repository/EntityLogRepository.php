<?php
namespace Watcher\Repository;

use Doctrine\ORM\EntityRepository;
use Watcher\Entity\EntityLog;
use Watcher\Entity\WatchedEntity;

/**
 * Class EntityLogRepository
 *
 * @package Watcher\Repository
 */
class EntityLogRepository extends EntityRepository
{

    /**
     * @param WatchedEntity $entity
     *
     * @return EntityLog[]
     */
    public function getLogsFromEntity(WatchedEntity $entity)
    {
        return $this->createQueryBuilder('l')
            ->where('l.entityId = :id')
            ->andWhere('l.entityClass = :class')
            ->orderBy('l.changedAt', 'DESC')
            ->setParameters([
                'id' => $entity->getId(), // Take id column name from column metadata
                'class' => get_class($entity),
            ])
            ->getQuery()->getResult();
    }

}