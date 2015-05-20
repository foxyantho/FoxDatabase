<?php

namespace Fox\Database\Connectors;

use PDO;
use PDOException;


class Connector
{

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, //PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false

        //PDO::FETCH_OBJ => true,
        //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    ];

    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array   $config
     * @param  array   $options
     * @return PDO
     */
    public function createConnection( $dsn, array $config, array $options )
    {
        try
        {
            return new PDO($dsn, $config['username'], $config['password'], $options);
        }
        catch( PDOException $e )
        {
            die('PDOException: ' . get_called_class() . ' -> {' . $e->getCode() . '} ' . $e->getMessage());
        }
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions( array $config )
    {
        if( isset($config['options']) )
        {
            //foreach( $config['options'] as $key => $value )
            //  $this->options[$key] = $value;

            // array_diff_key because array_merge re-organize keys

            return array_diff_key($this->options, $config['options']) + $config['options'];
        }

        return $this->getDefaultOptions();
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param  array  $options
     * @return void
     */
    public function setDefaultOptions( array $options )
    {
        $this->options = $options;
    }


}
