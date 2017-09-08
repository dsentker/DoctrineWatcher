<?php

namespace Watcher\UpdateHandler;

use Watcher\ChangedField\ChangedField;
use Watcher\UpdateHandler;
use Watcher\Entity\WatchedEntity;
use Watcher\ValueFormatter;

class NullHandler implements UpdateHandler
{

    /**
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     * @param WatchedEntity  $entity
     */
    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity)
    {

        return;

    }

}