<?php

namespace Fox\Database;


interface ModelInterface
{

    public function getTable();

    public function setTable( $table );

    public function query();

    
    public function sync();

    public function revert();

    public function fill( $attributes );

    public function save();

    public function delete();

    public static function create( array $attributes );


    public function getAttributes();

    public function getAttributesKeys();

    public function getAttributesValues();

    public function getAttribute( $key );

    public function __get( $key );

    public function setAttribute( $key, $value );

    public function __set( $key, $value );


    public function getKey();

    public function getKeyName();


    public static function find( array $wheres, array $columns );

    public static function findBy( $key, $value, array $columns );

    public static function findById( $id, array $columns );

    public static function findAll( array $columns, array $wheres, $limit, $offset );

    public static function findAllForPage( array $columns, array $wheres, $page, $perPage );


    public static function newQueryBuilder( $model );


    public static function newFromResults( $attributes );

    public function fillFromResults( $attributes );

    public static function hydrate( array $items, $connection );

    public static function hydrateRaw( $query, array $bindings, $connection );


}
