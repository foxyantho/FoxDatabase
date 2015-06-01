<?php

namespace Fox\Database;

use Fox\Database\Interfaces\ConnectorInterface;

use PDO;
use PDOException;


class Connector implements ConnectorInterface
{

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    public static function getDefaultSettings()
    {
        return [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, //PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false

        //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ];
    }

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect( array $config )
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        // brand new connection with PDO options

        $connection = $this->createConnection($dsn, $config['username'], $config['password'], $options);

        // next we will set the "names"

        if( isset($config['charset']) )
        {
            $connection->prepare('SET NAMES ' . $config['charset'])->execute();
        }

        // if "strict" option has been configured for the connection
        // enforces some extra rules when using a MySQL database system

        if( isset($config['strict']) && $config['strict'] )
        {
            $connection->prepare("SET SESSION sql_mode='STRICT_ALL_TABLES'")->execute();
        }


        return $connection;
    }

    /**
     * Create a new PDO connection.
     * 
     * @param  string $dsn
     * @param  string $username
     * @param  string $password
     * @param  array  $options
     * @return \PDO|void
     */
    protected function createConnection( $dsn, $username, $password, array $options )
    {
        try
        {
            return new PDO($dsn, $username, $password, $options);
        }
        catch( PDOException $e )
        {
            die('Connector: Could not connect to database, code : ' . $e->getCode());
        }
    }

    /**
     * Create a DSN string from a configuration.
     * Everything in the 'dsn' config will included.
     *
     * @param  array   $config
     * @return string
     */
    protected function getDsn( array $config )
    {
        extract($config);

        return isset($config['port'])
                ? $driver . ':host=' . $host . ';port=' . $port . ';dbname=' . $database
                : $driver . ':host=' . $host . ';dbname=' . $database;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    protected function getOptions( array $config )
    {
        if( isset($config['options']) )
        {
            // merge with keys keeping

            return $config['options'] + static::getDefaultSettings();
        }

        return static::getDefaultSettings();
    }


}
