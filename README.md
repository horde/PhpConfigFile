# horde/phpconfigfile

Read and write PHP config files with array or primitives data.

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