<?php

namespace Fox\database\connections;

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
     * Execute a query
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return int rowCount
     */
    public function execute( $query, array $bindings );

    /**
     * Return a single value
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return string
     */
    public function single( $query, array $bindings );

    /**
     * Return a row
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return object
     */
    public function row( $query, array $bindings );

    /**
     * Return all results
     * 
     * @param  string $query
     * @param  array  $bindings
     * @return array
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
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack();


}
