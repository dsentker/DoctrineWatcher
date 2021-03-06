<?php

namespace Watcher;

use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;

/**
 * Interface UpdateHandler
 *
 * @package Watcher
 */
interface UpdateHandler
{

    /**
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     * @param WatchedEntity  $entity
     *
     * @return void
     */
    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity);

}