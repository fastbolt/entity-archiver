EntityArchiverBundle handles automatic archiving or removal of 
doctrine entities in the application.

Installation:

```console
composer require fastbolt/entity-archiver-bundle
```

You will need to create an entity-archiver.yaml file in your config directory.

To use the command, you will need to add the following line to the bundles.php:
```php
Fastbolt\EntityArchiverBundle\EntityArchiverBundle::class => ['all' => true]
```

Execute by using this command:
```console
php bin/console entity-archiver:run
```

Options:
--dry-run           Will only display the results table, showing all entities from entity-archiver.yaml, the total 
                    number of entries in the origin table and the number of entities selected for archiving
--update-schema     Will update the archive tables based on the configuration in entity-archiver.yaml


***Configuration***

Example:
```yaml
#entity-archiver.yaml
table_suffix: _archive
entities:
  App\Entity\Article:
    strategy: archive
    filter:
      - {
        type: age,
        age: 14,
        unit: days,
        field: createdAt
      }
```


table_suffix           Name of the table created by the bundle to hold the archived entities
strategy               What to do with the entity when it is selected to be archived
    - remove           Deletes the enitity using the 'id'-column
    - archive          Removes it from the original table and pastes a non-unique copy to the archive table

filter:            
    - type: age
        - age          integer         
        - unit         ("days"/"months"/"years")
        - field        Entity field that is used to determin the age of the entry (Datetime)


Adding new Filters and Strategies:
New filters need to implement the EntityArchivingFilterInterface, while strategies will need to implement the 
ArchivingStrategyInterface. Add the corresponding tag in the services.yaml.

```yaml
Fastbolt\EntityArchiverBundle\Filter\AgeArchivingFilter:
tags: [ 'fastbolt.archiver.filter' ]

Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy:
    tags: [ 'fastbolt.archiver.strategy' ]
```
