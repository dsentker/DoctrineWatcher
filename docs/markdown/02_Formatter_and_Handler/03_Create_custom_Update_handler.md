## Create custom update Handler

If the two handlers do not meet your expectations, you can (of course) create your own. If you look at the interface (`\Watcher\UpdateHandler`), you see that this is easy:

```php
interface UpdateHandler
{

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity);

}
```
The method UpdateHandler::handleUpdate() is called for each field individually. Grouping is not possible since doctrines flush process can be executed several times during the runtime.

The three parameters should be self-explanatory:
* The **ChangedField** instance is a non-persistent domain object which contains methods to receive the following property details:
  * The field _name_ (is equal to the property name from your entity)
  * The field _label_ (a user-friendly representation from your entity field)
  * The old value*
  * The new value*
* The **ValueFormatter** handles the job for the upcoming string conversion. It was set when creating the FlushListener OR overridden in your entity (see "Usage" > "Annotation options" for more information)
* **WatchedEntity** is the entity which is related to the changed field 
  
  
<small>*) Keep in mind that this values are not "stringified". Have a look at the ValueFormatter for further information.</small>

With this in mind, you can create your own handler. Let's say you want to receive an SMS on your mobile phone when a a user changes his email address:

Create a new class and dont forget to implement the interface \Watcher\UpdateHandler:
```php
namespace Example;

use Watcher\ChangedField\ChangedField;
use Watcher\Entity\WatchedEntity;
use Watcher\UpdateHandler;
use Watcher\ValueFormatter;

class SmsHandler implements UpdateHandler {

    public function handleUpdate(ChangedField $changedField, ValueFormatter $formatter, WatchedEntity $entity)
    {

        if(($entity instanceof \Example\Entity\User) && ('emailAddress' == $changedField->getFieldName())) {

            $newEmailAddress = $formatter->formatValue($changedField->getNewValue());
            $message = sprintf('Wohoo! %s has changed his email adress to "%s"!', $entity->getUsername(), $newEmailAddress);

            SomeSmsSenderService::send($message);
        }

    }

}
```

The last step is to assign your new handler within the configuration:

```php
$updateHandler = new SmsHandler();
$em = EntityManager::create($dbParams, $config, Watcher::createEventManager($updateHandler));
```