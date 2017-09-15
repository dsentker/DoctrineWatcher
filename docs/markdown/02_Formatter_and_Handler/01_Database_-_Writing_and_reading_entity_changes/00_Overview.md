## Writing changes to database and read changes from database
_Please note that this chapter is only useful for you if you used the DatabaseHandler._

## The EntityLog entity class
Changes made to an entity are represented in the EntityLog entity. A ready-to-use entity is available with `\Watcher\Entity\EntityLog`. Keep in mind that this class only provides basic usage fields (as described with the table schema as seen in _Introduction: Requirements and Installation_)

The EntityLog provides methods to get information about
* Which field was changed?
* What was the old value?
* What is the new value?
* When was it changed? 

A common use-case is to display all changes made to an specific entity. 
There are two ways to get EntityLogs for a given entity.