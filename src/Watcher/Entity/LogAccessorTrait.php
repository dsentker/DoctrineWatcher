<?php

namespace Watcher\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LogAccessorTrait
 *
 * @package Watcher\Entity
 */
trait LogAccessorTrait
{

    /** @var EntityLog[] */
    private $logs;

    /**
     * @return EntityLog[]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param EntityLog[] $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }


}