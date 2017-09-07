<?php

namespace DSentker\Watcher\Entity;

use DSentker\Watcher\ChangedField\ChangedField;

/**
 * Class EntityLog
 *
 * @Entity
 * @Table(name="entity_logs")
 */
class EntityLog
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="datetime", name="changed_at")
     */
    protected $changedAt;

    /**
     * @Column(type="string", name="entity_class")
     */
    protected $entityClass;

    /**
     * @Column(type="integer", name="entity_id")
     */
    protected $entityId;

    /**
     * @Column(type="string", name="field")
     */
    protected $field;

    /**
     * @Column(type="string", name="label")
     */
    protected $label;

    /**
     * @Column(type="string", name="old_value")
     */
    protected $oldValue;

    /**
     * @Column(type="string", name="new_value")
     */
    protected $newValue;

    public function __construct(WatchedEntity $entity)
    {
        $this->entityClass = get_class($entity);
        $this->entityId = $entity->getId();
        $this->changedAt = new \DateTime('NOW');
    }

    /**
     * @param WatchedEntity $entity
     * @param ChangedField $changedField
     * @return static
     */
    public static function createFormChangedField(WatchedEntity $entity, ChangedField $changedField)
    {
        $log = new static($entity);
        $log->setField($changedField->getFieldName());
        $log->setLabel($changedField->getFieldLabel());
        $log->setOldValue($changedField->getOldValue());
        $log->setNewValue($changedField->getNewValue());

        return $log;
    }

    /**
     * @return \DateTime
     */
    public function getChangedAt()
    {
        return $this->changedAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     * @return EntityLog
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return EntityLog
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @param mixed $oldValue
     * @return EntityLog
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param mixed $newValue
     * @return EntityLog
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
        return $this;
    }


}