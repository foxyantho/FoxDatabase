<?php

namespace Fox\Database;

use Fox\Database\Connections\ConnectionFactory;
use Fox\Database\ConnectionRetrieveResolverTrait as ConnectionRetrieveResolver;


class Capsule
{

    /**
     * The current globally used instance.
     * 
     * @var Capsule
     */
    protected static $instance;

    /**
     * Configurations container
     */
    protected $configurations = [];

    /**
     * The database manager instance.
     */
    protected $manager;


    /**
     * Get default application settings
     * 
     * @return array
     */
    public static function getDefaultSettings()
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
///////////////////////////////////////////@FIXME: multi-copy of configurations

    /**
     * Create a new database capsule manager.
     *
     * @return void
     */
    public function __construct()
    {
        // We will setup the default configuration. This will make the database
        // manager behave correctly since all the correct binding are in place.

        $this->setupDefaultConfiguration(); // @TODO: simplifier, don't use connector

        $this->setupManager();
    }

    /**
     * Run Capsule
     * 
     * @return void
     */
    public function run()
    {
        $this->setupManager();

        $this->bootConnectionRetrieve();
    }

    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        $factory = new ConnectionFactory($this->configurations);

        $this->manager = new DatabaseManager($this->configurations, $factory);
    }

    /**
     * Bootstrap ConnectionRetrieveResolver so it is ready for usage anywhere
     * Used to get the current Connection
     */
    public function bootConnectionRetrieve()
    {
        ConnectionRetrieveResolver::setConnectionResolver($this->manager);
    }



    /**
     * Setup the default database configuration options.
     *
     * @return void
     */
    protected function setupDefaultConfiguration()
    {
        $this->configurations['database.default'] = 'default';
    }

    /**
    * Register a connection with the manager.
    *
    * @param array $config
    * @param string $name
    * @return void
    */
    public function addConnection( array $userSettings, $name = 'default' )
    {
        $this->configurations[$name] = array_merge(static::getDefaultSettings(), $userSettings);
    }

    /**
     * Get a connection instance from the global manager.
     *
     * @param  string  $connection
     * @return \Fox\database\connctions\Connection
     */
    public static function connection( $connection = null )
    {
        return static::$instance->getConnection($connection);
    }

    /**
     * Get a registered connection instance.
     *
     * @param  string  $name
     * @return \Fox\database\connctions\Connection
     */
    public function getConnection( $name = null )
    {
        return $this->manager->connection($name);
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Get the database manager instance.
     *
     * @return \Fox\database\DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic( $method, $parameters )
    {
        return call_user_func_array([static::connection(), $method], $parameters);
    }


}
