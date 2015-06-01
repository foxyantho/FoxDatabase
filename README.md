# FoxDatabase

Simple stack including : database connections & management, model and a query-string builder.

Still in beta, use at your own risk! MySQL and derivates only,
it could works under different DBMS, but no guarantees.


## Usage :

Look at classes for more informations.

Create a database connection :

```PHP
use Fox\Database\DatabaseManager;

$config = [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => 'helloworld',
    'charset'   => 'utf8',
    'prefix'    => 'xx_',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
];

$m = new DatabaseManager;

$m->addConnection($config); // 'default'

$m->addConnection($config2, 'myconnection');
```


## Models

Basic usage :

```PHP
use Fox\Database\Model;

class Article extends Model
{
    protected $connection = 'myconnection'; // optional or 'default'

    protected $table = 'table_name'; // optional or @class'name

    protected $primaryKey = 'other_id'; // optional or 'id'


    public function sayHello()
    {
        if( $this->exist )
        {
            echo 'I exist and my PK is : ' . $this->getKey();
        }
    }

}


$article = new Article([
    'title'   => 'My Awesome Title',
    'content' => '...'
]);

$lastid = $article->save();


$article2 = Article::create([
    'title'   => 'My Awesome Title',
    'content' => '...'
]);

$article2->content = 'new stuff';

$update = $article2->save();


$article3 = Article::on('connection2')->find( $id = 1 );

$affected = $article3->delete();
```


# Query log :

First, set config['querylog'] config to `true`.

```PHP
$m = new DatabaseManager;

$m->addConnection($config, 'myconnection');

$logs = $m->connection('myconnection')->getQueryLog();


foreach( $logs as $query )
{
    echo 'Query: ' . $query['query'];

    echo 'Bindings: ' . json_encode($query['bindings']);

    echo 'Process time: ' . $query['time'];
}
```


## QueryBuilder

Basic CRUD :

( note : '@' will be replace by config[prefix] )

```PHP
use Fox\Database\QueryBuilder;

$single = (new QueryBuilder() )
                ->select([
                    'title',
                    'content'
                ])
                ->max('comments')
                ->selectFunction('COUNT', 'comments')
                ->from([
                    '@article',
                    '@comments'
                ])
                ->where([
                    'id',
                    'authord'
                ])
                ->groupBy('id')
                ->having('date > 2014')
                ->orderBy('date')
                ->limit(10)
                ->offset(5)
                ->execute([
                    'id' => 1,
                    'author' => 'John Doe'
                ]);

$result = (new QueryBuilder() )
                ->update('@article')
                ->set([
                    'title' => '"New Title"',
                    'content'
                ])
                ->execute([
                    'content' => '...']
                );

$affected = (new QueryBuilder() )
                ->delete()
                ->from('@article')
                ->execute();

$lastid = (new QueryBuilder() )
                ->insert()
                ->into('@article')
                ->values([
                    'title' => '"Title"',
                    'content'
                ])
                ->execute([
                    'content' => '...'
                ]);
```

