<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database utility class for AIOSEO.
 *
 * @since 4.0.0
 */
class Database {
	/**
	 * List of custom tables we support.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $customTables = [
		'aioseo_cache',
		'aioseo_crawl_cleanup_blocked_args',
		'aioseo_crawl_cleanup_logs',
		'aioseo_links',
		'aioseo_links_suggestions',
		'aioseo_notifications',
		'aioseo_posts',
		'aioseo_redirects',
		'aioseo_redirects_404',
		'aioseo_redirects_404_logs',
		'aioseo_redirects_hits',
		'aioseo_redirects_logs',
		'aioseo_terms',
		'aioseo_search_statistics_objects',
		'aioseo_revisions'
	];

	/**
	 * Holds $wpdb instance.
	 *
	 * @since 4.0.0
	 *
	 * @var \wpdb
	 */
	public $db = null;

	/**
	 * Holds $wpdb prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * The database table in use by this query.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * The sql statement (SELECT, INSERT, UPDATE, DELETE, etc.).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $statement = '';

	/**
	 * The limit clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var string|int
	 */
	private $limit = '';

	/**
	 * The group clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $group = [];

	/**
	 * The order by clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $order = [];

	/**
	 * The select clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $select = [];

	/**
	 * The set clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $set = [];

	/**
	 * Duplicate clause for the INSERT query.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	private $onDuplicate = [];

	/**
	 * Ignore clause for the INSERT query.
	 *
	 * @since 4.1.6
	 *
	 * @var array
	 */
	private $ignore = false;

	/**
	 * The where clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $where = [];

	/**
	 * The union clause for the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $union = [];

	/**
	 * The join clause for the SQL query.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $join = [];

	/**
	 * Determines whether the select statement should be distinct.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private $distinct = false;

	/**
	 * The order by direction for the query.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $orderDirection = 'ASC';

	/**
	 * The query string is populated after the __toString function is run.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $query = '';

	/**
	 * The sql query results are stored here.
	 *
	 * @since 4.0.0
	 *
	 * @var mixed
	 */
	private $result;

	/**
	 * The method in which $wpdb will output results.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $output = 'OBJECT';

	/**
	 * Whether or not to strip tags.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private $stripTags = false;

	/**
	 * Set which option to use to escape the SQL query.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $escapeOptions = 0;

	/**
	 * A cache of all queries and their results.
	 *
	 * @var array
	 */
	private $cache = [];

	/**
	 * Whether or not to reset the cached results.
	 *
	 * @var bool
	 */
	private $shouldResetCache = false;

	/**
	 * Constant for escape options.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	const ESCAPE_FORCE = 2;

	/**
	 * Constant for escape options.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	const ESCAPE_STRIP_HTML = 4;

	/**
	 * Constant for escape options.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	const ESCAPE_QUOTE = 8;

	/**
	 * List of model class instances.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $models = [];

	/**
	 * The last query that ran, stringified.
	 *
	 * @since 4.3.0
	 */
	public $lastQuery = '';

	/**
	 * Prepares the database class for use.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the DB class.
	 * This needs to be called after the class is instantiated or when switching between sites in a multisite environment.
	 * The latter is important because the prefix otherwise isn't updated.
	 *
	 * @since 4.6.1
	 *
	 * @return void
	 */
	public function init() {
		global $wpdb;
		$this->db            = $wpdb;
		$this->prefix        = $wpdb->prefix;
		$this->escapeOptions = self::ESCAPE_STRIP_HTML | self::ESCAPE_QUOTE;
	}

	/**
	 * If this is a clone, lets reset all the data.
	 *
	 * @since 4.0.0
	 */
	public function __clone() {
		// We need to reset the result separately as well since it is not in the default array.
		$this->reset( [ 'result' ] );
		$this->reset();
	}

	/**
	 * Gets all AIOSEO installed tables.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of custom AIOSEO tables.
	 */
	public function getInstalledTables() {
		$results = $this->db->get_results( 'SHOW TABLES', 'ARRAY_N' );

		return ! empty( $results ) ? wp_list_pluck( $results, 0 ) : [];
	}

	/**
	 * Get all the database info such as data size, index size, table list.
	 *
	 * @since 4.4.5
	 *
	 * @return array An array of the database info.
	 */
	public function getDatabaseInfo() {
		$tables       = [];
		$databaseSize = [];

		if ( defined( 'DB_NAME' ) ) {
			$databaseTableInformation = $this->db->get_results(
				$this->db->prepare(
					"SELECT
						table_name AS 'name',
						table_collation AS 'collation',
						engine AS 'engine',
						round( ( data_length / 1024 / 1024 ), 2 ) 'data',
						round( ( index_length / 1024 / 1024 ), 2 ) 'index'
					FROM information_schema.TABLES
					WHERE table_schema = %s
					ORDER BY name ASC;",
					DB_NAME
				)
			);

			$databaseSize = [
				'data'  => 0,
				'index' => 0,
			];

			$siteTablesPrefix = $this->db->get_blog_prefix( get_current_blog_id() );
			$globalTables     = $this->db->tables( 'global', true );
			foreach ( $databaseTableInformation as $table ) {
				// Only include tables matching the prefix of the current site, this is to prevent displaying all tables on a MS install not relating to the current.
				if ( is_multisite() && 0 !== strpos( $table->name, $siteTablesPrefix ) && ! in_array( $table->name, $globalTables, true ) ) {
					continue;
				}

				$tableType = ( 0 === strpos( $table->name, aioseo()->core->db->prefix . 'aioseo' ) ) ? 'aioseo' : 'other';

				$tables[ $tableType ][ $table->name ] = [
					'data'      => $table->data,
					'index'     => $table->index,
					'engine'    => $table->engine,
					'collation' => $table->collation
				];

				$databaseSize['data']  += $table->data;
				$databaseSize['index'] += $table->index;
			}
		}

		return [
			'tables' => $tables,
			'size'   => $databaseSize,
		];
	}

