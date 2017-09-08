<?php

namespace Watcher\UpdateHandler;

use Doctrine\ORM\EntityManager;
use Watcher\ChangedField\ChangedField;
use Watcher\Entity\EntityLog;
use Watcher\Entity\WatchedEntity;
use Watcher\UpdateHandlerEntityManagerAware;
use Watcher\ValueFormatter;

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

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity)
    {

        $uow = $this->em->getUnitOfWork();

        $log = new EntityLog($entity);
        $log->setOldValue($formatter->formatValue($changedField->getOldValue()));
        $log->setNewValue($formatter->formatValue($changedField->getNewValue()));
        $log->setField($changedField->getFieldName());
        $log->setLabel($changedField->getFieldLabel());

        $this->em->persist($log);
        $metaData = $this->em->getClassMetadata(get_class($entity));
        $uow->computeChangeSets();
        $uow->recomputeSingleEntityChangeSet($metaData, $entity);

    }

}