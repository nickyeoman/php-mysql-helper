# Find One

Behind the scene this is just a wrapper function for the findall method that statically adds a limit 1.
Basically it just reduces the number of parameters you use in your code.
It returns every column, which might not be idea for your use case.

```php
$this->db->findone($table, $col, $match);
```

## Parameters

There are three Parameters for this function.

### table

The table name of the database.

### col
The column you want to match.

```
'id'
```

### match

match is the part after 'LIKE' in the SQL statement.

```SQL
`col` LIKE '$match'"
```
