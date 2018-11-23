<?php

namespace Watcher\Entity;

/**
 * Interface LogAccessor
 *
 * @package Watcher\Entity
 */
interface LogAccessor extends WatchedEntity
{

    /**
     * @return EntityLog[]
     */
    public function getLogs();

    /**
     * @param EntityLog[] $logs
     */
    public function setLogs($logs);

}