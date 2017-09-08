<?php

namespace Watcher;

use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;

interface UpdateHandler
{

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity);

}