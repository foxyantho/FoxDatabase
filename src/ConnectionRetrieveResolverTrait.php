<?php

namespace Fox\Database;

use Fox\Database\ConnectionResolverInterface as ConnectionResolver;


trait ConnectionRetrieveResolverTrait
{

    /**
     * connection name for the model
     *
     * @var string
     */
    protected $connection;
    /**
     * connection resolver instance
     *
     * @var \Fox\Database\ConnectionResolverInterface
     */
    protected static $resolver;


    /**
     * Create a new instance
     * 
     * @return static
     */
    public static function newInstance()
    {
        return ( new static );
    }

    /**
     * Start the model with a given connection
     *
     * @param  string  $connection
     * @return static
     */
    final public static function on( $connection = null )
    {
        // First we will just create a fresh instance of this model, and then we can
        // set the connection on the model so that it is be used for the queries
        // we execute, as well as being set on each relationship we retrieve.

        $instance = static::newInstance();

        $instance->setConnection($connection);

        return $instance;
    }

    /**
     * Get the database connection for the model.
     *
     * @return \Fox\Database\Connections\Connection
     */
    final public function getConnection()
    {
        return static::resolveConnection($this->connection);
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    final public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return $this
     */
    final public function setConnection( $name )
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Resolve a connection instance.
     *
     * @param  string  $connection
     * @return \Fox\Database\connections\Connection
     */
    final public static function resolveConnection( $connection = null )
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
     *
     * @return \Fox\Database\ConnectionResolverInterface
     */
    final public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param  \Fox\Database\ConnectionResolverInterface  $resolver
     */
    final public static function setConnectionResolver( ConnectionResolver $resolver )
    {
        static::$resolver = $resolver;
    }

    /**
     * Unset the connection resolver for models.
     */
    final public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }


}
