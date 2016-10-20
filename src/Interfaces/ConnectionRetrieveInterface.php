<?php

namespace Fox\Database\Interfaces;

use Fox\Database\Interfaces\ConnectionResolverInterface as ConnectionResolver;


interface ConnectionRetrieveInterface
{

    /**
     * Start the model with a given connection
     *
     * @param  string  $connection
     * @return
     */
    public static function on( $connection );

    /**
     * Get the database connection for the model.
     *
     * @return \Fox\Database\Connection
     */
    public function connection();

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName();

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnection( $name );

    /**
     * Resolve a connection instance.
     *
     * @param  string  $connection
     * @return \Fox\Database\Connection
     */
    public static function resolveConnection( $connection );

    /**
     * Get the connection resolver instance.
     *
     * @return \Fox\Database\Interfaces\ConnectionResolverInterface
     */
    public static function getConnectionResolver();

    /**
     * Set the connection resolver instance.
     *
     * @param  \Fox\Database\Interfaces\ConnectionResolverInterface  $resolver
     */
    public static function setConnectionResolver( ConnectionResolver $resolver );

    /**
     * Unset the connection resolver for models.
     */
    public static function unsetConnectionResolver();


}
