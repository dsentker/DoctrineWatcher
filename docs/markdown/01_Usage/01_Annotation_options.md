## Annotation options
### Label your fields
When implement the @WatchedField annotation, you are able to specify the way you want persist the changes. With the label option, you can label this field if the property naming is not user friendy (or does not meet your expectations).

```php
/**
 * @ORM\Column(type="boolean")
 * @Watch\WatchedField(label="Is user active?")
 */
protected $active;
```

The label of the entity field is processed with the UpdateHandler and helps to describe, which detail from the entity got changed.

### Use custom ValueFormatter
As described before, you've already passed an ValueFormatter when creating the EventManager. In some cases you need a special formatter, e.g. in cases when a password or some other privacy data must be concealed.

```php
/**
 * @ORM\Column(type="string", length=64)
 * @Watch\WatchedField(valueFormatter="\Watcher\ValueFormatter\ConcealFormatter")
 */
protected $password;
```

When assign a custom value formatter, make sure to specify the fully-qualified class name (with namespace).