	/**
	 * Gets all columns from a table.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $table The name of the table to lookup columns for.
	 * @return array         An array of custom AIOSEO tables.
	 */
	public function getColumns( $table ) {
		$installedTables = json_decode( aioseo()->internalOptions->database->installedTables, true );
		$table           = $this->prefix . $table;

		if ( ! isset( $installedTables[ $table ] ) ) {
			return [];
		}

		if ( empty( $installedTables[ $table ] ) ) {
			$installedTables[ $table ]                           = $this->db->get_col( 'SHOW COLUMNS FROM `' . $table . '`' );
			aioseo()->internalOptions->database->installedTables = wp_json_encode( $installedTables );
		}

		return $installedTables[ $table ];
	}

	/**
	 * Checks if a table exists.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $table The name of the table.
	 * @return bool          Whether or not the table exists.
	 */
	public function tableExists( $table ) {
		$table           = $this->prefix . $table;
		$installedTables = json_decode( aioseo()->internalOptions->database->installedTables, true ) ?: [];
		if ( isset( $installedTables[ $table ] ) ) {
			return true;
		}

		$results = $this->db->get_results( "SHOW TABLES LIKE '" . $table . "'" );
		if ( empty( $results ) ) {
			return false;
		}

		$installedTables[ $table ]                           = [];
		aioseo()->internalOptions->database->installedTables = wp_json_encode( $installedTables );

		return true;
	}

	/**
	 * Checks if a column exists on a given table.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $table  The name of the table.
	 * @param  string $column The name of the column.
	 * @return bool           Whether or not the column exists.
	 */
	public function columnExists( $table, $column ) {
		if ( ! $this->tableExists( $table ) ) {
			return false;
		}

		$columns = $this->getColumns( $table );

		return in_array( $column, $columns, true );
	}

