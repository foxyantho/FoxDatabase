<?php

namespace Fox\Database\Interfaces;

use PDO;
use Closure;


interface ConnectionInterface
{

    /**
     * Execute an SQL statement and return the boolean result
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return boolean
     */
    public function statement( $query, array $bindings );

    /**
     * Execute a query, and returns the number of rows affected by the statement 
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return int|false rowCount
     */
    public function execute( $query, array $bindings );

    /**
     * Return a single value
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return mixed|false
     */
    public function single( $query, array $bindings );

    /**
     * Return a row of results
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return array|\stdClass|false
     */
    public function row( $query, array $bindings );

    /**
     * Return all results
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return array|\stdClass|false
     */
    public function all( $query, array $bindings );

    /**
     * Return the last inserted id
     * 
     * @return string
     */
    public function lastInsertId();

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @throws \Exception
     * @return mixed
     */
    public function transaction( Closure $callback );

    /**
     * Start a new database transaction.
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     */
    public function rollBack();

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog();

    /**
     * Clear the query log.
     */
    public function flushQueryLog();

    /**
     * Enable the query log on the connection.
     */
    public function enableQueryLog();

    /**
     * Disable the query log on the connection.
     */
    public function disableQueryLog();

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging();

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo();

    /**
     * Set the PDO connection.
     *
     * @param  \PDO  $pdo
     * @return $this
     */
    public function pdo( PDO $pdo );

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName();

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     */
    public function databaseName( $database );

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix();

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string  $prefix
     */
    public function tablePrefix( $prefix );



}
