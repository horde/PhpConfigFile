# horde/phpconfigfile

Read and write PHP config files with array or primitives data. Provide building blocks for a PHP native schema definition.

## Supported formats

Leading and trailing spaces are ignored
Opening and closing PHP tags are optional

### Classic Horde Format

```
<?php
$conf['critical_value'] = 'Code before the header is not deleted on write and can act as a default for missing values in the actual config';
/* Some Config File Header*/
$conf['critical_value'] = 'Everything in here is overwritten on write';
$conf['auth']['transparent'] = true;
$other_var = 'debug';
/* Some Config File Footer*/
$conf['critical_value'] = 'Code after the footer is not overwritten on write and can act as a temporary override to admin-provided config';
```

### Random Array Format

```
<?php
$data = [

  'key' => 'value',
  'key2' => [
               'nested => 'values', 
               'of_any' => ['possible' =>  'format' ],

            ],
];
```

### Primitives

```
<?php

$debug = true;
$max_errors = 23;
$initial_application_page = 'News';
?>
```



## Usage

See Unit Tests

## Schema Definition

Applications have expectations with regards to PHP config files but free form PHP allows to undermine these expectations.
The ConfigurationSchema classes provide building blocks for 
- Rendering defaults into a new or incomplete configuration file
- generating UIs from Code without external schema languages like XML, Yaml, etc
- versioning and upgrading configuration files in a defined fashion.

Schema definitions always begin with a root element ConfigurationSchema.
Every subsequent element is either a LeafElement or a ParentElement.
LeafElements hold primitive values such as integer, string, float, boolean or null.
A special type of leaf elements are void elements which do not emit anything to the configuration file but provide metadata for display in a UI assembled from the schema.
ParentElements hold levels of array keys
