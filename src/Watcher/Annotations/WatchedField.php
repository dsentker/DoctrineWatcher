<?php

namespace Watcher\Annotations;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\MappingAttribute;
use Watcher\ValueFormatter;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class WatchedField implements MappingAttribute
{

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $valueFormatter = null;

    /**
     * @return ValueFormatter|null
     */
    public function getFormatterClassInstance()
    {

        if (!$this->hasValueFormatter()) {
            return null;
        }

        $className = $this->valueFormatter;

        if (strpos($className, '\\') !== 0) {
            $className = '\\' . $className;
        }

        return new $className;


    }

    /**
     * @return bool
     */
    public function hasValueFormatter()
    {
        return !empty($this->valueFormatter);
    }

}