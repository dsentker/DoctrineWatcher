<?php
namespace Watcher\UpdateHandler;

use App\Entity\EntityLog;
use Symfony\Component\Security\Core\Security;
use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;
use Watcher\ValueFormatter;

/**
 * Class AppWatcherHandler
 *
 * @package Watcher\UpdateHandler
 */
class SymfonyDatabaseHandler extends DatabaseHandler
{

    /** @var Security */
    private $security;

    /**
     * AppWatcherHandler constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param WatchedEntity  $entity
     * @param ChangedField   $changedField
     * @param ValueFormatter $formatter
     *
     * @return EntityLog
     */
    protected function createNewEntity(WatchedEntity $entity, ChangedField $changedField, ValueFormatter $formatter)
    {
        // Use our "new" UserEntityLog
        $log = new EntityLog($entity, $this->security->getUser());

        // from here, everything as usual
        $log->setOldValue($formatter->formatValue($changedField->getOldValue()));
        $log->setNewValue($formatter->formatValue($changedField->getNewValue()));
        $log->setField($changedField->getFieldName());
        $log->setLabel($changedField->getFieldLabel());

        return $log;
    }

}