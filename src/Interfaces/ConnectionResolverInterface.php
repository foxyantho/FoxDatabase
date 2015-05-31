<?php

namespace Fox\Database;


interface ConnectionResolverInterface
{

    /**
     * Register a connection with the manager.
     * 
     * @param array  $settings 
     * @param string $name
     */
    public function addConnection( array $settings, $name = null );

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    public function connection( $name  );

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections();

}
