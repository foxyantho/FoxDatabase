<?php

namespace Fox\Database;

use Fox\Database\Connections\ConnectionFactory;
use InvalidArgumentException;


class DatabaseManager implements ConnectionResolverInterface
{

    /**
     * The database connection factory instance.
     *
     * @var \Fox\Database\Connections\ConnectionFactory
     */
    protected $factory;

    /**
     * Configurations container
     */
    protected $configurations;

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];


    /**
     * Create a new database manager instance.
     *
     * @param  $configurations
     * @param  ConnectionFactory  $factory
     * @return void
     */
    public function __construct( array $configurations, ConnectionFactory $factory )
    {
        $this->configurations = $configurations;

        $this->factory = $factory;
    }

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Fox\Database\Connections\Connection
     */
    public function connection( $name = null )
    {
        $name = $name ?: $this->getDefaultConnection();

        // If we haven't created this connection, we'll create it based on the config

        if( !isset($this->connections[$name]) )
        {
            $connection = $this->makeConnection($name);

            $this->connections[$name] = $connection;
        }

        return $this->connections[$name];
    }

    /**
     * Make the database connection instance.
     *
     * @param  string  $name
     * @return \Fox\Database\Connections\Connection
     */
    protected function makeConnection( $name )
    {
        $config = $this->getConfig($name);

        return $this->factory->make($config, $name);
    }

    /**
     * Reconnect to the given database.
     *
     * @param  string  $name
     * @return \Fox\Database\Connections\Connection
     */
    public function reconnect( $name = null )
    {
        $name = $name ?: $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->connection($name);
    }

    /**
     * Disconnect from the given database.
     *
     * @param  string  $name
     * @return void
     */
    public function disconnect( $name = null )
    {
        $name = $name ?: $this->getDefaultConnection();

        unset($this->connections[$name]);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getConfig( $name )
    {
        $name = $name ?: $this->getDefaultConnection();

        // To get the database connection configuration based of the given name.
        // If the configuration doesn't exist, we'll throw an exception.

        if( !isset($this->configurations[$name]) )
        {
            throw new InvalidArgumentException('Database "' . $name . '" not configured.');
        }

        return $this->configurations[$name];
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->configurations['database.default'];
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->connection(), $method], $parameters);
    }


}
