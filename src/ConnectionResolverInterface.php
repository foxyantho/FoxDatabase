<?php

namespace Fox\Database;


interface ConnectionResolverInterface
{

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    public function connection( $name  );

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection();

}
