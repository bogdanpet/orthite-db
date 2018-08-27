# orthite-db
PDO Wrapper for easier database access

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
    'database' => 'database_name'
];

$db = new \Orthite\Database\Database($conn);
```
This case is useful when the connection details are stored in some kind of configuration files.


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
