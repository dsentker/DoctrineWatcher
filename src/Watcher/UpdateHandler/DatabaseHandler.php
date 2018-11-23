<?php

namespace Watcher\UpdateHandler;

use Doctrine\ORM\EntityManager;
use Watcher\ChangedField\ChangedField;
use Watcher\Entity\EntityLog;
use Watcher\Entity\WatchedEntity;
use Watcher\UpdateHandlerEntityManagerAware;
use Watcher\ValueFormatter;

/**
 * Class DatabaseHandler
 *
 * @package Watcher\UpdateHandler
 */
class DatabaseHandler implements UpdateHandlerEntityManagerAware
{

    /** @var EntityManager */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * This method is the perfect start point when this class is extended.
     *
     * @param WatchedEntity  $entity
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     *
     * @return EntityLog
     * @throws \Exception
     */
    protected function createNewEntity(WatchedEntity $entity, ChangedField $changedField, ValueFormatter $formatter) {
        $log = new EntityLog($entity);
        $log->setOldValue($formatter->formatValue($changedField->getOldValue()));
        $log->setNewValue($formatter->formatValue($changedField->getNewValue()));
        $log->setField($changedField->getFieldName());
        $log->setLabel($changedField->getFieldLabel());

        return $log;
    }

    /**
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     * @param WatchedEntity  $entity
     *
     * @throws \Exception
     */
    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity)
    {

        $log = $this->createNewEntity($entity, $changedField, $formatter);

        $this->em->persist($log);
        $this->em->getUnitOfWork()->computeChangeSets();

        // The two lines below were suggested in documentation, but are not needed IMHO.
        #$metaData = $this->em->getClassMetadata(get_class($entity));
        #$uow->recomputeSingleEntityChangeSet($metaData, $entity);

    }

}