## Use a custom database Handler to gain more information

The DatabaseHandler provided by this package covers only basic information.

_Excerpt from  `\Watcher\UpdateHandlerDatabaseHandler:`_
```php
/**
 * This method is the perfect start point when this class is extended.
 *
 * @param WatchedEntity  $entity
 * @param ChangedField   $changedField
 * @param ValueFormatter $formatter
 *
 * @return EntityLog
 */
protected function createNewEntity(WatchedEntity $entity, ChangedField $changedField, ValueFormatter $formatter) {
    $log = new EntityLog($entity);
    $log->setOldValue($formatter->formatValue($changedField->getOldValue()));
    $log->setNewValue($formatter->formatValue($changedField->getNewValue()));
    $log->setField($changedField->getFieldName());
    $log->setLabel($changedField->getFieldLabel());

    return $log;
}
```

As you can see, no more information is stored. If you want to persist the current user id or some other information too, you have to use a custom database handler.

## Example: Storing the user id to changed fields

### Extend entity_logs table
First, you have to add a user_id column to the table entity_logs:

```sql
ALTER TABLE `entity_logs`
	ADD COLUMN `user_id` INT UNSIGNED NULL DEFAULT NULL AFTER `changed_at`;
```

We choose to allow NULL values on column user_id (if an anonymous change is done, e.g. from a cronjob).

### Extend EntityLog
Because the additional field in is not treated in \Watcher\Entity\EntityLog, we create a custom entity, based on the "old" one:
```php
namespace Example;

use Example\Entity\User;
use Watcher\Entity\EntityLog;

/**
 * @ORM\Entity(repositoryClass="Watcher\Repository\EntityLogRepository")
 * @ORM\Table(name="entity_logs")
 */
class UserEntityLog extends EntityLog
{

    /**
     * @var User|UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Example\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $anonymousName
     *
     * @return string
     */
    public function getUsername()
    {
        return ($this->user)
            ? $this->getUser()->getUsername()
            : '(anonymous)';
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}
```

### Extend the DatabaseHandler
As described above, the current `DatabaseHandler` does not cover this new column, so let's extend this class, too:

```php
namespace Example;

use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;
use Watcher\UpdateHandler\DatabaseHandler;
use Watcher\ValueFormatter;
use Example\Entity\User;

class UserDatabaseHandler extends DatabaseHandler
{

    /** @var User */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    protected function createNewEntity(WatchedEntity $entity, ChangedField $changedField, ValueFormatter $formatter)
    {
    
        // Use our "new" UserEntityLog
        $log = new UserEntityLog($entity);          
        
        $log->setUser($this->user);
        
        // from here, everything as usual
        $log->setOldValue($formatter->formatValue($changedField->getOldValue()));
        $log->setNewValue($formatter->formatValue($changedField->getNewValue()));
        $log->setField($changedField->getFieldName());
        $log->setLabel($changedField->getFieldLabel());
        
        return $log;
    }

}
```

### Configuring the event manager
What is still missing, is the integration of the new UserDatabaseHandler. As described in Introduction > Setup, this handler must assigned in the configuration process:
 
```php
$currentUser = $this->getUser(); 
$updateHandler = new UserDatabaseHandler($currentUser);
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager($updateHandler));
```