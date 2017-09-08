<?php

namespace Watcher\UpdateHandler;

use Doctrine\ORM\EntityManager;
use Watcher\ChangedField\ChangedField;
use Watcher\Entity\EntityLog;
use Watcher\UpdateHandler;
use Watcher\Entity\WatchedEntity;
use Watcher\ValueFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogHandler implements UpdateHandler
{

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $logLevel;


    /**
     * LogHandler constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setLogLevel(LogLevel::INFO);
    }

    /**
     * @return mixed
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param mixed $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     * @param WatchedEntity  $entity
     */
    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity)
    {

        $message = vprintf('Field "%s" (%s) changed from %s to %s.', [
            $changedField->getFieldLabel(),
            $changedField->getFieldName(),
            $formatter->formatValue($changedField->getOldValue()),
            $formatter->formatValue($changedField->getNewValue()),
        ]);

        $this->logger->log($this->getLogLevel(), $message, [
            'entity' => $entity,
        ]);


    }

}