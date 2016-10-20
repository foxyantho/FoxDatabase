<?php

namespace Fox\Database\Interfaces;


interface ConnectorInterface
{

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect( array $config );


}
