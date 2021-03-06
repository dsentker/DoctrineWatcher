# Watcher 

**Allows to track changes on doctrine entities with an easy-to-use and highly customizable API.**

You can use this library to track changes to Entites. You can use annotations to define the fields that you want to monitor. They determine where the changes are to be saved.

```php
// User Entity class
/**
 * @Column(type="string")
 * @WatchedField // <-- Watcher now tracks changes related to this field
 */
protected $emailAddress;
```

<a name="example"></a>
# Quick start
Given the fictious entity _User_, you want to track whether a change on the email address has occured. To do so, add the annotation `@WatchedField` to the field `$emailAddress`. You also add the Interface `WatchedEntity` to the entity (this only needs the `getId()` method to help the library track changes).

```php
/**
 * @Entity
 * @Table(name="app_user")
 */
class User implements WatchedEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string")
     * @WatchedField
     */
    protected $emailAddress;
    
    // ...
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @var string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }
    
}
```

**Watcher** is now watching this field. If it is 
changed (`$user->setEmailAddress("john@example.com")` ), this change is saved during 
the flush (`$em->flush()` process.
 
![Tracked changes in database](http://medialeitwerk.de/files/trackedchanges.png "Tracked changes in database")

You can define a custom handler, if you want something different on a field change. This package provides the 
_DatabaseHandler_ (storing the changes in a table) and a _LogHandler_ (according to PSR-3).

<a name="quickstart"></a>
## Setup (Quickstart)

I recommend to use composer:

`composer require dsentker/watcher`

<sub>(If you do not want to use composer, download this package and add it to your PSR-4 autoloader.)</sub>
<hr />

To enable tracking of changes, you must pass the `\Watcher\EventListener\FlushListener` to the EventManager when creating the EntityManager:
```php
$listener = FlushListener::createWithHandler($handler);
$eventManager = new EventManager();

// this tracks changes on entities
$eventManager->addEventListener(array(Events::onFlush), $listener);

// provides methods to fetch changes directly from the entity 
$eventManager->addEventListener(array(Events::postLoad), new LoadListener());

$em = EntityManager::create($dbParams, $config, Watcher::createEventManager(new DatabaseHandler()));

// or, to simplify things:
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager($handler));
```

The `$handler` represents the instance from an object that is executed when a change is detected. Use the `\Watcher\UpdateHandler\DatabaseHandler` to save the changes to Doctrine in a separate table. Alternatively, you can also use the LogHandler (this expects a logger according to PSR in the constructor).

To enable support for the annotations, you must register them after the entity manger is created. The easiest way to do this is to use the `::registerAnnotations()` method:
```php
Watcher::registerAnnotations();
```
<a name="database"></a>
### Database setup
#### Database structure
If you are using the DatabaseHandler, a new table in your database is needed. Create the table using the entity_log.db.sql file in the `resources/` folder. 

#### Entity setup
Use Watcher\Entity\EntityLog as a template, extend it or copy it to your entity folder.

#### Repository and basic usage
This package has an `EntityLogRepository` to fetch changes related to an entity:
```php
/** @var EntityLogRepository $logRepo */
$logRepo = $em->getRepository(EntityLog::class);

/** @var EntityLog[] $changes */
$changes = $logRepo->getLogsFromEntity($user);

// Example: get latest change:
$lastChange = $changes[0];
echo vsprintf("Last updated at (%s): Changed %s to %s", [
    $lastChange->getChangedAt()->format('Y-m-d'),
    $lastChange->getOldValue(),
    $lastChange->getNewValue(),
]);
```

#### Get changed fields from entity
You can fetch the related changes directly from the entity:


The changed fields are injected to the entity itself, if the entity supports the Interface `Watcher\Entity\LogAccessor`:

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
As you can see, this Interface extends the `WatchedEntity` Interface, so there is no need to implement both interfaces.
For an easier usage you can use the `Watcher\Entity\LogAccessorTrait` which provides this methods.
  
<a name="handler"></a>
### Creating custom handler
You can also write your own handlers. The handler is executed when a field change was detected and persisted. This only has to implement the interface namespace `Watcher\UpdateHandler`:
```php
interface UpdateHandler
{

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity);

}
```
While `$changedField` contains all information about the changed field, The `$formatter` represents a converter class that transforms a non-scalar value to a string. If the value of a modified field is non-primitive (for example, a `DateTime` object, it must be converted to a string before persistence. A boolean value should also be output with "Yes" or "No".

<a name="formatter"></a>
### ValueFormatter
The `ValueFormatter` does the conversion of a field into a string. Practically, a default formatter is provided, which converts all typical data types to a string representation.

You can also create your own ValueFormatter, which must follow the Interface `Watcher\ValueFormatter`:
```php
interface ValueFormatter
{

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value);

}
```
Each ValueFormatter _must_ return a string.

#### Custom value formatter
To replace the DefaultFormatter with your own, you have to pass it to the `FlushListener` constructor:
```php
$formatter = new \Your\Own\Formatter();
$listener = new FlushListener($formatter);
$listener->pushUpdateHandler(...);
$eventManager = new EventManager();
$eventManager->addEventListener(array(Events::onFlush), $listener);
```

You can also use a custom formatter for a particular entity field. This is also useful to hide 
sensible or encrypted information (e.g. passwords) 
```php
/**
 * @Column(type="string", length=64)
 * @WatchedField(valueFormatter="\Watcher\ValueFormatter\ConcealFormatter")
 */
protected $password;
```

The valueFormatter property expects a full qualified classname. As you can see in this 
example, this package has also a `ConcealFormatter`, which only shows Asteriks (*) on each 
changed character. If no valueFormatter is definied for this field, 
the default formatter is used (the section above).

<a name="labels"></a>
### Setting labels
The names of the attributes in the entities are not always user-friendly, especially when the changes of respective field has to be displayed to the user. Therefore, you can optionally set a label to each field:
```php
/**
 * @Column(type="datetime", name="updated_at")
 * @WatchedField(label="Last updated")
 */
protected $updatedAt;
```

<a name="fullexample"></a>
## Full Example
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
]); // Last updated at 2017-09-07: Changed User name from 'John Doe' to 'A new username' 
```

<a name="limitations"></a>
## Known Limitations
* This package is able to track changes on single fields and associations (collections), but depends 
on the concept of Doctrine, [which is limited to track changes on fields on the **owning side**](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/unitofwork-associations.html). That means, that inverse side associations (`@OneToMany`) are NOT supported. `@ManyToMany` and `@ManyToOne` associations _are_ supported.
* Also consider the overhead. The change of each individual(!) field results in a single database query (if you use the `DatabaseHandler`). The change of 10 fully-watched entities with 10 fields generates an additional 100 database queries.

<a name="stuff"></a>
## Testing
TBD
 
## Credits
* [Daniel Sentker](https://github.com/dsentker)
 
## Submitting bugs and feature requests
Bugs and feature request are tracked on GitHub.
 
## ToDo
* Create a Symfony2 / Symfony3 bundle _(WIP)_
* Write tests
* Optimize performance (group changes)
 
## External Libraries
This library depends on Doctrine (surprise!) and subpackages.
 
## Copyright and license
_Watcher_ is licensed for use under the MIT License (MIT). Please see LICENSE for more information.

***

[TOC]