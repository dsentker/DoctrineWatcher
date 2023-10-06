<?php

namespace Watcher\EventListener;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Watcher\Annotations\WatchedField;
use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;
use Watcher\UpdateHandler;
use Watcher\UpdateHandlerEntityManagerAware;
use Watcher\ValueFormatter;
use Watcher\ValueFormatter\DefaultFormatter;

/**
 * Class FlushListener
 *
 * @package Watcher\EventListener
 */
class FlushListener
{

    /** @var UpdateHandler[] */
    protected $handler = [];

    /** @var ValueFormatter */
    protected $defaultValueFormatter;

    /** @var Reader */
    private $annotationReader;

    /**
     * FlushListener constructor.
     *
     * @param ValueFormatter|null $defaultFormatter
     */
    public function __construct(ValueFormatter $defaultFormatter = null)
    {
        $this->defaultValueFormatter = (null !== $defaultFormatter) ? $defaultFormatter : new DefaultFormatter();
    }

    /**
     * @param UpdateHandler $handler
     *
     * @return FlushListener
     */
    public static function createWithHandler(UpdateHandler $handler)
    {
        $listener = new static();
        $listener->pushUpdateHandler($handler);
        return $listener;
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
     * @return Reader
     */
    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    /**
     * @param Reader $annotationReader
     */
    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param UpdateHandler $handler
     *
     * @return $this
     */
    public function pushUpdateHandler(UpdateHandler $handler)
    {
        $this->handler[] = $handler;
        return $this;
    }

    public function pushUpdateHandlers(array $handler)
    {
        foreach ($handler as $updateHandler) {
            $this->pushUpdateHandler($updateHandler);
        }
    }

    protected function clearUpdateHandlers()
    {
        $this->handler = [];
    }

    /**
     * @return UpdateHandler[]
     */
    public function getUpdateHandler()
    {
        return $this->handler;
    }

    /**
     * @param Reader        $reader
     * @param WatchedEntity $entity
     * @param string        $field
     *
     * @return WatchedField|null
     * @throws \ReflectionException
     */
    private function getWatchedFieldAnnotation(Reader $reader, WatchedEntity $entity, string $field): ?WatchedField
    {

        $dotPosition = strpos($field, '.');
        if (false !== $dotPosition) {
            // This is not a 'regular' field, but an embedded object, which is noted as "mainEntityField.embeddedField".
            // What we need is just the first part of the string (before the point).
            // If an embedded is found, the position of the dot is 0-based. "ab.cd" -> dot is on pos. 2
            $field = substr($field, 0, $dotPosition);
        }

        // Do not use a proxied class
        $realClass = ClassUtils::getClass($entity);
        $reflectionProperty = new \ReflectionProperty($realClass, $field);

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

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $reader = $this->getAnnotationReader();
        if (empty($reader)) {
            // Try to get annotation reader from metadata driver, provided by configuration.

            /** @var AnnotationDriver $driver */
            $driver = $em->getConfiguration()->getMetadataDriverImpl();

            if (!($driver instanceof AnnotationDriver)) {
                throw new \RuntimeException(sprintf('Annotation driver not provided (given: "%s"! Use the Doctrine Configuration or pass it via %s::setAnnotationReader!', get_class($driver), __CLASS__));
            }

            /** @var CachedReader $reader */
            $reader = $driver->getReader();

        }

        foreach ($this->handler as $handler) {
            if ($handler instanceof UpdateHandlerEntityManagerAware) {
                $handler->setEntityManager($em);
            }

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

                            $handler->handleUpdate($changedFieldObject, $formatter, $entity);

                        }
                    }


                    // Associations
                    foreach ($uow->getScheduledCollectionUpdates() as $collectionUpdate) {

                        if ($collectionUpdate->getOwner() === $entity) {
                            // This entity has an association mapping which contains updates.

                            $collectionMapping = $collectionUpdate->getMapping();
                            $field = $collectionMapping['fieldName'];

                            $annotation = $this->getWatchedFieldAnnotation($reader, $entity, $field);

                            if ($annotation) {
                                $changedFieldObject = new ChangedField($collectionUpdate->getSnapshot(),
                                    $collectionUpdate->unwrap()->toArray(), $field, $annotation->label);

                                $formatter = ($annotation->hasValueFormatter()) ? $annotation->getFormatterClassInstance() : $this->getDefaultValueFormatter();

                                $handler->handleUpdate($changedFieldObject, $formatter, $entity);
                            }

                        }
                    }
                }
            }
        }
    }
}