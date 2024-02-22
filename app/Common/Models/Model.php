<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Model class.
 *
 * @since 4.0.0
 */
#[\AllowDynamicProperties]
class Model implements \JsonSerializable {
	/**
	 * Fields that can be null when saving to the database.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $nullFields = [];

	/**
	 * Fields that should be encoded/decoded on save/get.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $jsonFields = [];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $booleanFields = [];

	/**
	 * Fields that should be numeric values.
	 *
	 * @since 4.1.0
	 *
	 * @var array
	 */
	protected $numericFields = [];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $hidden = [];

	/**
	 * The table used in the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * The primary key retrieved from the table.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $pk = 'id';

	/**
	 * The ID of the model.
	 * This needs to be null in order for MySQL to auto-increment correctly if the NO_AUTO_VALUE_ON_ZERO SQL mode is enabled.
	 *
	 * @since 4.2.7
	 *
	 * @var int|null
	 */
	public $id = null;

	/**
	 * An array of columns from the DB that we can use.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private static $columns = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed $var This can be the primary key of the resource, or it could be an array of data to manufacture a resource without a database query.
	 */
	public function __construct( $var = null ) {
		$skip = [ 'id', 'created', 'updated' ];
		$fields = [];
		foreach ( $this->getColumns() as $column => $value ) {
			if ( ! in_array( $column, $skip, true ) ) {
				$fields[ $column ] = $value;
			}
		}

		$this->applyKeys( $fields );

		// Process straight through if we were given a valid array.
		if ( is_array( $var ) || is_object( $var ) ) {
			// Apply keys to object.
			$this->applyKeys( $var );

			if ( $this->exists() ) {
				return true;
			}

			return false;
		}

		return $this->loadData( $var );
	}

	/**
	 * Load the data from the database!
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed      $var Generally the primary key to load up the model from the DB.
	 * @return Model|bool      Returns the current object.
	 */
	protected function loadData( $var = null ) {
		// Return false if var is invalid or not supplied.
		if ( null === $var ) {
			return false;
		}

		$query = aioseo()->core->db
			->start( $this->table )
			->where( $this->pk, $var )
			->limit( 1 )
			->output( 'ARRAY_A' );

		$result = $query->run();
		if ( ! $result || $result->nullSet() ) {
			return $this;
		}

		// Apply keys to object.
		$this->applyKeys( $result->result()[0] );

		return $this;
	}

	/**
	 * Take the keys from the result array and add them to the Model.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $array The array of keys and values to add to the Model.
	 * @return void
	 */
	protected function applyKeys( $array ) {
		if ( ! is_object( $array ) && ! is_array( $array ) ) {
			throw new \Exception( '$array must either be an object or an array.' );
		}

		foreach ( (array) $array as $key => $value ) {
			$key = trim( $key );
			$this->$key = $value;

			if ( null === $value && in_array( $key, $this->nullFields, true ) ) {
				continue;
			}

			if ( in_array( $key, $this->jsonFields, true ) ) {
				if ( $value ) {
					$this->$key = is_string( $value ) ? json_decode( $value ) : $value;
				}
				continue;
			}

			if ( in_array( $key, $this->booleanFields, true ) ) {
				$this->$key = (bool) $value;
				continue;
			}

			if ( in_array( $key, $this->numericFields, true ) ) {
				$this->$key = (int) $value;
				continue;
			}
		}
	}

	/**
	 * Let's filter out any properties we cannot save to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $key The table column.
	 * @return array       The array of valid columns for the database query.
	 */
	protected function filter( $key ) {
		$table   = aioseo()->core->db->prefix . $this->table;
		$results = aioseo()->core->db->execute( 'SHOW COLUMNS FROM `' . $table . '`', true );
		$fields  = [];
		$skip    = [ 'created', 'updated' ];
		$columns = $results->result();

		foreach ( $columns as $col ) {
			if ( ! in_array( $col->Field, $skip, true ) && array_key_exists( $col->Field, $key ) ) {
				$fields[ $col->Field ] = $key[ $col->Field ];
			}
		}

		return $fields;
	}

	/**
	 * Transforms the data to be null if it exists in the nullFields variables.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $data The data array to transform.
	 * @return array       The transformed data.
	 */
	protected function transform( $data, $set = false ) {
		foreach ( $this->nullFields as $field ) {
			if ( isset( $data[ $field ] ) && empty( $data[ $field ] ) ) {
				$data[ $field ] = null;
			}
		}

		foreach ( $this->booleanFields as $field ) {
			if ( isset( $data[ $field ] ) && '' === $data[ $field ] ) {
				unset( $data[ $field ] );
			} elseif ( isset( $data[ $field ] ) ) {
				$data[ $field ] = (bool) $data[ $field ] ? 1 : 0;
			}
		}

		if ( $set ) {
			return $data;
		}

		foreach ( $this->numericFields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$data[ $field ] = (int) $data[ $field ];
			}
		}

		foreach ( $this->jsonFields as $field ) {
			if ( isset( $data[ $field ] ) && ! aioseo()->helpers->isJsonString( $data[ $field ] ) ) {
				if ( is_array( $data[ $field ] ) && aioseo()->helpers->isArrayNumeric( $data[ $field ] ) ) {
					$data[ $field ] = array_values( $data[ $field ] );
				}
				$data[ $field ] = wp_json_encode( $data[ $field ] );
			}
		}

