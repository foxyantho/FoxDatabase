<?php

namespace Fox\Database\Interfaces;



interface ModelInterface
{

    public function __construct( $attributes );

    // table

    public function getTable();

    public function table( $table );

    // query

    public function query();

    public static function newQuery();

    // syncing

    public function sync();

    public function revert();

    // model

    public function delete();

    public static function create( array $attributes );

    public function save();

    // attributes

    public function getAttributes();

    public function attributes( $attributes, $sync );

    public function getAttributesKeys();

    public function getAttributesValues();

    public function getAttribute( $key );

    public function __get( $key );

    public function attribute( $key, $value );

    public function __set( $key, $value );

    public function __isset( $key );

    public function __unset( $key );

    // PK

    public function getKey();

    public function getKeyName();

    public function keypair();

    public function key( $value );

    // find by PK

    public static function find( $value );


    public function toJson( $options );

    public function __toString();


}
