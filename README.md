
# Convert.php

PHP CLI application that converts XML files to CSV, vice versa.



## First Usage

Please call

```bash
$ composer install
```

to recreate dependencies.



## Usage

Example usage of convert.php:

```bash
$ php convert.php fromcsv sample.csv
```

Will generate a file `sample.csv.xml` in valid XML 1.0 format.


```bash
$ php convert.php fromcsv sample.csv --delimiter=";"
```

(Do not forget the quotes!)

Will generate a file `sample.csv.xml` from a CSV file which is separated by a semicolon.


```bash
$ php convert.php fromcsv sample.csv --save=custom.xml
```

Will generate a file `custom.xml` from a CSV file.

The command `fromxml` will work analogous.

## Documentation

You can generate a documentation of the class `Converter\Converter` by calling

```bash
$ php phpDocumentor.phar -f src/Converter/Converter.php -t docs
```

from the command line.

(Download from <http://phpdoc.org/phpDocumentor.phar>)

You can then find HTML docs inside the `docs` folder.
