<?php

namespace Watcher\ChangedField;

class ChangedField
{

    /** @var string */
    private $fieldName;

    /** @var string */
    private $fieldLabel;

    /** @var mixed */
    private $oldValue;

    /** @var mixed */
    private $newValue;

    /**
     * ChangedField constructor.
     *
     * @param string $fieldName
     * @param string $fieldLabel
     * @param mixed  $oldValue
     * @param mixed  $newValue
     */
    public function __construct($oldValue, $newValue, $fieldName, $fieldLabel = null)
    {
        $this->fieldName = $fieldName;
        $this->fieldLabel = ($fieldLabel) ? $fieldLabel : $fieldName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

}