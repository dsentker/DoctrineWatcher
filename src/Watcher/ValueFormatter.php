<?php
namespace Watcher;

/**
 * Interface ValueFormatter
 *
 * @package Watcher
 */
interface ValueFormatter
{

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value);

}