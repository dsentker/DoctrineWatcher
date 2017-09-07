<?php

namespace DSentker\Watcher\UpdateHandler;

use DSentker\Watcher\ChangedField\ChangedField;
use DSentker\Watcher\UpdateHandler;
use DSentker\Watcher\Entity\WatchedEntity;
use DSentker\Watcher\ValueFormatter;

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