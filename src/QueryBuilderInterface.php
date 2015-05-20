<?php

namespace Fox\Database;


interface QueryBuilderInterface
{

    static function newInstance(); // ConnectionRetrieveResolver


    public function getModel();

    public function setModel( ModelInterface $model );


    public function getQueryType();

    public function setQueryType( $type );

    // TABLES //

    public function getTables();

    public function table( $tables );

    public function removeTable( $tables );

    // FIELDS //

    public function getFields();

    public function field( $fields );

    public function removeField( $fields );

    // SELECT //

    public function select( $keys );

    public function min( $keys );

    public function max( $keys );

    public function count( $keys );

    public function selectFunction( $function, $keys );

    public function removeSelect( $keys );

    // UPDATE //

    public function update( $tables );

    public function set( $keys );

    // DELETE //

    public function delete( $tables );

    // INSERT //

    public function insert( $tables );

    public function values( $values );

    // FROM //

    public function from( $keys );

    public function removeFrom( $keys );

    // WHERE //

    public function where( $keys );

    public function wherePK( $with_value );

    public function removeWhere( $key );

    // GROUP BY //

    public function groupBy( $keys );

    // HAVING //

    public function having( $keys );

    // ORDER BY //

    public function orderBy( $keys, $suffix );

    // LIMIT //

    public function limit( $key );

    // OFFSET //

    public function offset( $key );

    //

    public function getQueryString();

    //

    public function execute( array $data );

    public function single( array $data );


}
