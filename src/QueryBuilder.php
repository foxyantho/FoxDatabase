<?php

namespace Fox\Database;

use Fox\Database\Interfaces\QueryBuilderInterface;

use Closure;
use LogicException;
use UnexpectedValueException;


/**
 * QueryBuilder Class
 * Perform and generate queries
 */
class QueryBuilder implements QueryBuilderInterface
{

    use ConnectionRetrieveTrait;
//@TODO: INSERT INTO  (`article_id`, `category_id`) VALUES ('3', '1'), ('5', '1');

    const SELECT = 1;
    const UPDATE = 2;
    const DELETE = 3;
    const INSERT = 4;

    protected $type;

    protected $fields = []; //attributes : SELECT xx, SET xx, VALUES xx

    protected $tables = [];


    protected $where = [];


    protected $groupBy = [];

    protected $having = [];

    protected $orderBy = [];

    protected $orderBySuffix;


    protected $limit;

    protected $offset;



    public function __construct()
    {
        // default query type

        $this->queryType(static::SELECT);

        // @TODO: implements OR / IN / NOT IN ...
    }

    /**
     * Get query string type
     * 
     * @return int
     */
    public function getQueryType()
    {
        return $this->type;
    }

    /**
     * Set query string type
     * 
     * @param int $type
     */
    public function queryType( $type )
    {
        $this->type = $type;
    }

    /**
     * Return table(s)
     * 
     * @return array|LogicException
     */
    public function getTables()
    {
        if( !empty($this->tables) )
        {
            return $this->tables;
        }

        throw new LogicException('No tables were found.');
    }

