<?php
namespace DSentker\Watcher\Repository;

use Doctrine\ORM\EntityRepository;
use DSentker\Watcher\Entity\EntityLog;
use DSentker\Watcher\Entity\WatchedEntity;

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
                'id' => $entity->getId(),
                'class' => get_class($entity),
            ])
            ->getQuery()->getResult();
    }

}