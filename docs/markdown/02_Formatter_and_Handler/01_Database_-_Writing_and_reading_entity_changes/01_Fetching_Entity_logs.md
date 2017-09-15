## Fetching entity logs

## Fetch EntityLogs from a repository
Use the `\Watcher\Repository\EntityLogRepository` (or extend it). This Repository provides the method `getLogsFromEntity()`:

```php
$userRepository = $em->getRepository(User::class);
$user = $userRepository->find(42);

/** @var $logRepository EntityLogRepository */
$logRepository = $em->getRepository(EntityLog::class);

/** @var $logs EntityLog[] */
$logs = $logRepository->getLogsFromEntity($user);

printf("Latest change: %s.", $logs[0]->getChangedAt()->format('Y-m-d H:i:s'));
```

## Fetch EntityLogs from entity
You can also inject the logs from to the related entity itself. To achieve this, a few steps are needed:

### Register the LoadListener
When creating the event manager, let the `\Watcher\EventListener\LoadListener` listen to the `Event::postLoad` event: 
```php
// ...
$eventManager->addEventListener(array(Events::onFlush), $listener);
// ...
$eventManager->addEventListener(array(Events::postLoad), new LoadListener());
```

### Assign the LogAccessor interface
Your entity must follow the `\Watcher\Entity\LogAccessor` interface to ensure that the LoadListener can inject the changes:

```php
/**
 * User Entity
 * @ORM\Entity
 * @ORM\Table(name="example_users")
 */
class User implements LogAccessor {
    // ...
}
```

The LogAccessor interface does look like this: 

```php
interface LogAccessor extends WatchedEntity
{

    /**
     * @return EntityLog[]
     */
    public function getLogs();

    /**
     * @param EntityLog[] $logs
     */
    public function setLogs($logs);

}
```

As you can the, the interface does already implement the `WatchedEntity` interface, so there is no need to implement both.

Fortunately, this package already provides a `\Watcher\Enity\LogAccessorTrait`, so there is no more configuration needed (You must implement the LogAccessor since PHP does not supports interface implementations on traits).

From now, you can fetch entites without an EnityLogRepository:

```php
$userRepository = $em->getRepository(User::class);
$user = $userRepository->find(42);
$logs = $user->getLogs();
printf("Latest change: %s (%d changes total).", $logs[0]->getChangedAt()->format('Y-m-d H:i:s'), count($logs);
```