    /**
     * Set table(s) of query string
     * 
     * @param  string|array $tables
     * @return this
     */
    public function table( $tables )
    {
        if( is_string($tables) )
        {
            $tables = [$tables];
        }

        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    /**
     * Remove table(s) from query string
     * 
     * @param  string|array $tables
     * @return this
     */
    public function removeTable( $tables )
    {
        if( is_string($tables) )
        {
            $tables = [$tables];
        }

        foreach( $tables as $key )
        {
            if( isset($this->tables[$key]) )
            {
                unset($this->tables[$key]);
            }
        }

        return $this;
    }

    /**
     * Get fields
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set field(s)
     * used in: SELECT xx, SET xx, VALUES xx
     * 
     * @param  string|array $fields
     * @return this
     */
    public function field( $fields )
    {
        if( is_string($fields) )
        {
            $fields = [$fields];
        }

        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * Remove field(s)
     * 
     * @param  string|array $fields
     * @return this
     */
    public function removeField( $fields )
    {
        if( is_string($fields) )
        {
            $fields = [$fields];
        }

        foreach( $fields as $key )
        {
            if( isset($this->fields[$key]) )
            {
                unset($this->fields[$key]);
            }
        }

        return $this;
    }


    // SELECT //


    /**
     * SELECT clause
     * 
     * @param  string|array|null $keys
     * @return this
     */
    public function select( $keys = null )
    {
        $this->queryType(static::SELECT);

        if( isset($keys) )
        {
            $this->field($keys);
        }

        return $this;
    }

    public function min( $keys )
    {
        $this->selectFunction('MIN', $keys);

        return $this;
    }

    public function max( $keys )
    {
        $this->selectFunction('MAX', $keys);

        return $this;
    }

    public function count( $keys )
    {
        $this->selectFunction('COUNT', $keys);

        return $this;
    }

    /**
     * Add a SELECT clause with function name
     * 
     * @param  string $function
     * @param  string|array $keys
     * @return this
     */
    public function selectFunction( $function, $keys )
    {
        if( is_string($keys) )
        {
            $keys = [$keys];
        }

        foreach( $keys as $key => $value )
        {
            if( !is_string($key) ) // non assosiative
            {
                $key = $value;
            }

            $this->select([ $function . '(' . $key . ')' => $value]);
        }

        return $this;
    }

    public function removeSelect( $keys )
    {
        $this->removeField($keys);

        return $this;
    }


    // UPDATE //


    /**
     * UPDATE clause
     * 
     * @param  string|null $tables
     * @return this
     */
    public function update( $tables = null )
    {
        $this->queryType(static::UPDATE);

        if( isset($tables) )
        {
            $this->table($tables);
        }

        return $this;
    }

    /**
     * UPDATE SET clause
     * 
     * @param  string|array $keys
     * @return this
     */
    public function set( $keys )
    {
        $this->field($keys);

        return $this;
    }


    // DELETE //

    /**
     * DELETE clause
     * 
     * @param  string|null $tables
     * @return this
     */
    public function delete( $tables = null )
    {
        $this->queryType(static::DELETE);

        if( isset($tables) )
        {
            $this->table($tables);
        }

        return $this;
    }


    // INSERT //

    /**
     * INSERT INTO clause
     * 
     * @param  string|null $tables
     * @return this
     */
    public function insert( $tables = null )
    {
        $this->queryType(static::INSERT);

        if( isset($tables) )
        {
            $this->into($tables);
        }

        return $this;
    }

    /**
     * INTO clause
     * 
     * @param  string|array $tables
     * @return $this
     */
    public function into( $tables )
    {
        $this->table($tables);

        return $this;
    }

    /**
     * INSERT INTO VALUES clause
     * 
     * @param  string|array $values
     * @return this
     */
    public function values( $values )
    {
        $this->field($values);

        return $this;
    }


    // FROM //


    /**
     * FROM clause
     * 
     * @param  string|array $keys
     * @return this
     */
    public function from( $keys )
    {
        $this->table($keys);

        return $this;
    }

    public function removeFrom( $keys )
    {
        $this->removeTable($keys);

        return $this;
    }


    // WHERE //


    /**
     * WHERE clause
     * 
     * @param  string|array $keys
     * @return this
     */
    public function where( $keys )
    {
        if( is_string($keys) )
        {
            $keys = [$keys];
        }

        $this->where = array_merge($this->where, $keys);

        return $this;
    }

    public function removeWhere( $key )
    {
        unset($this->where[$key]);

        return $this;
    }


    // OTHER CLAUSE


    /**
     * GROUP BY clause
     * 
     * @param  string|array $keys
     * @return this
     */
    public function groupBy( $keys )
    {
        if( is_string($keys) )
        {
            $keys = [$keys];
        }

        $this->groupBy = array_merge($this->groupBy, $keys);

        return $this;
    }

    /**
     * HAVING clause
     * 
     * @param  string|array $keys
     * @return this
     */
    public function having( $keys )
    {
        if( is_string($keys) )
        {
            $keys = [$keys];
        }

        $this->having = array_merge($this->having, $keys);

        return $this;
    }

    /**
     * ORER BY clause
     * 
     * @param  string|array $keys
     * @param  string $suffix
     * @return this
     */
    public function orderBy( $keys, $suffix = null )
    {
        if( is_string($keys) )
        {
            $keys = [$keys];
        }

        $this->orderBy = array_merge($this->orderBy, $keys);

        if( $suffix )
        {
            $this->orderBySuffix = $suffix;
        }

        return $this;
    }

    /**
     * LIMIT clause
     * 
     * @param  int $limit
     */
    public function limit( $limit )
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set OFFSET clause
     * 
     * @param  int $offset
     */
    public function offset( $offset )
    {
        $this->offset = $offset;

        return $this;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////



    /**
     * Array to string
     * 
     * @param  array  $arr
     * @param  string $glue
     * @return string
     */
    protected function arrayAsString( array $arr, $glue = ', ' )
    {
        return implode($glue, $arr);
    }

    /**
     * Used in the get_xx_QueryString() to flatten data
     * $closure(key, value) -> $key : table / field's key ; $value : table alias / field's value
     * 
     * @param  array   $data
     * @param  Closure $closure
     * @param  string  $glue
     * @return null|string
     */
    protected function getFieldsPartQueryString( array $data , Callable $closure, $glue = ', ' )
    {
        if( $data )
        {
            $arr = [];

            foreach( $data as $key => $value )
            {
                if( !is_string($key) ) // non associative array : ['key', 'key']
                {
                    $key = $value; //@FIXME: unknow when using number etc

                    $value = null; // must be null -> futher isset() ; if($value) with empty string
                }

                $arr[] = $closure($key, $value);
            }

            return $this->arrayAsString($arr, $glue);
        }
    }


    /**
     * Return a generated query string part with tables
     * 
     * @return string
     */
    protected function getTablesQueryString()
    {
        if( $tables = $this->getTables() )
        {
            return $this->getFieldsPartQueryString(
                $tables,
                function( $table, $alias ) { return isset($alias) ? $table . ' AS ' . $alias : $table ; }
            );

            // string index ( an alias in query )
        }
    }

    /**
     * Return the "SELECT" part query string
     * SELECT "xx, yy AS zz"
     * 
     * @return string
     */
    protected function getFieldsSelectQueryString()
    {
        if( $fields = $this->getFields() )
        {
            return $this->getFieldsPartQueryString(
                $fields,
                function( $key, $alias ) { return isset($alias) ? $key . ' AS ' . $alias : $key; }
            );
            
            // xx, yy / xx AS aa, yy AS bb
        }
        else
        {
            // no fields : wildcard

            return '*';
        }
    }

    /**
     * Return the "SET" part of "UPDATE" query string
     * UPDATE tt SET "xx = xx"
     * 
     * @return string|LogicException
     */
    protected function getFieldsUpdateQueryString()
    {
        if( $fields = $this->getFields() )
        {
            return $this->getFieldsPartQueryString(
                $fields,
                function( $key, $value ) { return isset($value) ? $key . ' = ' . $value : $key . ' = :' . $key; }
            );

            // xx = value / xx = :xx
        }
        
        // no fields

        throw new LogicException('No fields were found.');
    }

    /**
     * Return the "INTO" part of "INSERT" query string
     * INSERT INTO tt("xx, yy")
     * 
     * @return string
     */
    protected function getFieldsInsertIntoQueryString()
    {
        if( $fields = $this->getFields())
        {
            return $this->getFieldsPartQueryString(
                $fields,
                function( $key, $value ) { return $key; }
            );

            // table(key, key)
        }
        
        // no fields

        throw new LogicException('No fields were found.');
    }

    /**
     * Return the "VALUES" part of "INSERT" query string
     * INSERT INTO tt(xx, yy) VALUES ("'xx', 'yy'")
     * 
     * @return string|LogicException
     */
    protected function getFieldsInsertValuesQueryString()
    {
        if( $fields = $this->getFields() )
        {
            return $this->getFieldsPartQueryString(
                $fields,
                function( $key, $value ) { return isset($value) ? $value : ':' . $key; }
            );

            // xx, yy / :xx, :yy
        }
        
        // no fields

        throw new LogicException('No fields were found.');
    }

    /**
     * Return the "WHERE" part of query string
     * SELECT WHERE xx = yy AND zz = :zz
     * 
     * @return string|null
     */
    protected function getWhereQueryString()
    {
        if( $fields = $this->where )
        {
            return 'WHERE ' . $this->getFieldsPartQueryString(
                $fields,
                function( $key, $value ) { return isset($value) ? $key . ' = ' . $value : $key . ' = :' . $key; },
                ' AND '
            );

            // xx = yy / xx = :xx
        }
    }

    /**
     * Return the "GROUP BY xx, yy" query string part
     * 
     * @return string|null
     */
    protected function getGroupByQueryString()
    {
        if( $this->groupBy )
        {
            return 'GROUP BY ' . $this->arrayAsString($this->groupBy);
        }
    }

    /**
     * Return the "HAVING xx <> xx" query string part
     * 
     * @return string|null
     */
    protected function getHavingQueryString()
    {
        if( $this->having )
        {
            return 'HAVING ' . $this->arrayAsString($this->having, ' AND ');
        }
    }

    /**
     * Return the "ORDER BY xx, yy ASC|DESC" query string part
     * 
     * @return string|null
     */
    protected function getOrderByQueryString()
    {
        if( $this->orderBy )
        {
            return 'ORDER BY ' . $this->arrayAsString($this->orderBy) . ' ' . ( $this->orderBySuffix ?: null );
        }
    }


    /**
     * Return the "LIMIT limit" or "LIMIT offset, limit" query string part
     * 
     * @return string|null
     */
    protected function getLimitQueryString()
    {
        if( $this->offset && $this->limit )
        {
            return 'LIMIT ' . $this->offset . ', ' . $this->limit;
        }

        if( $this->limit )
        {
            return 'LIMIT ' . $this->limit;
        }

        //return 'LIMIT ' . ( isset($this->offset) ? $this->offset . ', ' . $this->limit : $this->limit );
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////



    /**
     * Return the full "SELECT" query string for prepared statements
     * 
     * @return string
     */
    protected function getSelectQueryString()
    {
        $queryString = 'SELECT ' . $this->getFieldsSelectQueryString() . ' FROM ' . $this->getTablesQueryString();

        if( $this->where )
        {
            $queryString .= ' ' . $this->getWhereQueryString();
        }

        if( $this->groupBy )
        {
            $queryString .= ' ' . $this->getGroupByQueryString();
        }

        if( $this->having )
        {
            $queryString .= ' ' . $this->getHavingQueryString();
        }

        if( $this->orderBy )
        {
            $queryString .= ' ' . $this->getOrderByQueryString();
        }

        if( $this->limit )
        {
            $queryString .= ' ' . $this->getLimitQueryString();
        }

        return $queryString;
    }

    /**
     * Return the full "UPDATE" query string for prepared statements
     * 
     * @return string
     */
    protected function getUpdateQueryString()
    {
        $queryString = 'UPDATE ' . $this->getTablesQueryString() . ' SET ' . $this->getFieldsUpdateQueryString();

        if( $this->where )
        {
            $queryString .= ' ' . $this->getWhereQueryString();
        }

        return $queryString;
    }

    /**
     * Return the full "DELETE" query string for prepared statements
     * 
     * @return string
     */
    protected function getDeleteQueryString()
    {
        $queryString = 'DELETE FROM ' . $this->getTablesQueryString();

        if( $this->where )
        {
            $queryString .= ' ' . $this->getWhereQueryString();
        }

        return $queryString;
    }

    /**
     * Return the full "INSERT" query string for prepared statements
     * 
     * @return string
     */
    protected function getInsertQueryString()
    {
        return 'INSERT INTO ' . $this->getTablesQueryString() . ' (' . $this->getFieldsInsertIntoQueryString() . ') VALUES (' . $this->getFieldsInsertValuesQueryString() . ')';

        // @TODO multiple values(xx), (xx)
    }


    /**
     * Get the final generated query string
     * 
     * @throws \UnexpectedValueException
     * @return string
     */
    public function getQueryString()
    {
        switch( $this->getQueryType() )
        {
            case static::SELECT:

                return $this->getSelectQueryString();

            case static::UPDATE:

                return $this->getUpdateQueryString();

            case static::DELETE:

                return $this->getDeleteQueryString();

            case static::INSERT:

                return $this->getInsertQueryString();
        }

        throw new UnexpectedValueException('Query Type is unknow.');
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Execute a statement
     * 
     * @param  array  $data
     * @return PDOStatement|false
     */
    protected function statement( array $data = [] )
    {
        return $this->connection()->statement($this->getQueryString(), $data);
    }


    /**
     * Execute the query, and get its result
     * 
     * @param  array  $data
     * @throws \UnexpectedValueException
     * @return array|bool|int|string
     */
    public function execute( array $data = [] ) //@TODO: change name to 'get'
    {
        if( !$statement = $this->statement($data) )
        {
            return false;
        }

        switch( $this->getQueryType() )
        {
            case static::SELECT:

                return $statement->fetchAll(); // array

            case static::UPDATE:

                return true; // already tested, look upper if()

            case static::DELETE:

                return $statement->rowCount();

            case static::INSERT:

                return $this->connection()->lastInsertId(); // string

            default:

                throw new UnexpectedValueException('This query type can not return results.');
        }
    }

    /**
     * Execute the query and get the first set of results
     * 
     * @param  array  $data
     * @return Object|mixed|false
     */
    public function single( array $data = [] )
    {
        if( $results = $this->execute($data) )
        {
            if( is_array($results) )
            {
                // if multiple results, get only the first

                return array_shift($results);
            }

            return $results;
        }

        return false;
    }

    /**
     * To String method
     * 
     * @return string|UnexpectedValueException
     */
    public function __toString()
    {
        return $this->getQueryString();
    }


}
