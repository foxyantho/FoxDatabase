<?php

namespace Fox\Database\Connections;

use Fox\Database\Interfaces\ConnectionInterface;

use PDO;
use Closure;
use Exception;


class Connection implements ConnectionInterface
{

    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Indicates whether queries are being logged.
     *
     * @var boolean
     */
    protected $loggingQueries = false;

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected $queryLog = [];



    /**
     * Create a new database connection instance.
     *
     * @param  \PDO     $pdo
     * @param  string   $database
     * @param  string   $tablePrefix
     * @return void
     */
    public function __construct( PDO $pdo, $database = '', $tablePrefix = '' )
    {
        $this->pdo = $pdo;

        // Setup the default properties

        $this->database = $database;

        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Replace a string with the database prefix, in query string
     * 
     * @param  string $query
     * @return string
     */
    protected function replacePrefix( $query )
    {
        return str_replace('@', $this->tablePrefix, $query);
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @return PDOStatement|false
     */
    protected function runQuery( $query, array $bindings = [] )
    {
        if ( $this->loggingQueries )
        {
            $start = microtime(true);
        }
        
        $query = $this->replacePrefix($query);

    //var_dump($query);die;

        $statement = $this->pdo->prepare($query);

        $statement->execute($bindings);

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.

        if ( $this->loggingQueries )
        {
            $time = $this->getElapsedTime($start);

            $this->logQuery($query, $bindings, $time);
        }

        return $statement;
    }

    /**
     * Execute an SQL statement and return the boolean result
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return boolean
     */
    public function statement( $query, array $bindings = [] )
    {
        return $this->runQuery($query, $bindings);
    }

    /**
     * Execute a query, and returns the number of rows affected by the statement 
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return int|false rowCount
     */
    public function execute( $query, array $bindings = [] )
    {
        $statement = $this->statement($query, $bindings);

        return ( $statement ? $statement->rowCount() : false );
    }

    /**
     * Return a single value
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return mixed|false
     */
    public function single( $query, array $bindings = [] )
    {
        $statement = $this->statement($query, $bindings);

        return ( $statement ? $statement->fetchColumn(0) : false );
    }

    /**
     * Return a row of results
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return object
     */
    public function row( $query, array $bindings = [] )
    {
        $statement = $this->statement($query, $bindings);

        return ( $statement ? $statement->fetch() : false );
    }

    /**
     * Return all results
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return array
     */
    public function all( $query, array $bindings = [] )
    {
        $statement = $this->statement($query, $bindings);

        return ( $statement ? $statement->fetchAll() : false );
    }

    /**
     * Return the last inserted id
     * 
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @throws \Exception
     * @return mixed
     */
    public function transaction( Closure $callback )
    {
        $this->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.

        try
        {
            $result = $callback($this);

            $this->commit();
        }

        // If we catch an exception, we will roll back so nothing gets messed
        // up in the database. Then we'll re-throw the exception so it can
        // be handled how the developer sees fit for their applications.

        catch( Exception $e )
        {
            $this->rollBack();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }


    /************************************************************************************/


    /**
     * Get the elapsed time since a given starting point.
     *
     * @param  int    $start
     * @return float
     */
    protected function getElapsedTime( $start )
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  $time
     * @return void
     */
    protected function logQuery( $query, $bindings, $time = null )
    {
        if ( !$this->loggingQueries ) return;

        $this->queryLog[] = compact('query', 'bindings', 'time');
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }


    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function pdo( $pdo )
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     * @return void
     */
    public function databaseName( $database )
    {
        $this->database = $database;
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string  $prefix
     * @return void
     */
    public function tablePrefix( $prefix )
    {
        $this->tablePrefix = $prefix;
    }


}
