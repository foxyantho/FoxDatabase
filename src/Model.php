<?php

namespace Fox\Database;

use Fox\Database\Interfaces\ModelInterface;
use Fox\Database\Interfaces\ConnectionRetrieveInterface;


abstract class Model implements ModelInterface, ConnectionRetrieveInterface
{

    use ConnectionRetrieveTrait;


    /**
     * the table associated with the model.
     * @var string
     */
    protected static $table;

    /**
     * primary key for the model
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * model attribute's ( current state )
     * @var array
     */
    protected $original = [];

    /**
     * model's attributes ( draft state )
     * @var array
     */
    protected $attributes = [];

    /**
     * indicates if the current model exists
     * @var boolean
     */
    public $exist = false;


    /**
     * Constructor
     * 
     * @param mixed $attributes
     */
    public function __construct( $attributes = [] )
    {
        $this->attributes($attributes);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get the table associated with the model aka the class' basename
     *
     * @return string
     */
    public static function getTable()
    {
        if( isset(static::$table) )
        {
            return static::$table;
        }

        // '@' will be replace by table preffix by Connection

        $parts = explode('\\', get_called_class());

        return '@' . strtolower(end( $parts  ));
    }

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     */
    public static function table( $table )
    {
        static::$table = $table;
    }

    /**
     * Start a new query builder with model's given table
     * 
     * @return QueryBuilder
     */
    public static function query()
    {
        $builder = static::newQuery();

        $builder->table(static::getTable());

        return $builder;
    }

    /**
     * Create a new query builder instance
     * 
     * @return QueryBuilder
     */
    public static function newQuery()
    {
        return new QueryBuilder;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function sync()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Revert all changes on attributes
     *
     * @return $this
     */
    public function revert()
    {
        $this->attributes = $this->original;

        return $this;
    }

    /**
     * Delete an object, and check if it was affected by DELETE
     *
     * @return int|false
     */
    public function delete()
    {
        $result = static::query()
                         ->delete()
                         ->where($this->keypair())
                         ->execute();

        if( $result > 0 )
        {
            $this->exist = false;

            return $result;
        }

        return false;
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function create( array $attributes = [] )
    {
        $instance = static::newInstance($attributes);

        $instance->save();

        return $instance;
    }

    /**
     * Save current object, and return the PDO query result
     *
     * @return true|false|int
     */
    public function save()
    {
        // if the model already exists in the database, update the record using "where PK"

        if( $this->exist )
        {
            if( !empty($this->diff()) )
            {
                $saved = static::query()
                                ->update()
                                ->set($this->diffKeys())
                                ->where($this->keypair())
                                ->execute($this->diff());
            }
            else
            {
                // all modified attributes are the same as those in Database,
                // so don't need to unnecessarily update it

                $saved = true; // same return as QueryBuilder::UPDATE
            }
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.

        else
        {
            $saved = static::query()
                            ->insert()
                            ->values($this->getAttributesKeys())
                            ->execute($this->getAttributes());

            // finish insert

            $this->key($saved);
        }

        // finish save

        $this->exist = true;

        $this->sync();


        return $saved;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get differences between original and modified attributes
     * 
     * @return array
     */
    protected function diff()
    {
        return array_diff($this->attributes, $this->original);
    }

    /**
     * Get keys from differences between original and modified attributes
     * 
     * @return array
     */
    protected function diffKeys()
    {
        return array_keys($this->diff());
    }

    /**
     * Get values from differences between original and modified attributes
     * 
     * @return array
     */
    protected function diffValues()
    {
        return array_values($this->diff());
    }

    /**
     * Get all of the current attributes on the model
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Fill the model with an array of attributes. No checking is done.
     *
     * @param mixed|array $attributes
     * @param  bool   $sync
     * @return $this
     */
    public function attributes( $attributes = [], $sync = false )
    {
        foreach( $attributes as $key => $value )
        {
            $this->attribute($key, $value);
        }

        if( $sync )
        {
            $this->sync();
        }

        return $this;
    }

    /**
     * Get all of the current attributes' keys on the model
     *
     * @return array
     */
    public function getAttributesKeys()
    {
        return array_keys($this->attributes);
    }

    /**
     * Get all of the current attributes' values on the model
     *
     * @return array
     */
    public function getAttributesValues()
    {
        return array_values($this->attributes);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute( $key )
    {
        if( isset($this->attributes[$key]) )
        {
            return $this->attributes[$key];
        }

        return false;
    }

    /**
     * Dynamically retrieve attributes on the model
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get( $key )
    {
        return $this->getAttribute($key);
    }

    /**
     * Set a given attribute on the model
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function attribute( $key, $value )
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamically set attributes on the model
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function __set( $key, $value )
    {
        $this->attribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset( $key )
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute on the model
     *
     * @param  string  $key
     * @return void
     */
    public function __unset( $key )
    {
        unset($this->attributes[$key]);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get the value of the model's primary key
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Return an associative array with key name and value
     * Exclusively used with WHERE clause in queries
     * 
     * @return array
     */
    public function keypair()
    {
        return [ $this->getKeyName() => $this->getKey() ];
    }

    /**
     * Set the primary key for the model
     * 
     * @param mixed $value
     */
    public function key( $value )
    {
        $key = $this->getKeyName();

        $this->attribute($key, $value);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Retrieving a record by its primary key
     * 
     * @return static|bool
     */
    public static function find( $value )
    {
        $model = static::newInstance();

        $keyname = $model->getKeyName();

        $results = $model::query()
                          ->where($keyname)
                          ->single([$keyname => $value]);

        if( $results )
        {
            $model->fillFromResults($results);

            return $model;
        }

        return false;
    }

    /**
     * Create a new model instance that is existing
     *
     * @param  mixed $attributes
     * @return static
     */
    public static function newFromResults( $attributes = [] )
    {
        return static::newInstance($attributes, true);
    }

    /**
     * Fill the model with an array of results
     * 
     * @param  mixed $attributes
     */
    public function fillFromResults( $attributes = [] )
    {
        $this->attributes($attributes, true); // +sync

        $this->exist = true;

        return $this;
    }

    /**
     * Create a new instance of the given model
     *
     * @param  mixed  $attributes
     * @param  bool   $exist
     * @return static
     */
    public static function newInstance( $attributes = [], $exist = false )
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.

        $instance = new static($attributes);

        $instance->exist = $exist;

        return $instance;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson( $options = 0 )
    {
        //merge new/old ?
        return json_encode($this->getAttributes(), $options);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }


}
