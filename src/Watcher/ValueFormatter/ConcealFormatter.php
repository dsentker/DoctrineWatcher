<?php

namespace Watcher\ValueFormatter;

use Doctrine\Common\Collections\Collection;

/**
 * Class ConcealFormatter
 *
 * @package Watcher\ValueFormatter
 */
class ConcealFormatter extends DefaultFormatter
{

    const CONCEAL_CHARACTER = '*';

    protected function formatBoolean($value)
    {
        return static::CONCEAL_CHARACTER;
    }

    protected function formatNull()
    {
        return static::CONCEAL_CHARACTER;
    }

    protected function formatString($value)
    {
        return str_repeat(static::CONCEAL_CHARACTER, mb_strlen($value));
    }

    protected function formatDateTime(\DateTimeInterface $dateTime)
    {
        return preg_replace('/[0-9]/', static::CONCEAL_CHARACTER, parent::formatDateTime($dateTime));
    }

    protected function formatObject($object)
    {
        return str_repeat(static::CONCEAL_CHARACTER, 3);
    }

    protected function formatArray(array $array)
    {
        $concealString = str_repeat(static::CONCEAL_CHARACTER, 3);
        return str_repeat($concealString, count($array));
    }

    protected function formatCollection(Collection $collection)
    {
        $concealString = str_repeat(static::CONCEAL_CHARACTER, 3);
        return str_repeat($concealString, $collection->count());
    }

    protected function formatOther($other)
    {
        return str_repeat(static::CONCEAL_CHARACTER, 3);
    }


}