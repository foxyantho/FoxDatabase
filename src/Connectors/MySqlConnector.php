<?php

namespace Fox\Database\Connectors;


class MySqlConnector extends Connector implements ConnectorInterface
{

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

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.

        $connection = $this->createConnection($dsn, $config, $options);

        // Next we will set the "names"

        $connection->prepare('SET NAMES ' . $config['charset'])->execute();

        // If the "strict" option has been configured for the connection we'll enable
        // strict mode on all of these tables. This enforces some extra rules when
        // using the MySQL database system and is a quicker way to enforce them.

        if( isset($config['strict']) && $config['strict'] )
        {
            $connection->prepare("SET SESSION sql_mode='STRICT_ALL_TABLES'")->execute();
        }

        return $connection;
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
        $dsn = [];

        foreach( $config['dsn'] as $key => $value )
        {
            $dsn[] = $key . '=' .$value;
        }


        return $config['driver'] . ':' . implode(';', $dsn);
    }


}
