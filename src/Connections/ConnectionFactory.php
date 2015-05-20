<?php

namespace Fox\Database\Connections;

use PDO;
use Fox\Database\Connectors\MySqlConnector;
use InvalidArgumentException;


class ConnectionFactory
{

    /**
     * Configurations container
     */
    protected $configurations;

    /**
     * Create a new connection factory instance.
     *
     * @param  $configurations
     */
    public function __construct( $configurations )
    {
        $this->configurations = $configurations;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param  array   $config
     * @param  string   $name
     * @return Fox\Database\Connections\Connection
     */
    public function make( array $config, $name = null )
    {
        //@TODO: $name look at Laravel Database ConnectionFactory
        return $this->createSingleConnection($config);
    }

    /**
     * Create a single database connection instance.
     *
     * @param  array  $config
     * @return \Fox\Database\Connections\Connection
     */
    protected function createSingleConnection( array $config )
    {
        $pdo = $this->createConnector($config)->connect($config);

        return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param  array  $config
     * @throws \InvalidArgumentException
     * @return \Fox\Database\Connectors\ConnectorInterface
     */
    public function createConnector( array $config )
    {
        if( !isset($config['driver']) )
        {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        switch( $config['driver'] )
        {
            case 'mysql':
                return new MySqlConnector;

            // @TODO: add more
        }

        throw new InvalidArgumentException('Unsupported driver "' . $config['driver'] . '".');
    }

    /**
     * Create a new connection instance.
     *
     * @param  string   $driver
     * @param  \PDO     $connection
     * @param  string   $database
     * @param  string   $prefix
     * @param  array    $config
     * @throws \InvalidArgumentException
     * @return \Fox\Database\Connections\Connection
     */
    protected function createConnection( $driver, PDO $connection, $database, $prefix = '', array $config = [] )
    {
        switch ($driver)
        {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);

            // @TODO: add more
        }

        throw new InvalidArgumentException('Unsupported driver "' . $driver . '".');
    }


}