		return $data;
	}

	/**
	 * Sets a piece of data to the requested resource.
	 *
	 * @since 4.0.0
	 */
	public function set() {
		$args  = func_get_args();
		$count = func_num_args();

		if ( ! is_array( $args[0] ) && $count < 2 ) {
			throw new \Exception( 'The set method must contain at least 2 arguments: key and value. Or an array of data. Only one argument was passed and it was not an array.' );
		}

		$key   = $args[0];
		$value = ! empty( $args[1] ) ? $args[1] : null;

		// Make sure we have a key.
		if ( false === $key ) {
			return false;
		}

		// If it's not an array, make it one.
		if ( ! is_array( $key ) ) {
			$key = [ $key => $value ];
		}

		// Preprocess data.
		$key = $this->transform( $key, true );

		// Save the items in this object.
		foreach ( $key as $k => $v ) {
			if ( ! empty( $k ) ) {
				$this->$k = $v;
			}
		}
	}

	/**
	 * Delete the Model Resource itself.
	 *
	 * @since 4.0.0
	 *
	 * @return null
	 */
	public function delete() {
		if ( ! $this->exists() ) {
			return;
		}

		aioseo()->core->db
			->delete( $this->table )
			->where( $this->pk, $this->id )
			->run();

		return null;
	}

	/**
	 * Saves data to the requested resource.
	 *
	 * @since 4.0.0
	 */
	public function save() {
		$fields = $this->transform( $this->filter( (array) get_object_vars( $this ) ) );

		$id = null;
		if ( count( $fields ) > 0 ) {
			$pk = $this->pk;

			if ( isset( $this->$pk ) && '' !== $this->$pk ) {
				// PK specified.
				$pkv   = $this->$pk;
				$query = aioseo()->core->db
					->start( $this->table )
					->where( [ $pk => $pkv ] )
					->run();

				if ( ! $query->nullSet() ) {
					// Row exists in database.
					$fields['updated'] = gmdate( 'Y-m-d H:i:s' );
					aioseo()->core->db
						->update( $this->table )
						->set( $fields )
						->where( [ $pk => $pkv ] )
						->run();
					$id = $this->$pk;
				} else {
					// Row does not exist.
					$fields[ $pk ]     = $pkv;
					$fields['created'] = gmdate( 'Y-m-d H:i:s' );
					$fields['updated'] = gmdate( 'Y-m-d H:i:s' );

					$id = aioseo()->core->db
						->insert( $this->table )
						->set( $fields )
						->run()
						->insertId();

					if ( $id ) {
						$this->$pk = $id;
					}
				}
			} else {
				$fields['created'] = gmdate( 'Y-m-d H:i:s' );
				$fields['updated'] = gmdate( 'Y-m-d H:i:s' );

				$id = aioseo()->core->db
					->insert( $this->table )
					->set( $fields )
					->run()
					->insertId();

				if ( $id ) {
					$this->$pk = $id;
				}
			}
		}

		// Refresh the resource.
		$this->reset( $id );
	}

	/**
	 * Check if the model exists in the database.
	 *
	 * @since 4.0.0
	 *
	 * @return bool If the model exists, true otherwise false.
	 */
	public function exists() {
		return ( ! empty( $this->{$this->pk} ) ) ? true : false;
	}

	/**
	 * Resets a resource by forcing internal updates to be applied.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $id The resource ID.
	 * @return void
	 */
	public function reset( $id = null ) {
		$id = ! empty( $id ) ? $id : $this->{$this->pk};
		$this->__construct( $id );
	}

	/**
	 * Helper function to remove data we don't want serialized.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of data that we are okay with serializing.
	 */
	#[\ReturnTypeWillChange]
	// The attribute above omits a deprecation notice from PHP 8.1 that is thrown because the return type of jsonSerialize() isn't "mixed".
	// Once PHP 7.x is our minimum supported version, this can be removed in favour of overriding the return type in the method signature like this -
	// public function jsonSerialize() : array
	public function jsonSerialize() {
		$array = [];

		foreach ( $this->getColumns() as $column => $value ) {
			if ( in_array( $column, $this->hidden, true ) ) {
				continue;
			}

			$array[ $column ] = isset( $this->$column ) ? $this->$column : null;
		}

		return $array;
	}

	/**
	 * Retrieves the columns for the model.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of columns.
	 */
	public function getColumns() {
		if ( empty( self::$columns[ get_called_class() ] ) ) {
			self::$columns[ get_called_class() ] = [];

			// Let's set the columns that are available by default.
			$table   = aioseo()->core->db->prefix . $this->table;
			$results = aioseo()->core->db->start( $table )
				->output( 'OBJECT' )
				->execute( 'SHOW COLUMNS FROM `' . $table . '`', true )
				->result();

			foreach ( $results as $col ) {
				self::$columns[ get_called_class() ][ $col->Field ] = $col->Default;
			}

			if ( ! empty( $this->appends ) ) {
				foreach ( $this->appends as $append ) {
					self::$columns[ get_called_class() ][ $append ] = null;
				}
			}
		}

		return self::$columns[ get_called_class() ];
	}
}