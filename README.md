# php-mysql-helper
mysql helper class for php 8


see: https://www.nickyeoman.com/blog/php/php-mysql-insert-from-array/

composer: https://packagist.org/packages/nickyeoman/php-mysql-helper

# Usage

## Connect

Here is how you connect to a database.

```php
require_once 'vendor/autoload.php'; // Composer
USE Nickyeoman\Dbhelper;
$db = new Nickyeoman\Dbhelper\Dbhelp('127.0.0.1', 'username', 'thePassword', 'databaseName', '3306');
$db->close();
```

## Update a row

```php
$array = array(
  'id'      => 'unique_key',
  'content' => 'data value'
)
$id = 'id'; // The key of the array to use as the lookup
$db->update("name_of_db_table, $array, $id);
```
