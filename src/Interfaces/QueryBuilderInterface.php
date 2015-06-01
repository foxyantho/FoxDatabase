<?php

namespace Fox\Database\Interfaces;


interface QueryBuilderInterface
{
    // query

    public function getQueryType();

    public function queryType( $type );

    // tables

    public function getTables();

    public function table( $tables );

    public function removeTable( $tables );

    // fields

    public function getFields();

    public function field( $fields );

    public function removeField( $fields );

    // select

    public function select( $keys );

    public function min( $keys );

    public function max( $keys );

    public function count( $keys );

    public function selectFunction( $function, $keys );

    public function removeSelect( $keys );

    // update

    public function update( $tables );

    public function set( $keys );

    // delete

    public function delete( $tables );

    // insert

    public function insert( $tables );

    public function into( $tables );

    public function values( $values );

    // from

    public function from( $keys );

    public function removeFrom( $keys );

    // where

    public function where( $keys );

    public function removeWhere( $key );

    // group by

    public function groupBy( $keys );

    // having

    public function having( $keys );

    // order by

    public function orderBy( $keys, $suffix );

    // limit

    public function limit( $key );

    public function offset( $key );

    // querystring

    public function getQueryString();

    // pdo results

    public function execute( array $data );

    public function single( array $data );


}
