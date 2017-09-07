<?php

namespace DSentker\Watcher;

use DSentker\Watcher\ChangedField\ChangedField;
use DSentker\Watcher\Entity\WatchedEntity;

interface UpdateHandler
{

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity);

}