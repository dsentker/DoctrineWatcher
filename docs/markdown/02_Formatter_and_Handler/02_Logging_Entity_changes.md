## Logging value changes from Entities

If you do not want to persist the changes to a database, you can log the changes instead. It's your decision if you want to write the changes to a log file, send changes via email, or [any other log handler](https://seldaek.github.io/monolog/doc/02-handlers-formatters-processors.html). 

We recommend to choose [Monolog](https://github.com/Seldaek/monolog), but you can use any PSR-3-Logger.

### Preparing the LogHandler
When constructing your event manager, you have to pass the `\Watcher\UpdateHandler\LogHandler` to the FlushListener. The LogHandler constructor expects an instance of an `\Psr\Log\LoggerInterface`:

```php
// Create a PSR-3-Logger
$logger = new Logger('entity-logger');
$logger->pushHandler(new StreamHandler('path/to/your.log'));


// [1] Detailed setup:
$listener = new FlushListener();
$listener->pushUpdateHandler(new LogHandler($logger));

$eventManager = new EventManager();
$eventManager->addEventListener(array(Events::onFlush), $listener);
$eventManager->addEventListener(array(Events::postLoad), new LoadListener());

$em = EntityManager::create($dbParams, $config, $eventManager);


// [2] Short setup:
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager(new LogHandler($logger)));
```