	/**
	 * Gets the size of a table in bytes.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $table The table to check.
	 * @return int           The size of the table in bytes.
	 */
	public function getTableSize( $table ) {
		$this->db->query( 'ANALYZE TABLE ' . $this->prefix . $table );
		$results = $this->db->get_results( '
			SELECT
				TABLE_NAME AS `table`,
				ROUND(SUM(DATA_LENGTH + INDEX_LENGTH)) AS `size`
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = "' . $this->db->dbname . '"
			AND TABLE_NAME = "' . $this->prefix . $table . '"
			ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;
		' );

		return ! empty( $results ) ? $results[0]->size : 0;
	}

	/**
	 * The query string in all its glory.
	 *
	 * @since 4.0.0
	 *
	 * @return string The actual query string.
	 */
	public function __toString() {
		switch ( strtoupper( $this->statement ) ) {
			case 'INSERT':
				$insert = 'INSERT ';
				if ( $this->ignore ) {
					$insert .= 'IGNORE ';
				}
				$insert   .= 'INTO ' . $this->table;
				$clauses   = [];
				$clauses[] = $insert;
				$clauses[] = 'SET ' . implode( ', ', $this->set );
				if ( ! empty( $this->onDuplicate ) ) {
					$clauses[] = 'ON DUPLICATE KEY UPDATE ' . implode( ', ', $this->onDuplicate );
				}

				break;
			case 'REPLACE':
				$clauses   = [];
				$clauses[] = "REPLACE INTO $this->table";
				$clauses[] = 'SET ' . implode( ', ', $this->set );

				break;
			case 'UPDATE':
				$clauses   = [];
				$clauses[] = "UPDATE $this->table";

				if ( count( $this->join ) > 0 ) {
					foreach ( (array) $this->join as $join ) {
						if ( is_array( $join[1] ) ) {
							$join_on = [];
							foreach ( (array) $join[1] as $left => $right ) {
								$join_on[] = "$this->table.`$left` = `{$join[0]}`.`$right`";
							}

							$clauses[] = "\t" . ( ( 'LEFT' === $join[2] || 'RIGHT' === $join[2] ) ? $join[2] . ' JOIN ' : 'JOIN ' ) . $join[0] . ' ON ' . implode( ' AND ', $join_on );
						} else {
							$clauses[] = "\t" . ( ( 'LEFT' === $join[2] || 'RIGHT' === $join[2] ) ? $join[2] . ' JOIN ' : 'JOIN ' ) . "{$join[0]} ON {$join[1]}";
						}
					}
				}

				$clauses[] = 'SET ' . implode( ', ', $this->set );

				if ( count( $this->where ) > 0 ) {
					$clauses[] = "WHERE 1 = 1 AND\n\t" . implode( "\n\tAND ", $this->where );
				}

				if ( count( $this->order ) > 0 ) {
					$clauses[] = 'ORDER BY ' . implode( ', ', $this->order );
				}

				if ( $this->limit ) {
					$clauses[] = 'LIMIT ' . $this->limit;
				}

				break;

			case 'TRUNCATE':
				$clauses   = [];
				$clauses[] = "TRUNCATE TABLE $this->table";
				break;

			case 'DELETE':
				$clauses   = [];
				$clauses[] = "DELETE FROM $this->table";

				if ( count( $this->where ) > 0 ) {
					$clauses[] = "WHERE 1 = 1 AND\n\t" . implode( "\n\tAND ", $this->where );
				}

				if ( count( $this->order ) > 0 ) {
					$clauses[] = 'ORDER BY ' . implode( ', ', $this->order );
				}

				if ( $this->limit ) {
					$clauses[] = 'LIMIT ' . $this->limit;
				}

				break;
			case 'SELECT':
			case 'SELECT DISTINCT':
			default:
				// Select fields.
				$clauses   = [];
				$distinct  = ( $this->distinct || stripos( $this->statement, 'DISTINCT' ) !== false ) ? 'DISTINCT ' : '';
				$select    = ( count( $this->select ) > 0 ) ? implode( ",\n\t", $this->select ) : '*';
				$clauses[] = "SELECT {$distinct}\n\t{$select}";

				// Select table.
				$clauses[] = "FROM $this->table";

				// Select joins.
				if ( ! empty( $this->join ) && count( $this->join ) > 0 ) {
					foreach ( (array) $this->join as $join ) {
						if ( is_array( $join[1] ) ) {
							$join_on = [];
							foreach ( (array) $join[1] as $left => $right ) {
								$join_on[] = "$this->table.`$left` = `{$join[0]}`.`$right`";
							}

							$clauses[] = "\t" . ( ( 'LEFT' === $join[2] || 'RIGHT' === $join[2] ) ? $join[2] . ' JOIN ' : 'JOIN ' ) . $join[0] . ' ON ' . implode( ' AND ', $join_on );
						} else {
							$clauses[] = "\t" . ( ( 'LEFT' === $join[2] || 'RIGHT' === $join[2] ) ? $join[2] . ' JOIN ' : 'JOIN ' ) . "{$join[0]} ON {$join[1]}";
						}
					}
				}

				// Select conditions.
				if ( count( $this->where ) > 0 ) {
					$clauses[] = "WHERE 1 = 1 AND\n\t" . implode( "\n\tAND ", $this->where );
				}

				// Union queries.
				if ( count( $this->union ) > 0 ) {
					foreach ( $this->union as $union ) {
						$keyword   = ( $union[1] ) ? 'UNION' : 'UNION ALL';
						$clauses[] = "\n$keyword\n\n$union[0]";
					}

					$clauses[] = '';
				}

				// Select groups.
				if ( count( $this->group ) > 0 ) {
					$clauses[] = 'GROUP BY ' . implode( ', ', $this->escapeColNames( $this->group ) );
				}

				// Select order.
				if ( count( $this->order ) > 0 ) {
					$orderFragments = [];
					foreach ( $this->escapeColNames( $this->order ) as $col ) {
						$orderFragments[] = ( preg_match( '/ (ASC|DESC|RAND\(\))$/i', $col ) ) ? $col : "$col $this->orderDirection";
					}

					$clauses[] = 'ORDER BY ' . implode( ', ', $orderFragments );
				}

				// Select limit.
				if ( $this->limit ) {
					$clauses[] = 'LIMIT ' . $this->limit;
				}

				break;
		}

		// @HACK for wpdb::prepare.
		$clauses[] = '/* %d = %d */';

		$this->query = str_replace( '%%d = %%d', '%d = %d', str_replace( '%', '%%', implode( "\n", $clauses ) ) );

		// Flag queries with double quotes down, but not if the double quotes are contained within a string value (like JSON).
		if ( aioseo()->isDev && preg_match( '/\{[^}]*\}(*SKIP)(*FAIL)|\[[^]]*\](*SKIP)(*FAIL)|\'[^\']*\'(*SKIP)(*FAIL)|\\"(*SKIP)(*FAIL)|"/i', $this->query ) ) {
			error_log(
				"Query with double quotes detected - this may cause isues when ANSI_QUOTES is enabled:\r\n" .
				$this->query . "\r\n" . wp_debug_backtrace_summary()
			);
		}

		$this->lastQuery = $this->query;

		return $this->query;
	}

	/**
	 * Shortcut method to return the query string.
	 *
	 * @since 4.0.0
	 *
	 * @return string The query string.
	 */
	public function query() {
		return $this->__toString();
	}

	/**
	 * Start a new Database Query.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @param  string   $statement      The MySQL statement for the query.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function start( $table = '', $includesPrefix = false, $statement = 'SELECT' ) {
		// Always reset everything when starting a new query.
		$this->reset();
		$this->table     = $includesPrefix ? $table : $this->prefix . $table;
		$this->statement = $statement;

		return $this;
	}

	/**
	 * Shortcut method for start with INSERT as the statement.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function insert( $table = '', $includesPrefix = false ) {
		return $this->start( $table, $includesPrefix, 'INSERT' );
	}

	/**
	 * Shortcut method for start with INSERT IGNORE as the statement.
	 *
	 * @since 4.1.6
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function insertIgnore( $table = '', $includesPrefix = false ) {
		$this->ignore = true;

		return $this->start( $table, $includesPrefix, 'INSERT' );
	}

	/**
	 * Shortcut method for start with UPDATE as the statement.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function update( $table = '', $includesPrefix = false ) {
		return $this->start( $table, $includesPrefix, 'UPDATE' );
	}

	/**
	 * Shortcut method for start with REPLACE as the statement.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function replace( $table = '', $includesPrefix = false ) {
		return $this->start( $table, $includesPrefix, 'REPLACE' );
	}

	/**
	 * Shortcut method for start with TRUNCATE as the statement.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function truncate( $table = '', $includesPrefix = false ) {
		return $this->start( $table, $includesPrefix, 'TRUNCATE' );
	}

	/**
	 * Shortcut method for start with DELETE as the statement.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $table          The name of the table without the WordPress prefix unless includes_prefix is true.
	 * @param  bool     $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                 Returns the Database class which can then be method chained for building the query.
	 */
	public function delete( $table = '', $includesPrefix = false ) {
		return $this->start( $table, $includesPrefix, 'DELETE' );
	}

	/**
	 * Adds a SELECT clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function select() {
		$args = (array) func_get_args();
		if ( count( $args ) === 1 && is_array( $args[0] ) ) {
			$args = $args[0];
		}

		$this->select = array_merge( $this->select, $this->escapeColNames( $args ) );

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function where() {
		$criteria = $this->prepArgs( func_get_args() );

		foreach ( (array) $criteria as $field => $value ) {
			if ( ! preg_match( '/[\(\)<=>!]+/', $field ) && false === stripos( $field, ' IS ' ) ) {
				$operator = ( is_null( $value ) ) ? 'IS' : '=';
				$escaped  = $this->escapeColNames( $field );
				$field    = array_pop( $escaped ) . ' ' . $operator;
			}

			if ( is_null( $value ) && false !== stripos( $field, ' IS ' ) ) {
				// WHERE `field` IS NOT NULL.
				$this->where[] = "$field NULL";
				continue;
			}

			if ( is_null( $value ) ) {
				// WHERE `field` IS NULL.
				$this->where[] = "$field NULL";
				continue;
			}

			if ( is_array( $value ) ) {
				$wheres = [];
				foreach ( (array) $value as $val ) {
					$wheres[] = sprintf( "$field %s", $this->escape( $val, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
				}

				$this->where[] = '(' . implode( ' OR ', $wheres ) . ')';
				continue;
			}

			$this->where[] = sprintf( "$field %s", $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
		}

		return $this;
	}

	/**
	 * Adds a complex WHERE clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function whereRaw() {
		$criteria = $this->prepArgs( func_get_args() );

		foreach ( (array) $criteria as $clause ) {
			$this->where[] = $clause;
		}

		return $this;
	}

	/**
	 * Adds a WHERE clause with all arguments sent separated by OR instead of AND inside a subclause.
	 * @example [ 'a' => 1, 'b' => 2 ] becomes "AND (a = 1 OR b = 2)"
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function whereOr() {
		$criteria = $this->prepArgs( func_get_args() );

		$or = [];
		foreach ( (array) $criteria as $field => $value ) {
			if ( ! preg_match( '/[\(\)<=>!]+/', $field ) && false === stripos( $field, ' IS ' ) ) {
				$operator = ( is_null( $value ) ) ? 'IS' : '=';
				$field    = $this->escapeColNames( $field );
				$field    = array_pop( $field ) . ' ' . $operator;
			}

			if ( is_null( $value ) && false !== stripos( $field, ' IS ' ) ) {
				// WHERE `field` IS NOT NULL.
				$or[] = "$field NULL";
				continue;
			}

			if ( is_null( $value ) ) {
				// WHERE `field` IS NULL.
				$or[] = "$field NULL";
			}

			$or[] = sprintf( "$field %s", $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
		}

		// Create our subclause, and add it to the WHERE array.
		$this->where[] = '(' . implode( ' OR ', $or ) . ')';

		return $this;
	}

	/**
	 * Adds a WHERE IN() clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function whereIn() {
		$criteria = $this->prepArgs( func_get_args() );

		foreach ( (array) $criteria as $field => $values ) {
			if ( ! is_array( $values ) ) {
				$values = [ $values ];
			}

			if ( count( $values ) === 0 ) {
				continue;
			}

			foreach ( $values as &$value ) {
				// Note: We can no longer check for `is_numeric` because a value like `61021e6242255` returns true and breaks the query.
				if ( is_int( $value ) || is_float( $value ) ) {
					// No change.
					continue;
				}

				if ( is_null( $value ) || 'null' === strtolower( $value ) ) {
					// Change to a true NULL value.
					$value = null;
					continue;
				}

				$value = sprintf( '%s', $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
			}

			$values = implode( ',', $values );
			$this->whereRaw( "$field IN ($values)" );
		}

		return $this;
	}

	/**
	 * Adds a WHERE NOT IN() clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function whereNotIn() {
		$criteria = $this->prepArgs( func_get_args() );

		foreach ( (array) $criteria as $field => $values ) {
			if ( ! is_array( $values ) ) {
				$values = [ $values ];
			}

			if ( count( $values ) === 0 ) {
				continue;
			}

			foreach ( $values as &$value ) {
				if ( is_numeric( $value ) ) {
					// No change.
					continue;
				}

				if ( is_null( $value ) || false !== stristr( $value, 'NULL' ) ) {
					// Change to a true NULL value.
					$value = null;
					continue;
				}

				$value = sprintf( '%s', $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
			}

			$values = implode( ',', $values );
			$this->whereRaw( "$field NOT IN($values)" );
		}

		return $this;
	}

	/**
	 * Adds a WHERE BETWEEN clause.
	 *
	 * @since 4.3.0
	 *
	 * @return Database  Returns the Database class which can be method chained for more query building.
	 */
	public function whereBetween() {
		$criteria = $this->prepArgs( func_get_args() );

		foreach ( (array) $criteria as $field => $values ) {
			if ( ! is_array( $values ) ) {
				$values = [ $values ];
			}

			if ( count( $values ) === 0 ) {
				continue;
			}

			foreach ( $values as &$value ) {
				// Note: We can no longer check for `is_numeric` because a value like `61021e6242255` returns true and breaks the query.
				if ( is_int( $value ) || is_float( $value ) ) {
					// No change.
					continue;
				}

				if ( is_null( $value ) || false !== stristr( $value, 'NULL' ) ) {
					// Change to a true NULL value.
					$value = null;
					continue;
				}

				$value = sprintf( '%s', $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
			}

			$values = implode( ' AND ', $values );
			$this->whereRaw( "$field BETWEEN $values" );
		}

		return $this;
	}

	/**
	 * Adds a LEFT JOIN clause.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $table          The name of the table to join to this query.
	 * @param  string|array $conditions     The conditions of the join clause.
	 * @param  bool         $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                     Returns the Database class which can be method chained for more query building.
	 */
	public function leftJoin( $table = '', $conditions = '', $includesPrefix = false ) {
		return $this->join( $table, $conditions, 'LEFT', $includesPrefix );
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $table          The name of the table to join to this query.
	 * @param  string|array $conditions     The conditions of the join clause.
	 * @param  string       $direction      This can take 'LEFT' or 'RIGHT' as arguments.
	 * @param  bool         $includesPrefix This determines if the table name includes the WordPress prefix or not.
	 * @return Database                     Returns the Database class which can be method chained for more query building.
	 */
	public function join( $table = '', $conditions = '', $direction = '', $includesPrefix = false ) {
		$this->join[] = [ $includesPrefix ? $table : $this->prefix . $table, $conditions, $direction ];

		return $this;
	}

	/**
	 * Add a UNION query.
	 *
	 * @since 4.0.0
	 *
	 * @param  Database|string $query    The query (Database object or query string) to be joined with.
	 * @param  bool            $distinct Set whether this union should be distinct or not.
	 * @return Database                  Returns the Database class which can be method chained for more query building.
	 */
	public function union( $query, $distinct = true ) {
		$this->union[] = [ $query, $distinct ];

		return $this;
	}

	/**
	 * Adds am GROUP BY clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function groupBy() {
		$args = (array) func_get_args();
		if ( count( $args ) === 1 && is_array( $args[0] ) ) {
			$args = $args[0];
		}

		$this->group = array_merge( $this->group, $args );

		return $this;
	}


	/**
	 * Adds am ORDER BY clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function orderBy() {
		// Normalize arguments.
		$args = (array) func_get_args();
		if ( count( $args ) === 1 && is_array( $args[0] ) ) {
			$args = $args[0];
		}

		// Escape the order by clause.
		$args = array_map( 'esc_sql', $args );

		if ( ! empty( $args[0] ) && true !== $args[0] ) {
			$this->order = array_merge( $this->order, $args );
		} else {
			// This allows for overwriting a preexisting order-by setting.
			array_shift( $args );
			$this->order = $args;
		}

		return $this;
	}

	/**
	 * Sets the sort direction for ORDER BY clauses.
	 *
	 * @since 4.0.0
	 *
	 * @param  string    $direction This sets the direction of the order by clause, default is 'ASC'.
	 * @return Database             Returns the Database class which can be method chained for more query building.
	 */
	public function orderDirection( $direction = 'ASC' ) {
		$this->orderDirection = $direction;

		return $this;
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @since 4.0.0
	 *
	 * @param  int      $limit  The amount of rows to limit the query to.
	 * @param  int      $offset The amount of rows the result of the query should be ofset with.
	 * @return Database         Returns the Database class which can be method chained for more query building.
	 */
	public function limit( $limit = 0, $offset = -1 ) {
		if ( ! $limit ) {
			return $this;
		}

		$this->limit = ( -1 === $offset ) ? $limit : "$offset, $limit";

		return $this;
	}

	/**
	 * Converts associative arrays to a SET argument.
	 *
	 * @since 4.1.5
	 *
	 * @param  array $args The arguments.
	 * @return array       The prepared arguments.
	 */
	private function prepareSet( $args ) {
		$args = $this->prepArgs( $args );

		$preparedSet = [];
		foreach ( (array) $args as $field => $value ) {
			if ( is_null( $value ) ) {
				$preparedSet[] = "`$field` = NULL";
				continue;
			}

			if ( is_array( $value ) ) {
				throw new \Exception( 'Cannot save an unserialized array in the database. Data passed was: ' . wp_json_encode( $value ) );
			}

			if ( is_object( $value ) ) {
				throw new \Exception( 'Cannot save an unserialized object in the database. Data passed was: ' . esc_html( $value ) );
			}

			$preparedSet[] = sprintf( "`$field` = %s", $this->escape( $value, $this->getEscapeOptions() | self::ESCAPE_QUOTE ) );
		}

		return $preparedSet;
	}

	/**
	 * Adds a SET clause.
	 *
	 * @since 4.0.0
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function set() {
		$this->set = array_merge( $this->set, $this->prepareSet( func_get_args() ) );

		return $this;
	}

	/**
	 * Adds an ON DUPLICATE clause.
	 *
	 * @since 4.1.5
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function onDuplicate() {
		$this->onDuplicate = array_merge( $this->onDuplicate, $this->prepareSet( func_get_args() ) );

		return $this;
	}

	/**
	 * Set the output for the query.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $output This can be one of the following: ARRAY_A | ARRAY_N | OBJECT | OBJECT_K.
	 * @return Database         Returns the Database class which can be method chained for more query building.
	 */
	public function output( $output = 'OBJECT' ) {
		if ( ! $output ) {
			$output = 'OBJECT';
		}

		$this->output = $output;

		return $this;
	}

	/**
	 * Reset the cache so we make sure the query gets to the DB.
	 *
	 * @since 4.1.6
	 *
	 * @return Database Returns the Database class which can be method chained for more query building.
	 */
	public function resetCache() {
		$this->shouldResetCache = true;

		return $this;
	}

	/**
	 * Run this query.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool     $reset  Whether to reset the results/query.
	 * @param  string   $return Determine which method to call on the $wpdb object
	 * @param  array    $params Optional extra parameters to pass to the db method call
	 * @return Database         Returns the Database class which can be method chained for more query building.
	 */
	public function run( $reset = true, $return = 'results', $params = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! in_array( $return, [ 'results', 'col', 'var', 'row' ], true ) ) {
			$return = 'results';
		}

		$prepare        = $this->db->prepare( $this->query(), 1, 1 );
		$queryHash      = sha1( $this->query() );
		$cacheTableName = $this->getCacheTableName();

		// Pull the result from the in-memory cache if everything checks out.
		if (
			! $this->shouldResetCache &&
			! in_array( $this->statement, [ 'INSERT', 'REPLACE', 'UPDATE', 'DELETE' ], true ) &&
			isset( $this->cache[ $cacheTableName ][ $queryHash ][ $return ] ) &&
			empty( $this->join )
		) {
			$this->result = $this->cache[ $cacheTableName ][ $queryHash ][ $return ];

			return $this;
		}

		switch ( $return ) {
			case 'col':
				$this->result = $this->db->get_col( $prepare );
				break;
			case 'var':
				$this->result = $this->db->get_var( $prepare );
				break;
			case 'row':
				$this->result = $this->db->get_row( $prepare );
				break;
			default:
				$this->result = $this->db->get_results( $prepare, $this->output );
		}

		if ( $reset ) {
			$this->reset();
		}

		$this->cache[ $cacheTableName ][ $queryHash ][ $return ] = $this->result;

		// Reset the cache trigger for the next run.
		$this->shouldResetCache = false;

		return $this;
	}

	/**
	 * Inject a count select statement and return the result.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $countColumn The column to count with. Defaults to '*' all.
	 * @return int                 The number of rows that were found.
	 */
	public function count( $countColumn = '*' ) {
		$usingGroup = ! empty( $this->group );
		$results    = $this->reset( [ 'select', 'order', 'limit' ] )
			->select( 'count(' . $countColumn . ') as count' )
			->run()
			->result();

		return 1 === $this->numRows() && ! $usingGroup
			? (int) $results[0]->count
			: $this->numRows();
	}

	/**
	 * Inject a count group select statement and return the result.
	 *
	 * @since 4.6.1
	 *
	 * @param  string $countDistinctColumn The column to count with. Defaults to '*' all.
	 * @return int                         The number of rows that were found.
	 */
	public function countDistinct( $countDistinctColumn = '*' ) {
		$countDistinctColumn = '*' !== $countDistinctColumn ? 'distinct( ' . $countDistinctColumn . ' )' : $countDistinctColumn;

		return $this->reset( [ 'select', 'order', 'limit' ] )
			->select( 'count(' . $countDistinctColumn . ') as count' )
			->run( true, 'var' )
			->result();
	}

	/**
	 * Returns the query results based on the value of the output property.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed This depends on what was set in the output property.
	 */
	public function result() {
		return $this->result;
	}

	/**
	 * Return a model model from a row.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $class The name of the model class to call.
	 * @return object        The model class instance.
	 */
	public function model( $class ) {
		$result = $this->result();

		return ! empty( $result )
			? ( is_array( $result )
				? new $class( (array) current( $result ) )
				: $result )
			: new $class();
	}

	/**
	 * Return an array of model class instancnes from the result.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $class  The name of the model class to call.
	 * @param  string $id     The ID of the index to use.
	 * @param  bool   $toJson The index if necessary.
	 * @return array          An array of model class instances.
	 */
	public function models( $class, $id = null, $toJson = false ) {
		if ( ! empty( $this->models ) ) {
			return $this->models;
		}

		$i      = 0;
		$models = [];
		foreach ( $this->result() as $row ) {
			$var   = ( null === $id ) ? $row : $row[ $id ];
			$class = new $class( $var );
			// Lets add the class to the array using the class ID.
			$models[ $class->id ] = $toJson ? $class->jsonSerialize() : $class;
			$i++;
		}

		$this->models = $models;

		return $this->models;
	}

	/**
	 * Returns the last error reported by MySQL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The last error message.
	 */
	public function lastError() {
		return $this->db->last_error;
	}

	/**
	 * Return the $wpdb insert_id from the last query.
	 *
	 * @since 4.0.0
	 *
	 * @return int The ID of the most recent INSERT query.
	 */
	public function insertId() {
		return $this->db->insert_id;
	}

	/**
	 * Return the $wpdb rows_affected from the last query.
	 *
	 * @since 4.0.0
	 *
	 * @return int The number of rows affected.
	 */
	public function rowsAffected() {
		return $this->db->rows_affected;
	}

	/**
	 * Return the $wpdb num_rows from the last query.
	 *
	 * @since 4.0.0
	 *
	 * @return int The count for the number of rows in the last query.
	 */
	public function numRows() {
		return $this->db->num_rows;
	}

	/**
	 * Check if the last query had any rows.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether there were any rows retrived by the last query.
	 */
	public function nullSet() {
		return ( $this->numRows() < 1 );
	}

	/**
	 * This will start a MySQL transaction. Be sure to commit or rollback!
	 *
	 * @since 4.0.0
	 */
	public function startTransaction() {
		$this->db->query( 'START TRANSACTION' );
	}

	/**
	 * This will commit a MySQL transaction. Used in conjunction with startTransaction.
	 *
	 * @since 4.0.0
	 */
	public function commit() {
		$this->db->query( 'COMMIT' );
	}

	/**
	 * This will rollback a MySQL transaction. Used in conjunction with startTransaction.
	 *
	 * @since 4.0.0
	 */
	public function rollback() {
		$this->db->query( 'ROLLBACK' );
	}

	/**
	 * Fast way to execute raw queries.
	 * NOTE: When using this method, all arguments must be sanitized manually!
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sql      The sql query to execute.
	 * @param  bool   $results  Whether to return the results or not.
	 * @param  bool   $useCache Whether to use the cache or not.
	 * @return mixed            Could be an array or object depending on the result set.
	 */
	public function execute( $sql, $results = false, $useCache = false ) {
		$this->lastQuery = $sql;
		$queryHash       = sha1( $sql );
		$cacheTableName  = $this->getCacheTableName();

		// Pull the result from the in-memory cache if everything checks out.
		if (
			$useCache &&
			! $this->shouldResetCache &&
			isset( $this->cache[ $cacheTableName ][ $queryHash ] )
		) {
			if ( $results ) {
				$this->result = $this->cache[ $cacheTableName ][ $queryHash ];
			}

			return $this;
		}

		if ( $results ) {
			$this->result = $this->db->get_results( $sql, $this->output );

			if ( $useCache ) {
				$this->cache[ $cacheTableName ][ $queryHash ] = $this->result;

				// Reset the cache trigger for the next run.
				$this->shouldResetCache = false;
			}

			return $this;
		}

		return $this->db->query( $sql );
	}

	/**
	 * Escape a value for safe use in SQL queries.
	 *
	 * @param string   $value   The value to be escaped.
	 * @param int|null $options The escape options.
	 * @return string           The escaped SQL value.
	 */
	public function escape( $value, $options = null ) {
		if ( is_array( $value ) ) {
			foreach ( $value as &$val ) {
				$val = $this->escape( $val, $options );
			}

			return $value;
		}

		$options = ( is_null( $options ) ) ? $this->getEscapeOptions() : $options;
		if ( ( $options & self::ESCAPE_STRIP_HTML ) !== 0 && isset( $this->stripTags ) && true === $this->stripTags ) {
			$value = wp_strip_all_tags( $value );
		}

		if (
			( ( $options & self::ESCAPE_FORCE ) !== 0 || php_sapi_name() === 'cli' ) ||
			( ( $options & self::ESCAPE_QUOTE ) !== 0 && ! is_int( $value ) )
		) {
			$value = esc_sql( $value );
			if ( ! is_int( $value ) ) {
				$value = "'$value'";
			}
		}

		return $value;
	}

	/**
	 * Returns the current escape options value.
	 *
	 * @since 4.0.0
	 *
	 * @return int The current escape options value.
	 */
	public function getEscapeOptions() {
		return $this->escapeOptions;
	}


	/**
	 * Sets the current escape options value.
	 *
	 * @since 4.0.0
	 *
	 * @param int $options The escape options value.
	 */
	public function setEscapeOptions( $options ) {
		$this->escapeOptions = $options;
	}

	/**
	 * Backtick-escapes an array of column and/or table names.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $cols An array of column names to be escaped.
	 * @return array       An array of escaped column names.
	 */
	private function escapeColNames( $cols ) {
		if ( ! is_array( $cols ) ) {
			$cols = [ $cols ];
		}

		foreach ( $cols as &$col ) {
			if ( false === stripos( $col, '(' ) && false === stripos( $col, ' ' ) && false === stripos( $col, '*' ) ) {
				if ( stripos( $col, '.' ) ) {
					list( $table, $c ) = explode( '.', $col );
					$col = "`$table`.`$c`";
					continue;
				}

				$col = "`$col`";
			}
		}

		return $cols;
	}

	/**
	 * Gets a variable list of function arguments and reformats them as needed for many of the functions of this class.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed $values This could be anything, but if used properly it usually is a string or an array.
	 * @return mixed         If the preparation was successful, it will return an array of arguments. Otherwise it could be anything.
	 */
	private function prepArgs( $values ) {
		$values = (array) $values;
		if ( ! is_array( $values[0] ) && count( $values ) === 2 ) {
			$values = [ $values[0] => $values[1] ];
		} elseif ( is_array( $values[0] ) && count( $values ) === 1 ) {
			$values = $values[0];
		}

		return $values;
	}

	/**
	 * Resets all the variables that make up the query.
	 *
	 * @since 4.0.0
	 *
	 * @param  array    $what Set which properties you want to reset. All are selected by default.
	 * @return Database       Returns the Database instance.
	 */
	public function reset(
		$what = [
			'table',
			'statement',
			'limit',
			'group',
			'order',
			'select',
			'set',
			'onDuplicate',
			'ignore',
			'where',
			'union',
			'distinct',
			'orderDirection',
			'query',
			'output',
			'stripTags',
			'models',
			'join'
		]
	) {
		// If we are not running a select query, let's bust the cache for this table.
		$selectStatements = [ 'SELECT', 'SELECT DISTINCT' ];
		if (
			! empty( $this->statement ) &&
			! in_array( $this->statement, $selectStatements, true )
		) {
			$this->bustCache( $this->getCacheTableName() );
		}

		foreach ( (array) $what as $var ) {
			switch ( $var ) {
				case 'group':
				case 'order':
				case 'select':
				case 'set':
				case 'onDuplicate':
				case 'where':
				case 'union':
				case 'join':
					$this->$var = [];
					break;
				case 'orderDirection':
					$this->$var = 'ASC';
					break;
				case 'ignore':
				case 'stripTags':
					$this->$var = false;
					break;
				case 'output':
					$this->$var = 'OBJECT';
					break;
				default:
					if ( isset( $this->$var ) ) {
						$this->$var = null;
					}
					break;
			}
		}

		return $this;
	}

	/**
	 * Returns the current value of one or more query properties.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|array  $what You can pass in an array of options to retrieve. By default it selects all if them.
	 * @return string|array       Returns the value of whichever variables are passed in.
	 */
	public function getQueryProperty(
		$what = [
			'table',
			'statement',
			'limit',
			'group',
			'order',
			'select',
			'set',
			'onDuplicate',
			'where',
			'union',
			'distinct',
			'orderDirection',
			'query',
			'output',
			'result'
		]
	) {
		if ( is_array( $what ) ) {
			$return = [];
			foreach ( (array) $what as $which ) {
				$return[ $which ] = $this->$which;
			}

			return $return;
		}

		return $this->$what;
	}

	/**
	 * Get a table name for the cache key.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $cacheTableName The table name to check against.
	 * @return string                 The cache key table name.
	 */
	private function getCacheTableName( $cacheTableName = '' ) {
		$cacheTableName = empty( $cacheTableName ) ? $this->table : $cacheTableName;

		foreach ( $this->customTables as $tableName ) {
			if ( false !== stripos( (string) $cacheTableName, $this->prefix . $tableName ) ) {
				$cacheTableName = $tableName;
				break;
			}
		}

		return $cacheTableName;
	}

	/**
	 * Busts the cache for the given table name.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $tableName The table name.
	 * @return void
	 */
	public function bustCache( $tableName = '' ) {
		if ( ! $tableName ) {
			// Bust all the cache.
			$this->cache = [];

			return;
		}

		unset( $this->cache[ $tableName ] );
	}

	/**
	 * In order to not have a conflict, we need to return a clone.
	 *
	 * @since 4.1.0
	 *
	 * @return Database The cloned Database instance.
	 */
	public function noConflict() {
		return clone $this;
	}

	/**
	 * Checks whether the given index exists on the given table.
	 *
	 * @since 4.4.8
	 *
	 * @param  string $tableName      The table name.
	 * @param  string $indexName      The index name.
	 * @param  bool   $includesPrefix Whether the table name includes the WordPress prefix or not.
	 * @return bool                   Whether the index exists or not.
	 */
	public function indexExists( $tableName, $indexName, $includesPrefix = false ) {
		$prefix    = $includesPrefix ? '' : $this->prefix;
		$tableName = strtolower( $prefix . $tableName );
		$indexName = strtolower( $indexName );

		$indexes = $this->db->get_results( "SHOW INDEX FROM `$tableName`" );
		foreach ( $indexes as $index ) {
			if ( empty( $index->Key_name ) ) {
				continue;
			}

			if ( strtolower( $index->Key_name ) === $indexName ) {
				return true;
			}
		}

		return false;
	}
}