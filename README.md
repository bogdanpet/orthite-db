# orthite-db
PDO Wrapper for easier database access. It is prototype phase and currently only supports mysql. It is possible that some features will work with postgres or sqlite but full support for these drivers will be added later.

## Table of contents

  * [Installation](#installation)
  * [Creating connection](#creating-connection)
      - [Creating connection using dsn, user and password strings](#creating-connection-using-dsn-user-and-password-strings)
      - [Recycling existing PDO connection](#recycling-existing-pdo-connection)
      - [Creating connection using array](#creating-connection-using-array)
      - [Creating child class and configure connection.](#creating-child-class-and-configure-connection)
  * [CRUD operations](#crud-operations)
      - [Insert](#insert)
      - [Select](#select)
        * [Limiting results](#limiting-results)
        * [Joining other tables in select](#joining-other-tables-in-select)
        * [Grouping and ordering results](#grouping-and-ordering-results)
      - [Where conditions](#where-conditions)
        * [Other comparisons](#other-comparisons)
      - [Update](#update)
      - [Delete](#delete)
  * [Raw queries execution](#raw-queries-execution)
      - [Secure execution of queries](#secure-execution-of-queries)
        * [Custom placeholders with data type specification](#custom-placeholders-with-data-type-specification)
  * [Migrations](#migrations)

## Installation

Install package via composer using command:
```
composer require bogdanpet/orthite-db
```

## Creating connection

#### Creating connection using dsn, user and password strings
Creating connection is the same as creating a new PDO connection using dsn, user and password strings.
```php
$db = new \Orthite\Database\Database($dsn, $user, $password)
```


#### Recycling existing PDO connection
If there is already an existing PDO connection it can be passed to Orthite's Database class constructor.
```php
$pdo = new \PDO($dsn, $user, $password);

.  .  .

$db = new \Orthite\Database\Database($pdo);
```


#### Creating connection using array
Database object can also be made using array containing connection details: driver, host, port, user, password and database.
```php
$conn = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'password' => 'secret',
    'database' => 'database_name',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci'
];

$db = new \Orthite\Database\Database($conn);
```
This case is useful when the connection details are stored in some kind of configuration files. Required arguments are database, user and password. Other arguments have default values:
* driver defaults to `mysql`
* host defaults to `localhost`
* port defaults to `3306`
* charset defaults to `utf8`
* collation defaults to `utf8_unicode_ci`

#### Creating child class and configure connection.
If the same connection is used accross whole application, but database object requires to be set in multiple places it is good to create a child Database class and make connection persistent by overriding the connection property.
```php
<?php

namespace App;

class Database extends \Orthite\Database\Database
{

    protected $connection = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => 'secret',
        'database' => 'database_name'
    ];
}
```
Or maybe the connection details should be returned from some configuration file like [dotenv](https://github.com/vlucas/phpdotenv)
```php
<?php

namespace App;

class Database extends \Orthite\Database\Database
{

    public function __construct() {
        $this->connection['driver'] = getenv('DB_DRIVER');
        $this->connection['host'] = getenv('DB_HOST');
        $this->connection['port'] = getenv('DB_PORT');
        $this->connection['user'] = getenv('DB_USER');
        $this->connection['password'] = getenv('DB_PASSWORD');
        $this->connection['database'] = getenv('DB_NAME');
        
        parent::_construct();
    }
}
```
Then the child class should just be instantiated.
```php
$db = new App\Database();
```

## CRUD operations

#### Insert
To insert a record in database table, call the insert method and pass the table name and array of data to insert. Array keys must match the column names. Let's say that we have users table with columns id, first_name, age, email, created_at, updated_at and id is auto increment primary key and updated_at is nullable. To insert a record:
```php
$data = [
    'first_name' => 'Anika',
    'age' => 28,
    'email' => 'anika@example.com',
    'created_at' => date()
];

$db->insert('users', $data);
```
To insert multiple records at once use the insertMany() method and pass array of arrays (each array represent one record).
```php
$data = [
    [
        'first_name' => 'Anika',
        'age' => 28,
        'email' => 'anika@example.com',
        'created_at' => date()
    ],
    [
        'first_name' => 'Bob',
        'age' => 29,
        'email' => 'bob@example.com',
        'created_at' => date()
    ]
];

$db->insertMany('users', $data);
```
Insert many method returns the number of successfully inserted records.


#### Select

Use select() method to fetch the data from the database. Required parameter is table name.
```php
$users = $db->select('users');
```
This will fetch all the records from users table with all columns into the associative array. To fetch only some columns, pass the array of column names as second parameter.
```php
$users = $db->select('users', ['name', 'email']);
```
This will fetch only name and email columns.

Optionally, as a third parameter fetch style can be passed. Default is FETCH_ASSOC. See [PDO fetch styles](http://php.net/manual/en/pdostatement.fetch.php)

##### Limiting results
Use limit() method before select to limit the number of fetched records.
```php
$users = $db->limit(10)->select('users');
```
This will return first 10 records from the database. Limit method can accept a second argument which will define a 'chunk' and it is 1 by default. So, to fetch users from second chunk (from 11th to 20th row in database) use:
```php
$users = $db->limit(10, 2)->select('users');
```
##### Joining other tables in select
Another tables can be joined before select using innerJoin() (or just join() which is alias of inner join), leftJoin(), rightJoin(), fullJoin().
```php
$users = $db->join('cities', 'city_id', 'id')->select('users');
```
This will inner join table cities on condition `users.city_id = cities.id`. Other types of joins works the same.

Since the select() method must be the last in the chain it can feel unnatural for some to first set the joined table and then the main table. In that case a table() method can be used and same results as above can be achieved with the following.
```php
$users = $db->table('users')->join('cities', 'city_id', 'id')->select();
```

##### Grouping and ordering results
Use groupBy() method and pass a single column as string or multiple columns as array to generate `GROUP BY' declaration.
```php
$users = $db->groupBy('age')->select('users');
```
This will generate query `SELECT * FROM users GROUP BY age`

Like grouping, ordering is also possible with orderBy() method.
```php
$users = $db->orderBy(['age', 'first_name'])->select('users');
```
Generated query is `SELECT * FROM users ORDER BY age, first_name`. For ordering it is possible to pass ASC or DESC separated with pipe '|' symbol.
```php
$users = $db->orderBy(['age|ASC', 'first_name|DESC'])->select('users');
```
Generated query is `SELECT * FROM users ORDER BY age ASC, first_name DESC`
#### Where conditions
Before taking a look at update and delete operations let's take a look at where conditions first. To add the where condition use general where() method.
```php
$user = $db->where('id', 3)->select('users');
```
First parameter is column name, second is value. Additionally, third parameter is comparator and defaults to '=', but can be '<', '>', '<=' or '>='. Also as a fourth parameter keyword 'WHERE' is passed, but can be changed to 'AND' or 'OR' for adding multiple conditions.
```php
$user = $db->where('id', 3)->where('age', 18, '>=', 'AND')->select('users');
```
This will generate `WHERE id=3 AND age >= 18`. This is possible but instead of changing third and fourth parameter it is recommended and much more intuitive to use wrapper methods:

* **and()** - where() with 'AND'
* **or()** - where() with 'OR'
* **whereGreaterThan(); andGreaterThan(); orGreaterThan()** - corresponding functions with '>' comparator
* **whereLessThan(); andLessThan(); orLessThan()** - corresponding functions with '<' comparator
* **whereGreaterOrEquals(); andGreaterOrEquals(); orGreaterOrEquals()** - corresponding functions with '>=' comparator
* **whereLessOrEquals(); andLessOrEquals(); orLessOrEquals()** - corresponding functions with '<=' comparator

So the query above is much more readable when written like this:
```php
$user = $db->where('id', 3)->andGreaterOrEquals('age', 18)->select('users');
```

For even more readability it is possible to use dynamic methods, where column name is injected in method name in camel case immediately after 'where', 'and' or 'or' keywords, and only the value is passed as argument.
```php
$user = $db->whereId(3)->andAgeGreaterOrEquals(18)->select('users');
```
For two or more words column names with underscores like 'first_name' use camel case whereFirstName().

##### Other comparisons
Orthite-db also provides whereLike(), andLike() and orLike() methods. For example:
```php
$users = $db->whereFirstNameLike('Ani%')->select('users');
```
Will generate `WHERE first_name LIKE 'Ani%'` which will fetch users with first name starting with 'Ani'.

Same as like, there are methods for IN comparison: whereIn(), andIn(), orIn() which accepts an array of values to compare.
```php
$users = $db->whereIdIn([1, 2, 3])->select('users');
```
Generates `WHERE id IN (1, 2, 3)` and fetches users with ids 1, 2 and 3.

And finally, for 'BETWEEN' comparisons there are whereBetween(), andBetween() and orBetween() methods which accepts two value parameters. Example:
```php
$users = $db->whereIdBetween(1, 10)->select('users');
```
This will generate `WHERE id BETWEEN 1 and 10`.

#### Update
To update the records in database use the update() method which accepts same parameters as insert(): table name and data. And don't forget to use it combined with where conditions if you don't want to update all the rows in a table.
```php
$data = [
    'email' => 'anika@example.com'
];

$db->whereFirstName('Anika')->update('users', $data);
```

#### Delete
To delete a row(s) in a table use delete() method with table name as argument combined with where condition.
```php
$db->whereId(8)->delete('users');
```

## Raw queries execution
Methods provided for crud operations cannot satisfy more complex operations so it is possible to write and execute the raw query using methods:

**raw()** which returns PDO statement.
```php
$stmt = $db->raw('SELECT * FROM users WHERE id < 100');
```
**rawFetch()** which is used for SELECT queries and instead returning the statement returns the fetched data.
```php
$users = $db->rawFetch('SELECT * FROM users WHERE id < 100');
```

#### Secure execution of queries
Instead of running raw queries, with user inputed data, to prevent SQL injection run execute() method which prepares the statement and execute it with given data. First parameter is query with placeholders and second is array of data. Example with positional placeholders:
```php
$stmt = $db->execute('SELECT * FROM users where id = ? and first_name = ?', [$id, $first_name]);
```
Example with named placeholders:
```php
$stmt = $db->execute('SELECT * FROM users where id = :id and first_name = :first_name', [
    ':id' => $id,
    ':first_name' => $first_name
]);
```

##### Custom placeholders with data type specification
orthite-db provides possibility to specify data type in placeholder for more secure queries. possible types are **s**, **i**, **b**, **l** which corresponds to [PDO param types](http://php.net/manual/en/pdo.constants.php) for strings, integers, booleans and lobs. Custom positional placeholders are written with question mark and corresponding letter '**?i**'. Named placeholders are written like '**s:first_name**'. It is not possible to mix named and positional placeholders in the same query. Examples:
```php
$stmt = $db->execute('SELECT * FROM users where id = ?i and first_name = ?s', [
    ':id' => $id,
    ':first_name' => $first_name
]);
```
```php
$stmt = $db->execute('SELECT * FROM users where id = i:id and first_name = s:first_name', [
    ':id' => $id,
    ':first_name' => $first_name
]);
```

## Migrations
Orthite-db also provides possibility to write migration scripts using migrate() method. First parameter of migrate() method is table name and second is callable containing column definitions. Callable accepts $schema argument which is used to define column structure. One simple migration script should look like this:
```php
use Orthite\Database\Migrations\SchemaInterface;

$db->migrate('cities', function (SchemaInterface $schema) {
    $schema->integer('id')->unsigned()->autoIncrement()->primary();
    $schema->string('name', 50);

    return $schema;
});

$db->migrate('users', function (SchemaInterface $schema) {
    $schema->increments(); // Alias of $schema->integer('id')->unsigned()->autoIncrement()->primary();
    $schema->string('username', 40)->unique();
    $schema->string('name', 30)->nullable();
    $schema->double('score')->default('0.00');
    $schema->integer('city_id')->unsigned()->foreign('cities', 'id');

    /*
     * Adds created_at, updated_at and deleted_at columns
     * Same as:
     * $this->timestamp('created_at');
     * $this->datetime('updated_at')->nullable();
     * $this->datetime('deleted_at')->nullable();
     */
    $schema->timestamps();

    return $schema;
});
```
Migration script above will create two tables: cities and users which have city_id foreign key referencin id of cities table.

So, as seen in example above, on $schema object first call a method which defines column and pass the column name, and then chain optional constraint methods. $schema object must be returned at the end of migration script. Currently supported column methods are:

* **string($column, $length = 255)** - VARCHAR column
* **text($column)** - TEXT column
* **binary($column)** - BLOB column
* **integer($column, $size = 4)** - INT column
* **double($column, $size = 4, $decimals = 2)** - DOUBLE column
* **decimal($column, $size = 4, $decimals = 2)** - DECIMAL column
* **bool($column)** - TINYINT(1) column
* **date($column)** - DATE column
* **datetime($column)** - DATETIME column
* **timestamp($column)** - TIMESTAMP column
* **time($column)** - TIME column
* **year($column)** - YEAR column

Available constraints methods are:
* **nullable()** - removes NOT NULL from column
* **unique()** - add UNIQUE constraint
* **primary()** - set as PRIMARY KEY
* **foreign($refTable, $refColumn)** - add FOREIGN KEY constraint
* **check($condition)** - add CHECK constraint
* **default($value)** - add DEFAULT value
* **index()** - set as INDEXED column
* **unsigned()** - set integer as UNSIGNED
* **autoIncrement()** - set column as AUTO_INCREMENT

Wrapper methods:
* **increments($column = 'id')** - integer($column)->unsigned()->autoIncrement()->primary();
* **timestamps()** - creates three columns: created_at TIMESTAMP, updated_at nullable DATETIME, deleted_at nullable DATETIME
