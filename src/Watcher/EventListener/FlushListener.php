<?php

namespace DSentker\Watcher\EventListener;


use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\UnitOfWork;
use DSentker\Watcher\Annotations\WatchedField;
use DSentker\Watcher\ChangedField\ChangedField;
use DSentker\Watcher\Entity\EntityLog;
use DSentker\Watcher\Entity\WatchedEntity;
use DSentker\Watcher\UpdateHandler;
use DSentker\Watcher\UpdateHandlerEntityManagerAware;
use DSentker\Watcher\ValueFormatter;
use DSentker\Watcher\ValueFormatter\DefaultFormatter;

class FlushListener
{

    /** @var UpdateHandler */
    protected $handler;

    /** @var ValueFormatter */
    protected $defaultValueFormatter;

    /**
     * FlushListener constructor.
     *
     * @param UpdateHandler       $handler
     * @param ValueFormatter|null $defaultFormatter
     */
    public function __construct(UpdateHandler $handler, ValueFormatter $defaultFormatter = null)
    {
        $this->handler = $handler;
        $this->defaultValueFormatter = (null !== $defaultFormatter) ? $defaultFormatter : new DefaultFormatter();
    }

    /**
     * @return ValueFormatter
     */
    public function getDefaultValueFormatter()
    {
        return $this->defaultValueFormatter;
    }

    /**
     * @param ValueFormatter $defaultValueFormatter
     */
    public function setDefaultValueFormatter(ValueFormatter $defaultValueFormatter)
    {
        $this->defaultValueFormatter = $defaultValueFormatter;
    }


    /**
     * @param Reader $reader
     * @param        $entity
     * @param        $field
     *
     * @return WatchedField|null
     */
    private function getWatchedFieldAnnotation(Reader $reader, $entity, $field)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(sprintf('Expected Entity, given: %s!', gettype($entity)));
        }

        $entityClassName = get_class($entity);

        $reflectionProperty = new \ReflectionProperty($entityClassName, $field);
        $propertyAnnotations = $reader->getPropertyAnnotations($reflectionProperty);
        foreach ($propertyAnnotations as $annotation) {
            if ($annotation instanceof WatchedField) {
                return $annotation;
            }
        }

        return null;


    }


    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if ($this->handler instanceof UpdateHandlerEntityManagerAware) {
            $this->handler->setEntityManager($em);
        }

        /** @var AnnotationDriver $driver */
        $driver = $em->getConfiguration()->getMetadataDriverImpl();
        $reader = $driver->getReader();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof WatchedEntity) {

                // Natural Fields
                $changedFields = $uow->getEntityChangeSet($entity);


                foreach ($changedFields as $field => $values) {
                    list($oldValue, $newValue) = $values;
                    $annotation = $this->getWatchedFieldAnnotation($reader, $entity, $field);
                    if ($annotation) {

                        $changedFieldObject = new ChangedField($oldValue, $newValue, $field, $annotation->label);
                        $formatter = ($annotation->hasValueFormatter()) ? $annotation->getFormatterClassInstance() : $this->getDefaultValueFormatter();

                        $this->handler->handleUpdate($changedFieldObject, $formatter, $entity);

                    }
                }


                // Associations
                foreach ($uow->getScheduledCollectionUpdates() as $collectionUpdate) {
                    /** @var $collectionUpdate \Doctrine\ORM\PersistentCollection */


                    if ($collectionUpdate->getOwner() === $entity) {
                        // This entity has an association mapping which contains updates.

                        $collectionMapping = $collectionUpdate->getMapping();
                        $field = $collectionMapping['fieldName'];

                        $annotation = $this->getWatchedFieldAnnotation($reader, $entity, $field);

                        if ($annotation) {
                            $changedFieldObject = new ChangedField($collectionUpdate->getSnapshot(),
                                $collectionUpdate->unwrap()->toArray(), $field, $annotation->label);

                            $formatter = ($annotation->hasValueFormatter()) ? $annotation->getFormatterClassInstance() : $this->getDefaultValueFormatter();

                            $this->handler->handleUpdate($changedFieldObject, $formatter, $entity);
                        }

                    }
                }
            }
        }
    }
}