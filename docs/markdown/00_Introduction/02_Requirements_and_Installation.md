## Requirements
This library requires PHP > 5.5 and Doctrine > 2.1. A composer setup is recommened.

## Installation
Install this package via composer:
`$ composer require dsentker/watcher`

Alternatively, you can download this library and connect it with any PSR-compatible autoloader. 
Add the namespace `\Watcher` and map it to the directory `[watcher-patch]/src/Watcher/`.

## Database setup
If you want to persist the field changes to database, you must create a table in your database. The basic table structure looks like this:

```sql
CREATE TABLE IF NOT EXISTS `entity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `changed_at` datetime NOT NULL,
  `entity_class` varchar(150) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `field` varchar(50) NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `old_value` varchar(128) NOT NULL,
  `new_value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

The fields of the table should be self-explanatory. This package provides an entity class to work with the items from the table (more on that in next chapters).

If you write your custom UpdateHandler, it is possible to fill different columns of the table (e.g. `user_id` to check which user changes which field).