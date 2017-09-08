<?php

namespace Watcher\ValueFormatter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Watcher\ValueFormatter;

class DefaultFormatter implements ValueFormatter
{

    /**
     * @param bool $value
     *
     * @return string
     */
    protected function formatBoolean($value)
    {
        return ($value) ? 'Yes' : 'No';
    }

    /**
     * @return string
     */
    protected function formatNull()
    {
        return 'N/A';
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function formatString($value)
    {
        return (string)$value;
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return string
     */
    protected function formatDateTime(\DateTimeInterface $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param object $object
     *
     * @return string
     */
    protected function formatObject($object)
    {
        return (method_exists($object, '__toString')) ? $object->__toString() : get_class($object);
    }

    /**
     * @param array $array
     *
     * @return string
     */
    protected function formatArray(array $array)
    {
        $items = [];
        foreach ($array as $item) {
            $items[] = $this->formatValue($item);
        }
        return implode(', ', $items);
    }

    /**
     * @param Collection $collection
     *
     * @return string
     */
    protected function formatCollection(Collection $collection)
    {
        $collectionData = $collection->map($this->getCollectionFormatter())->toArray();
        return implode(', ', $collectionData);
    }

    /**
     * @return \Closure
     */
    protected function getCollectionFormatter()
    {
        $formatter = $this;
        return function ($entity) use ($formatter) {
            return $formatter->formatValue($entity);
        };
    }

    /**
     * @param mixed $other
     *
     * @return string
     */
    protected function formatOther($other)
    {
        return gettype($other);
    }


    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value)
    {
        if (is_bool($value)) {
            return $this->formatBoolean($value);
        }

        if (is_array($value)) {
            return $this->formatArray($value);
        }

        if (empty($value)) {
            return $this->formatNull();
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            return $this->formatString($value);
        }

        if (is_object($value)) {

            if ($value instanceof \DateTimeInterface) {
                return $this->formatDateTime($value);
            } elseif ($value instanceof Collection) {
                return $this->formatCollection($value);
            } else {
                return $this->formatObject($value);
            }

        }

        return $this->formatOther($value);
    }

}