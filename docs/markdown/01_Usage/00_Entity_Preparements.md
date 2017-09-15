## Entity Preparements

## Assign the `WatchedEntity` Interface
Entities that are to be tracked must implement the interface `\Watcher\Entity\WatchedEntity`. This is necessary to tell **Watcher** whether this entity is to be tracked at all. It also requests the _getId()_ method, which is necessary to assign the changes to a particular row. It is assumed that your entity provides this method anyway.

## Using the `@WatchedField` annotation
Mark your fields with the @WatchedField annotation. The listener now tracks all changes made to this property.

## Example
```php
namespace Example\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Watcher\Entity\WatchedEntity;
use Watcher\Annotations as Watch;
use Doctrine\ORM\Mapping as ORM;

/**
 * User Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="example_users")
 */
class User implements WatchedEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Watch\WatchedField
     */
    protected $username;

    // ...
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
}
```

In this example, all changes made to $username are handled with the UpdateHandler provided by the FlushListener (see Introduction: Setup).
