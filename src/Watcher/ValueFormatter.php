<?php
namespace Watcher;

interface ValueFormatter
{

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value);

}