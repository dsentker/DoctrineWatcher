## Setup
When creating the entity manager, you have to pass the EventManger (provided by doctrine) as third parameter.

First, create a new instance for the event manager. This event manger has to listen to onFlush-Events:

```php
$eventManager = new EventManager();
$eventManager->addEventListener(array(Events::onFlush), $listener);
```

The $listener must be the FlushListener provided by this package. The easiest way to create an instance is to call

```php
$listener = \Watcher\EventListener\FlushListener::createWithHandler($handler);
```

This method expects an handler which is called when an entity change is detected. The handler must follow the \Watcher\UpdateHandler Interface. In this example, we use the DatabaseHandler, which writes the changes to a separate table. All you need is a new instance to \Watcher\UpdateHandler\DatabaseHandler;

## Register annotations
**Watcher** works with custom annotations. Since these are unknown to doctrine, they must be registered. Call `Watcher::registerAnnotations();` once per runtime to register the annotations before you use your entity manager.

### Don't make use of the SimpleAnnotationReader
**Important:** Doctrine only accepts custom annotations if the "Simple mode" is disabled. When you create your Doctrine Configuration, you have to use the AnnotationDriver to enable the package's own annotations:
```php
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$config->setMetadataDriverImpl(new AnnotationDriver(
    new CachedReader(new AnnotationReader(), new ArrayCache()),
    $paths
));``` 

***

### Setup example   
```php
// Create the flush listener with the DatabaseHandler
$handler = new DatabaseHandler();
$listener = FlushListener::createWithHandler($handler);

// Create the event manager
$eventManager = new EventManager();

// Map onFlush-events to your FlushListener
$eventManager->addEventListener(array(Events::onFlush), $listener);

// Optional: add the LoadListener to your event manager if you want to access changed fields directly from your entity (more on that later) 
$eventManager->addEventListener(array(Events::postLoad), new LoadListener());

// Obtain an entity manager - check doctrine docs if you get stuck
$dbParams = ...
$config = ...
$em = EntityManager::create($dbParams, $config, $eventManager);

Watcher::registerAnnotations();

// or, to simplify things:
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager(new DatabaseHandler());
Watcher::registerAnnotations();
```

Now you are ready :)