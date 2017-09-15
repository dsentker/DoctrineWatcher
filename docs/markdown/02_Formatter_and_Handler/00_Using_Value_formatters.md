## Value Formatters

A Value formatter is invoked, when a changed field must be converted to a string representation. This is needed, when the value of a changed property is outputted for the user ("Change history"). 
If a string field (e.g. VARCHAR) is changed, a value formatter is in most of the cases obsolete: You can output "Field 'Username' was changed from 'John Doe' to 'Jane Doe'". 

But what about boolean values, arrays or objects?

If a boolean value is changed, it would be nice to output "Field 'User active' changed from 'Active' to 'Inactive'" instead of "[...] changed from '1' to '0'". Think about an category entity - without a Value formatter, the string representation of the changed field would be "User category changed from '[object]' to '[object]''". This is not very intuitive. A Value formatter replaces changed values to a readable string.

## Using the `\Watcher\ValueFormatter\DefaultFormatter`

The Default Formatter is used as default formatter, if no further configuration was done. This formatter converts value as following:

| Type                                          | Example value                     | Example Output                                 | Notes                                                                                                                                               |
|-----------------------------------------------|-----------------------------------|------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| string                                        | Hello World                       | Hello World                                    | String is splitted after 64 characters                                                                                                              |
| boolean                                       | 1                                 | Yes                                            |                                                                                                                                                     |
| NULL                                          |                                   | N/A                                            |                                                                                                                                                     |
| array                                         | ['Hello', 'World']                | Hello, World                                   | Values are formatted again and concatenated with commas                                                                                             |
| object                                        | \Example\UserEntity instance      | Jane Doe                                       | If a __toString() method is implemented, the returning value will be used. Otherwise, the fully-qualified namespace will be used (with get_class()) |
| datetime object                               |                                   | 2017-09-25 14:31:10                            | The method format() will be applied with the date format 'Y-m-d H:i:s'                                                                              |
| Entity Collection (@ManyToMany or @ManyToOne) | ArrayCollection of other entities | CustomerCategory, AdminCategory, GuestCategory | If the entities have a __toString() method implemented, this value will be glued with a comma (see array)                                           |

## Create own value formatters
If you create your own formatter, you only have to remember that this class has to implement the following method:
```php
namespace Watcher;

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
The formatValue() method _must_ return a string.

If you change the formatting method for a particular data type, you can extend the DefaultFormatter class and overwrite the related method. Inspect the methods from the DefaultValueFormatter and override as you like:
```php
namespace Example;

use Watcher\ValueFormatter\DefaultFormatter;

class NoTimeFormatter extends DefaultFormatter {

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return string
     */
    protected function formatDateTime(\DateTimeInterface $dateTime)
    {
        return $dateTime->format('Y-m-d');
    }

}
```

## Change the default value formatter
Now that you've created your own formatter, you might want to use it as default formatter. To achieve this, assign it to the constructor from the FlushListener:

```php
$listener = new FlushListener(new NoTimeFormatter());
$listener->pushUpdateHandler($handler);
$eventManager = new EventManager();
$eventManager->addEventListener(array(Events::onFlush), $listener);
```

## Specify an custom formatter for a single entity field
If you want to use your formatter for a particular field, you can assign it with the property annotation:

```php
/**
 * @ORM\Column(type="datetime", name="updated_at")
 * @Watch\WatchedField(valueFormatter="\Example\NoTimeFormatter")
 */
protected $updatedAt;
```
Remember to set the full qualified namespace.

## Hiding sensitive data
A common usage for custom formatters is the hiding of sensible data, e.g. passwords. This package has the \Watcher\ValueFormatter\ConcealFormatter, which does nothing more than mask all characters.

```php
/**
 * @ORM\Column(type="string", length=64)
 * @Watch\WatchedField(valueFormatter="\Watcher\ValueFormatter\ConcealFormatter")
 */
protected $password;
```