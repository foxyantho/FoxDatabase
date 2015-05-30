<?php

namespace Fox\Database;

use Fox\Database\Connections\ConnectionFactory;
use Fox\Database\ConnectionRetrieveResolverTrait as ConnectionRetrieveResolver;


class Capsule
{

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

        $this->configurations['default'] = 'default';
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
    protected function bootConnectionRetrieve()
    {
        ConnectionRetrieveResolver::setConnectionResolver($this->manager);
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
     * Get the database manager instance.
     *
     * @return \Fox\database\DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }


}
