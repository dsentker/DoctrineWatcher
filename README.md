# Watcher

 [![GitHub release](https://img.shields.io/github/release/dsentker/DoctrineWatcher.svg?style=flat-square)]()
 [![Packagist](https://img.shields.io/packagist/v/dsentker/watcher.svg?style=flat-square)]()
 [![Maintenance](https://img.shields.io/maintenance/yes/2018.svg?style=flat-square)]()

**Allows to track changes on doctrine entities with an easy-to-use and highly customizable API.**

## Documentation
[View Documentation](https://dsentker.github.io/WatcherDocumentation/)

## Quick example
You can use this library to track changes to Doctrine Entities. Use annotations to define the fields that you want to monitor. They determine where the changes are to be saved.

```php
// User Entity class
/**
 * @Column(type="string")
 * @WatchedField // <-- Watcher now tracks changes related to this field
 */
protected $emailAddress;
```

***

```php
/**
 * @var $dbParams array
 * @var $config Configuration
 */
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager(new DatabaseHandler()));
Watcher::registerAnnotations();


/** @var $user User */
$user = $em->getRepository(User::class)->find(1);
$user->setUsername("A new username");
$em->persist($user);
$em->flush();

/** @var EntityLogRepository $logRepo */
$logRepo = $em->getRepository(EntityLog::class);

/** @var EntityLog[] $changes */
$changes = $logRepo->getLogsFromEntity($user);

$lastChange = $changes[0];
echo vsprintf("Last updated at (%s): Changed %s from '%s' to '%s'", [
    $lastChange->getChangedAt()->format('Y-m-d'),
    $lastChange->getFieldLabel(),
    $lastChange->getOldValue(),
    $lastChange->getNewValue(),
]); // Last updated at 2017-09-07: Changed Email Address from 'foo@example.com' to 'foo42@example.com' 
```

## Symfony4 services.yaml integration (example)
```yaml

    watcher.db_handler:
        class:         App\Utils\AppWatcherHandler #your handler, e.g. Database Handler
        autowire:      true

    Watcher\EventListener\FlushListener:
        calls:
            -   method: pushUpdateHandler
                arguments:
                    - '@watcher.db_handler'
            -   method: setAnnotationReader
                arguments:
                    - '@annotations.reader'
        tags:
            - { name: doctrine.event_listener, event: onFlush }
    Watcher\EventListener\LoadListener:
        arguments:
            -  'App\Entity\EntityLog' # The entity which relates to EntityLogRepository
        tags:
            - { name: doctrine.event_listener, event: postLoad }

```

<a name="limitations"></a>
## Known Limitations
* This package is able to track changes on single fields and associations (collections), but depends 
on the concept of Doctrine, [which is limited to track changes on fields on the **owning side**](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/unitofwork-associations.html). That means, that inverse side associations (`@OneToMany`) are NOT supported. `@ManyToMany` and `@ManyToOne` associations _are_ supported.
* Also consider the overhead. If you've chosen the DatabaseHandler, each tracked entity change results in a single database request.

<a name="stuff"></a>
## Testing
TBD (support is appreciated!)
 
## Credits
* [Daniel Sentker](https://github.com/dsentker)
 
## Submitting bugs and feature requests
Bugs and feature request are tracked on GitHub.
 
## ToDo
* Use interfaces for everything
* Easy Symfony4 integration
* Write tests
* Optimize performance (group changes?)
 
## External Libraries
This library depends on Doctrine (surprise!) and subpackages.
 
## Copyright and license
_Watcher_ is licensed for use under the MIT License (MIT). Please see LICENSE for more information.