EntityArchiverBundle handles automatic archiving or removal of 
doctrine entities in the application.

Installation:

```console
composer require fastbolt/entity-archiver-bundle
```

You will need to create an entity-archiver.yaml file in your config directory. See 'Configuration' below.

To use the command, you will need to add the following line to the bundles.php:
```php
Fastbolt\EntityArchiverBundle\EntityArchiverBundle::class => ['all' => true]
```

Execute by using this command:
```console
php bin/console entity-archiver:run
```

**Options:**

*--dry-run*
Will only display the results table, showing all entities from entity-archiver.yaml, the total 
number of entries in the origin table and the number of entities selected for archiving
                    
*--update-schema*
Will update the archive tables based on the configuration in entity-archiver.yaml


***Configuration***

Example:
```yaml
#entity-archiver.yaml
entity_archiver:
    table_suffix: archive
    entities:
        -   entity: App\Entity\Log
            strategy: archive
            filters:
                - {
                    type: age,
                    age: 1,
                    unit: months,
                    field: created_at
                }
            archivingDateFieldName: archived_at
            fields: [
                'id',
                'item_type',
                'item_id',
                'item_description',
                'item_action',
                'fk_client_id',
                'fk_user_id'
        ]
```

```
table_suffix           Suffix of the tables created by the bundle to hold the archived entities

addArchivedAt          Wether to add a field for the archiving date in the archive table
archivingDateFieldName Field name of the generated date field holding the date when the archiving was done, default 'archived_at'
strategy               What to do with the entity when it is selected to be archived
    - remove           Deletes the enitity using the 'id'-column
    - archive          Removes it from the original table and pastes a non-unique copy to the archive table
filters:            
    - type: age
        - age          integer         
        - unit         ("days"/"months"/"years")
        - field        Entity field that is used to determin the age of the entry (Datetime)
```

***Adding new Filters and Strategies:***
New filters need to implement the EntityArchivingFilterInterface, while strategies will need to implement the 
ArchivingStrategyInterface. Add the corresponding tag in the services.yaml.

```yaml
Fastbolt\EntityArchiverBundle\Filter\AgeArchivingFilter:
tags: [ 'fastbolt.archiver.filter' ]

Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy:
    tags: [ 'fastbolt.archiver.strategy' ]
```
