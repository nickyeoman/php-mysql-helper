# php-mysql-helper
mysql helper class for php 8


see: https://www.nickyeoman.com/blog/php/php-mysql-insert-from-array/

composer: https://packagist.org/packages/nickyeoman/php-mysql-helper

# Usage

```php
require_once 'vendor/autoload.php'; // Composer
USE Nickyeoman\Dbhelper;
$db = new Nickyeoman\Dbhelper\Dbhelp('127.0.0.1', 'username', 'thePassword', 'databaseName', '3306');
$db->close();
```
