<?php

namespace Fox\Database;

use Fox\Database\Interfaces\ModelInterface;

use BadMethodCallException;


/**
 * Model Class
 * MVC part
 */
abstract class Model implements ModelInterface
{

    use ConnectionRetrieveResolverTrait;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * model attribute's original state
     *
     * @var array
     */
    protected $original = [];

    /**
     * model's attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * indicates if the model exists
     *
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
        $this->sync();

        $this->fill($attributes);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get the table associated with the model aka the class' basename
     *
     * @return string
     */
    public function getTable()
    {
        if( isset($this->table) )
        {
            return $this->table;
        }

        // @NOTE: @ -> look at database/connections/connection

        return '@' . strtolower(substr(strrchr(get_called_class(), '\\'), 1));

        // or: end(explode('\\', static::class)
    }

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     * @return void
     */
    public function setTable( $table )
    {
        $this->table = $table;
    }

    /**
     * Start a new query builder instance for the model
     * 
     * @return QueryBuilder
     */
    public function query()
    {
        return static::newQueryBuilder($this);
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
     * Fill the model with an array of attributes.
     *
     * @param mixed|array $attributes
     * @return $this
     */
    public function fill( $attributes = [] )
    {
        foreach( $attributes as $key => $value )
        {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Delete an object
     *
     * @return mixed
     */
    public function delete()
    {
        $data[$this->getKeyName()] = $this->getKey(); // include pk

        $query = $this->query()
                        ->delete()
                        ->wherePK();

        $result = $query->execute($data);

        $this->finishDelete();

        return $result;
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


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Save current object
     *
     * @return mixed
     */
    public function save()
    {
        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.

        if( $this->exist )
        {
            $saved = $this->update();

            $this->finishUpdate();
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.

        else
        {
            $saved = $this->insert();

            $this->finishInsert();
        }


        $this->finishSave();

        return $saved;
    }

    /**
     * Insert the model in the database
     * 
     * @return mixed
     */
    protected function insert()
    {
        $fields = $this->getAttributesKeys();

        $data = $this->getAttributes();

        $query = $this->query()
                        ->insert()
                        ->values($fields);

        return $query->execute($data);
    }

    /**
     * Update modified attributes on the model
     * 
     * @return mixed
     */
    protected function update()
    {
        $fields = $this->diffKeys();

        $data = $this->diff();

        $data[$this->getKeyName()] = $this->getKey(); // include pk

        $query = $this->query()
                        ->update()
                        ->set($fields)
                        ->wherePK();

        return $query->execute($data);
    }

    /**
     * Finish processing on a successful Save operation.
     */
    protected function finishSave()
    {
        $this->exist = true;

        $this->sync();
    }

    /**
     * Finish processing on a successful Insert operation
     */
    protected function finishInsert()
    {
        $id = $this->getConnection()->lastInsertId();

        $this->setKey($id);
    }

    /**
     * Finish processing on a successful Update operation
     */
    protected function finishUpdate()
    {

    }

    /**
     * Finish processing on a successful Delete operation
     */
    protected function finishDelete()
    {
        $this->exist = false;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get differences between original and modified attributes
     * 
     * @return array
     */
    protected function diff()
    {
        return array_diff($this->attributes, $this->original); // inverted diff
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
    public function setAttribute( $key, $value )
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
        $this->setAttribute($key, $value);
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  mixed|array  $attributes
     * @param  bool   $sync
     * @return void
     */
    protected function setRawAttributes( $attributes, $sync = false )
    {
        //$this->attributes = $attributes; //<-- cannot if stdClass

        $this->fill($attributes);

        if( $sync )
        {
            $this->sync();
        }
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
     * Set the primary key for the model
     * 
     * @param mixed $value
     */
    protected function setKey( $value )
    {
        $pk = $this->getKeyName();

        $this->setAttribute($pk, $value);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Find a single model based on wheres array
     * 
     * @param  array $columns
     * @param  array $wheres
     * @return static|bool
     */
    public static function find( array $wheres, array $columns = ['*'] )
    {
        $model = static::newInstance();

        $whereskeys = array_keys($wheres); // can't implode multidimentional ; don't want values in plain text


        $query = $model->query()
                        ->select($columns)
                        ->where($whereskeys)
                        ->limit(1);

        if( $results = $query->single($wheres) )
        {
            $model->fillFromResults($results);

            return $model;
        }

        return false;
    }

    /**
     * Find a single model by it's attribute equal $value
     * 
     * @param  string $key
     * @param  mixed $value
     * @return static|bool
     */
    public static function findBy( $key, $value, array $columns = ['*'] )
    {
        $wheres[$key] = $value;

        return static::find($wheres, $columns);
    }

    /**
     * Find a single model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return static|false
     */
    public static function findById( $id, array $columns = ['*'] )
    {
        // can't use static::find() because, we need getKeyName() before

        $model = static::newInstance();

        $query = $model->query()
                        ->select($columns)
                        ->wherePK()
                        ->limit(1);

        $data[$model->getKeyName()] = $id; // include pk


        if( $results = $query->single($data) )
        {
            $model->fillFromResults($results);

            return $model;
        }

        return false;
    }

    /**
     * Find all models
     * Last arg is always limit
     * 
     * @param  array $columns
     * @return rray|false
     */
    public static function findAll( array $columns = ['*'], array $wheres = [], $limit = null, $offset = null )
    {
        $whereskeys = array_keys($wheres); // can't implode multidimentional

        $query = static::select($columns)->where($whereskeys);


        if( isset($offset) )
        {
            list($offset, $limit) = [$limit, $offset];

            // if 2 args -> $limit become $offset ( invertion ) -> last arg is always limit
        }

        $query->limit($limit)->offset($offset);

        $results = $query->execute($wheres);


        if( $results )
        {
            $models = [];

            foreach( $results as $result )
            {
                $models[] = static::newFromResults($result);
            }

            return $models;
        }

        return false;
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return 
     */
    public static function findAllForPage( array $columns = ['*'], array $wheres = [], $page = 1, $perPage = 10 )
    {
        $offset = ( $page - 1 ) * $perPage;

        return static::findAll($columns, $wheres, $offset, $perPage);

        // @TODO: order by
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Create a new query builder instance for the model ( static )
     * 
     * @param  static $model
     * @return QueryBuilder
     */
    public static function newQueryBuilder( $model = null )
    {
        $builder = new QueryBuilder;

        if( isset($model) )
        {
            $builder->setModel($model);
        }

        return $builder;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


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
        $this->setRawAttributes($attributes, true);

        $this->exist = true;
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param  array  $items
     * @param  string  $connection
     * @return array
     */
    public static function hydrate( array $items, $connection = null )
    {
        $instance = new static;

        $collection = [];

        foreach( $items as $item )
        {
            $model = $instance->fillFromResults($item);

            if( isset($connection) )
            {
                $model->setConnection($connection);
            }

            $collection[] = $model;
        }

        return $collection;
    }

    /**
     * Create a collection of models from a raw query.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  string  $connection
     * @return array
     */
    public static function hydrateRaw( $query, array $bindings = [], $connection = null )
    {
        $instance = new static;

        if ( isset($connection) )
        {
            $instance->setConnection($connection);
        }

        $results = $instance->getConnection()->all($query, $bindings);

        if( $results )
        {
            return static::hydrate($results, $connection);
        }

        return false;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


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

    /**
     * Handle dynamic static QueryBuilder calls into the method.
     *
     * @param  string  $method
     * @param  array   $arguments
     * @return mixed|BadMethodCallException
     */
    public static function __callStatic( $method, $parameters )
    {
        $builder = static::newQueryBuilder(new static);

        if( method_exists($builder, $method) )
        {
            return call_user_func_array([$builder, $method], $parameters);
        }

        throw new BadMethodCallException('Call to undefined method "' . get_class($builder) . '"::' . $method . '()');
    }


}
