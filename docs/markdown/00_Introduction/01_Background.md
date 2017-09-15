## Background
To track the changes to the entities, DoctrineWatcher uses the Doctrine event system, which provides useful 
information about the changes to entities. The following chapters describe how to configure the automation.

Once this library detects a change to a Doctrine entity, two components are called: The _ValueFormatter_ and the _UpdateHandler_.

## Update Handler
You can use an **UpdateHandler** to determine which action is carried out with the change.

This package provides three update handlers:
* The DatabaseHandler, which writes the changes directly to a database table
* The LogHandler, which passes the changes to a PSR3-compatible logger 
* The NullHandler (surprisingly does nothing)

Of course you can also write your own UpdateHandler.

***

## ValueFormatter
The **ValueFormatter** is used to transform the changed elements of the entities into a string. This is useful when boolean values or associated entities (ManyToMany or ManyToOne) must be converted into a readable string.
Watcher provides a standard formatter (**DefaultFormatter**) that covers all basic data types. In addition, a **ConcealFormatter** is provided - this masks the changes and can be used for password changes or other sensitive topics.

You can add custom formatters on your own.