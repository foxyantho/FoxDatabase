<?php

namespace Fox\Database;

use Fox\Database\Connections\ConnectionFactory;
use Fox\Database\ConnectionRetrieveResolverTrait as ConnectionRetrieveResolver;

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
    protected $configurations = [];

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];


    /**
     * Get default application settings
     * 
     * @return array
     */
    protected static function getDefaultSettings()
    {
        return [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'database',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'prefix'    => ''
        ];
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    protected static function getDefaultConnectionName()
    {
        return 'default';
    }

    /**
     * Create a new database manager instance.
     *
     * @param  $configurations
     * @param  ConnectionFactory  $factory
     * @return void
     */
    public function __construct()
    {
        // build the connection factory

        $this->factory = new ConnectionFactory;

        // bootstrap ConnectionRetrieveResolver so it is ready for usage anywhere

        ConnectionRetrieveResolver::setConnectionResolver($this);
    }

    /**
     * Register a connection with the manager.
     * 
     * @param array  $settings 
     * @param string $name
     */
    public function addConnection( array $settings, $name = null )
    {
        $name = $name ?: static::getDefaultConnectionName();

        $this->configurations[$name] = array_merge(static::getDefaultSettings(), $settings);
    }

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Fox\Database\Connections\Connection
     */
    public function connection( $name = null )
    {
        $name = $name ?: static::getDefaultConnectionName();

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

        return $this->factory->make($config);
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
        $name = $name ?: static::getDefaultConnectionName();

        // To get the database connection configuration based of the given name.
        // If the configuration doesn't exist, we'll throw an exception.

        if( !isset($this->configurations[$name]) )
        {
            throw new InvalidArgumentException('Database "' . $name . '" not configured.');
        }

        return $this->configurations[$name];
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
