<?php

namespace Fox\Database\Interfaces;


interface ConnectionResolverInterface
{

    /**
     * Register a connection with the manager.
     * 
     * @param array  $settings 
     * @param string $name
     */
    public function addConnection( array $settings, $name );

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Fox\Database\Connection
     */
    public function connection( $name  );

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections();

}
