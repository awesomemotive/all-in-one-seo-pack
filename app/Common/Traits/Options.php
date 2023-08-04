<?php
namespace AIOSEO\Plugin\Common\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options trait.
 *
 * @since 4.0.0
 */
trait Options {
	/**
	 * Whether or not this instance is a clone.
	 *
	 * @since 4.1.4
	 *
	 * @var boolean
	 */
	public $isClone = false;

	/**
	 * Whether or not the options need to be saved to the DB.
	 *
	 * @since 4.1.4
	 *
	 * @var string
	 */
	public $shouldSave = false;

	/**
	 * The name to lookup the options with.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $optionsName = '';

	/**
	 * Holds the localized options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $localized = [];

	/**
	 * The group key we are working with.
	 *
	 * @since 4.0.0
	 *
	 * @var string|null
	 */
	protected $groupKey = null;

	/**
	 * Allows us to create unlimited number of sub groups.
	 * Like so: options->breadcrumbs->templates->taxonomies->tags->template
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $subGroups = [];

	/**
	 * Any arguments associated with a dynamic method.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The value to set on an option.
	 *
	 * @since 4.0.0
	 *
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Holds all the defaults after they have been merged.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $defaultsMerged = [];

	/**
	 * Holds a redirect link or slug.
	 *
	 * @since 4.0.17
	 *
	 * @var string
	 */
	protected $screenRedirection = '';

	/**
	 * Retrieve an option or null if missing.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name      The name of the property that is missing on the class.
	 * @param  array  $arguments The arguments passed into the method.
	 * @return mixed             The value from the options or default/null.
	 */
	public function __call( $name, $arguments = [] ) {
		if ( $this->setGroupKey( $name, $arguments ) ) {
			return $this;
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = $cachedOptions[ $this->groupKey ];
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = $defaults[ $subGroup ];
			}
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			$this->resetGroups();

			return ! empty( $this->arguments[0] )
				? $this->arguments[0]
				: $this->getDefault( $name, false );
		}

		if ( empty( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		$value = isset( $cachedOptions[ $this->groupKey ][ $name ]['value'] )
			? $cachedOptions[ $this->groupKey ][ $name ]['value']
			: (
				! empty( $this->arguments[0] )
					? $this->arguments[0]
					: $this->getDefault( $name, false )
			);

		$this->resetGroups();

		return $value;
	}

	/**
	 * Retrieve an option or null if missing.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The name of the property that is missing on the class.
	 * @return mixed        The value from the options or default/null.
	 */
	public function __get( $name ) {
		if ( 'type' === $name ) {
			$name = '_aioseo_type';
		}

		if ( $this->setGroupKey( $name ) ) {
			return $this;
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = $cachedOptions[ $this->groupKey ];
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = $defaults[ $subGroup ];
			}
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			$default = $this->getDefault( $name, false );
			$this->resetGroups();

			return $default;
		}

		if ( ! isset( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		$value = $this->getDefault( $name, false );

		if ( isset( $defaults[ $name ]['value'] ) ) {
			$preserveHtml = ! empty( $defaults[ $name ]['preserveHtml'] );
			if ( $preserveHtml ) {
				if ( is_array( $defaults[ $name ]['value'] ) ) {
					foreach ( $defaults[ $name ]['value'] as $k => $v ) {
						$defaults[ $name ]['value'][ $k ] = html_entity_decode( $v, ENT_NOQUOTES );
					}
				} else {
					$defaults[ $name ]['value'] = html_entity_decode( $defaults[ $name ]['value'], ENT_NOQUOTES );
				}
			}
			$value = $defaults[ $name ]['value'];

			// Localized value.
			if ( isset( $defaults[ $name ]['localized'] ) ) {
				$localizedKey = $this->groupKey;
				if ( ! empty( $this->subGroups ) ) {
					foreach ( $this->subGroups as $subGroup ) {
						$localizedKey .= '_' . $subGroup;
					}
				}

				$localizedKey .= '_' . $name;

				if ( ! empty( $this->localized[ $localizedKey ] ) ) {
					$value = $this->localized[ $localizedKey ];
					// We need to rebuild the keywords as a json string.
					if ( 'keywords' === $name ) {
						$keywords = explode( ',', $value );
						foreach ( $keywords as $k => $keyword ) {
							$keywords[ $k ] = [
								'label' => $keyword,
								'value' => $keyword
							];
						}

						$value = wp_json_encode( $keywords );
					}
				}
			}
		}

		$this->resetGroups();

		return $value;
	}

	/**
	 * Sets the option value and saves to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name  The name of the option.
	 * @param  mixed  $value The value to set.
	 * @return void
	 */
	public function __set( $name, $value ) {
		if ( $this->setGroupKey( $name, null, $value ) ) {
			return $this;
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = json_decode( wp_json_encode( $cachedOptions[ $this->groupKey ] ), true );
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = &$defaults[ $subGroup ];
			}
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			$default = $this->getDefault( $name, false );
			$this->resetGroups();

			return $default;
		}

		if ( empty( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		$preserveHtml               = ! empty( $defaults[ $name ]['preserveHtml'] );
		$localized                  = ! empty( $defaults[ $name ]['localized'] );
		$defaults[ $name ]['value'] = $this->sanitizeField( $this->value, $defaults[ $name ]['type'], $preserveHtml );

		if ( $localized ) {
			$localizedKey = $this->groupKey;
			if ( ! empty( $this->subGroups ) ) {
				foreach ( $this->subGroups as $subGroup ) {
					$localizedKey .= '_' . $subGroup;
				}
			}

			$localizedKey  .= '_' . $name;
			$localizedValue = $defaults[ $name ]['value'];

			if ( 'keywords' === $name ) {
				$keywords = json_decode( $localizedValue ) ? json_decode( $localizedValue ) : [];
				foreach ( $keywords as $k => $keyword ) {
					$keywords[ $k ] = $keyword->value;
				}

				$localizedValue = implode( ',', $keywords );
			}

			$this->localized[ $localizedKey ] = $localizedValue;
			update_option( $this->optionsName . '_localized', $this->localized );
		}

		$originalDefaults = json_decode( wp_json_encode( $cachedOptions[ $this->groupKey ] ), true );
		$pointer          = &$originalDefaults; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		foreach ( $this->subGroups as $subGroup ) {
			$pointer = &$pointer[ $subGroup ];
		}
		$pointer = $defaults;

		$cachedOptions[ $this->groupKey ] = $originalDefaults;
		aioseo()->core->optionsCache->setOptions( $this->optionsName, $cachedOptions );

		$this->resetGroups();

		$this->update();
	}

	/**
	 * Checks if an option is set or returns null if not.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The name of the option.
	 * @return mixed        True or null.
	 */
	public function __isset( $name ) {
		if ( $this->setGroupKey( $name ) ) {
			return $this;
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = $cachedOptions[ $this->groupKey ];
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = &$defaults[ $subGroup ];
			}
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			$this->resetGroups();

			return false;
		}

		if ( empty( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		$value = isset( $defaults[ $name ]['value'] )
			? false === empty( $defaults[ $name ]['value'] )
			: false;

			$this->resetGroups();

		return $value;
	}

	/**
	 * Unsets the option value and saves to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name  The name of the option.
	 * @return void
	 */
	public function __unset( $name ) {
		if ( $this->setGroupKey( $name ) ) {
			return $this;
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = json_decode( wp_json_encode( $cachedOptions[ $this->groupKey ] ), true );
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = &$defaults[ $subGroup ];
			}
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			$this->groupKey  = null;
			$this->subGroups = [];

			return;
		}

		if ( empty( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		if ( ! isset( $defaults[ $name ]['value'] ) ) {
			return;
		}

		unset( $defaults[ $name ]['value'] );

		$cachedOptions[ $this->groupKey ] = $defaults;
		aioseo()->core->optionsCache->setOptions( $this->optionsName, $cachedOptions );

		$this->resetGroups();

		$this->update();
	}

	/**
	 * Retrieves all options.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $include Keys to include.
	 * @param  array $exclude Keys to exclude.
	 * @return array          An array of options.
	 */
	public function all( $include = [], $exclude = [] ) {
		$originalGroupKey  = $this->groupKey;
		$originalSubGroups = $this->subGroups;

		// Make sure our dynamic options have loaded.
		$this->init();

		// Refactor options.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$refactored    = $this->convertOptionsToValues( $cachedOptions );

		$this->groupKey = null;

		if ( ! $originalGroupKey ) {
			return $this->allFiltered( $refactored, $include, $exclude );
		}

		if ( empty( $originalSubGroups ) ) {
			$all = $refactored[ $originalGroupKey ];

			return $this->allFiltered( $all, $include, $exclude );
		}

		$returnable = &$refactored[ $originalGroupKey ]; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		foreach ( $originalSubGroups as $subGroup ) {
			$returnable = &$returnable[ $subGroup ];
		}

		$this->resetGroups();

		return $this->allFiltered( $returnable, $include, $exclude );
	}

	/**
	 * Reset the current option to the defaults.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $include Keys to include.
	 * @param  array $exclude Keys to exclude.
	 * @return void
	 */
	public function reset( $include = [], $exclude = [] ) {
		$originalGroupKey  = $this->groupKey;
		$originalSubGroups = $this->subGroups;

		// Make sure our dynamic options have loaded.
		$this->init();

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );

		// If we don't have a group key set, it means we want to reset everything.
		if ( empty( $originalGroupKey ) ) {
			$groupKeys = array_keys( $cachedOptions );
			foreach ( $groupKeys as $groupKey ) {
				$this->groupKey = $groupKey;
				$this->reset();
			}

			// Since we just finished resetting everything, we can return early.
			return;
		}

		// If we need to set a sub-group, do that now.
		$keys     = array_merge( [ $originalGroupKey ], $originalSubGroups );
		$defaults = json_decode( wp_json_encode( $cachedOptions[ $originalGroupKey ] ), true );
		if ( ! empty( $originalSubGroups ) ) {
			foreach ( $originalSubGroups as $subGroup ) {
				$defaults = $defaults[ $subGroup ];
			}
		}

		// Refactor options.
		$resetValues = $this->resetValues( $defaults, $this->defaultsMerged, $keys, $include, $exclude );
		// We need to call our helper method instead of the built-in array_replace_recursive() function here because we want values to be replaced with empty arrays.
		$defaults = aioseo()->helpers->arrayReplaceRecursive( $defaults, $resetValues );

		$originalDefaults = json_decode( wp_json_encode( $cachedOptions[ $originalGroupKey ] ), true );
		$pointer          = &$originalDefaults; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		foreach ( $originalSubGroups as $subGroup ) {
			$pointer = &$pointer[ $subGroup ];
		}
		$pointer = $defaults;

		$cachedOptions[ $originalGroupKey ] = $originalDefaults;
		aioseo()->core->optionsCache->setOptions( $this->optionsName, $cachedOptions );

		$this->resetGroups();

		$this->update();
	}

	/**
	 * Resets all values in a group.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $defaults The defaults array we are currently working with.
	 * @param  array $values   The values to adjust.
	 * @param  array $keys     Parent keys for the current group we are parsing.
	 * @param  array $include  Keys to include.
	 * @param  array $exclude  Keys to exclude.
	 * @return array           The modified values.
	 */
	protected function resetValues( $values, $defaults, $keys = [], $include = [], $exclude = [] ) {
		$values = $this->allFiltered( $values, $include, $exclude );
		foreach ( $values as $key => $value ) {
			$option = $this->isAnOption( $key, $defaults, $keys );
			if ( $option ) {
				$values[ $key ]['value'] = isset( $values[ $key ]['default'] ) ? $values[ $key ]['default'] : null;
				continue;
			}

			$keys[]         = $key;
			$values[ $key ] = $this->resetValues( $value, $defaults, $keys );
			array_pop( $keys );
		}

		return $values;
	}

	/**
	 * Checks if the current group has an option or group.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $optionOrGroup The option or group to look for.
	 * @param  bool   $resetGroups   Whether or not to reset the groups after.
	 * @return bool                  True if it does, false if not.
	 */
	public function has( $optionOrGroup = '', $resetGroups = true ) {
		if ( 'type' === $optionOrGroup ) {
			$optionOrGroup = '_aioseo_type';
		}

		$originalGroupKey  = $this->groupKey;
		$originalSubGroups = $this->subGroups;

		static $hasInitialized = false;
		if ( ! $hasInitialized ) {
			$hasInitialized = true;
			$this->init();
		}

		// If we need to set a sub-group, do that now.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$defaults      = $originalGroupKey ? $cachedOptions[ $originalGroupKey ] : $cachedOptions;
		if ( ! empty( $originalSubGroups ) ) {
			foreach ( $originalSubGroups as $subGroup ) {
				$defaults = $defaults[ $subGroup ];
			}
		}

		if ( $resetGroups ) {
			$this->resetGroups();
		}

		if ( ! empty( $defaults[ $optionOrGroup ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filters the results based on passed in array.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $all     All the options to filter.
	 * @param  array $include Keys to include.
	 * @param  array $exclude Keys to exclude.
	 * @return array          The filtered options.
	 */
	private function allFiltered( $all, $include, $exclude ) {
		if ( ! empty( $include ) ) {
			return array_intersect_ukey( $all, $include, function ( $key1, $key2 ) use ( $include ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
				if ( in_array( $key1, $include, true ) ) {
					return 0;
				}

				return -1;
			} );
		}

		if ( ! empty( $exclude ) ) {
			return array_diff_ukey( $all, $exclude, function ( $key1, $key2 ) use ( $exclude ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
				if ( ! in_array( $key1, $exclude, true ) ) {
					return 0;
				}

				return -1;
			} );
		}

		return $all;
	}

	/**
	 * Gets the default value for an option.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The option name.
	 * @return mixed        The default value.
	 */
	public function getDefault( $name, $resetGroups = true ) {
		$defaults = $this->defaultsMerged[ $this->groupKey ];
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				if ( empty( $defaults[ $subGroup ] ) ) {
					return null;
				}
				$defaults = $defaults[ $subGroup ];
			}
		}

		if ( $resetGroups ) {
			$this->resetGroups();
		}

		if ( ! isset( $defaults[ $name ] ) ) {
			return null;
		}

		if ( empty( $defaults[ $name ]['type'] ) ) {
			return $this->setSubGroup( $name );
		}

		return isset( $defaults[ $name ]['default'] )
			? $defaults[ $name ]['default']
			: null;
	}

	/**
	 * Gets the defaults options.
	 *
	 * @since 4.1.3
	 *
	 * @return array An array of dafults.
	 */
	public function getDefaults() {
		return $this->defaults;
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string     $optionsName An optional option name to update.
	 * @param  string     $defaults    The defaults to filter the options by.
	 * @param  array|null $options     An optional options array.
	 * @return void
	 */
	public function update( $optionsName = null, $defaults = null, $options = null ) {
		$optionsName = empty( $optionsName ) ? $this->optionsName : $optionsName;
		$defaults    = empty( $defaults ) ? $this->defaults : $defaults;

		// First, we need to filter our options.
		$options = $this->filterOptions( $defaults, $options );

		// Refactor options.
		$refactored = $this->convertOptionsToValues( $options );

		$this->resetGroups();

		// The following needs to happen here (possibly a clone) as well as in the main instance.
		$originalInstance = $this->getOriginalInstance();

		// Update the DB options.
		aioseo()->core->optionsCache->setDb( $optionsName, $refactored );

		// Force a save here and in the main class.
		$this->shouldSave             = true;
		$originalInstance->shouldSave = true;
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.1.4
	 *
	 * @param  boolean $force       Whether or not to force an immediate save.
	 * @param  string  $optionsName An optional option name to update.
	 * @param  string  $defaults    The defaults to filter the options by.
	 * @return void
	 */
	public function save( $force = false, $optionsName = null, $defaults = null ) {
		if ( ! $this->shouldSave && ! $force ) {
			return;
		}

		$optionsName = empty( $optionsName ) ? $this->optionsName : $optionsName;
		$defaults    = empty( $defaults ) ? $this->defaults : $defaults;

		$this->update( $optionsName );

		// First, we need to filter our options.
		$options = $this->filterOptions( $defaults );

		// Refactor options.
		$refactored = $this->convertOptionsToValues( $options );

		$this->resetGroups();

		update_option( $optionsName, wp_json_encode( $refactored ) );
	}

	/**
	 * Filter options to match our defaults.
	 *
	 * @since 4.0.0
	 *
	 * @param  array      $defaults The defaults to use in filtering.
	 * @param  array|null $options  An optional options array.
	 * @return array                An array of filtered options.
	 */
	public function filterOptions( $defaults, $options = null ) {
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$options       = ! empty( $options ) ? $options : json_decode( wp_json_encode( $cachedOptions ), true );

		return $this->filterRecursively( $options, $defaults );
	}

	/**
	 * Filters options in a loop.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options  An array of options to filter.
	 * @param  array $defaults An array of defaults to filter against.
	 * @return array           A filtered array of options.
	 */
	public function filterRecursively( $options, $defaults ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}

		foreach ( $options as $key => $value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $options[ $key ] );
				continue;
			}

			if ( ! isset( $value['type'] ) ) {
				$options[ $key ] = $this->filterRecursively( $options[ $key ], $defaults[ $key ] );
				continue;
			}
		}

		return $options;
	}

	/**
	 * Sanitizes the value before allowing it to be saved.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed  $value The value to sanitize.
	 * @param  string $type  The type of sanitization to do.
	 * @return mixed         The sanitized value.
	 */
	public function sanitizeField( $value, $type, $preserveHtml = false ) {
		switch ( $type ) {
			case 'boolean':
				return (bool) $value;
			case 'html':
				return sanitize_textarea_field( $value );
			case 'string':
				return sanitize_text_field( $value );
			case 'number':
				return intval( $value );
			case 'array':
				$array = [];
				foreach ( (array) $value as $k => $v ) {
					$array[ $k ] = sanitize_text_field( $preserveHtml ? htmlspecialchars( $v, ENT_NOQUOTES, 'UTF-8' ) : $v );
				}

				return $array;
			case 'float':
				return floatval( $value );
		}
	}

	/**
	 * Checks to see if we need to set the group key. If so, will return true.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $name      The name of the option to set.
	 * @param  array   $arguments Any arguments needed if this was a method called.
	 * @param  mixed   $value     The value if we are setting an option.
	 * @return boolean            Whether or not we need to set the group key.
	 */
	private function setGroupKey( $name, $arguments = null, $value = null ) {
		$this->arguments = $arguments;
		$this->value     = $value;

		if ( empty( $this->groupKey ) ) {
			$groups = array_keys( $this->defaultsMerged );
			if ( in_array( $name, $groups, true ) ) {
				$this->groupKey = $name;

				return true;
			}

			$this->groupKey = $groups[0];
		}

		return false;
	}

	/**
	 * Sets the sub group key. Will set and return the instance.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $name      The name of the option to set.
	 * @param  array   $arguments Any arguments needed if this was a method called.
	 * @param  mixed   $value     The value if we are setting an option.
	 * @return object             The options object.
	 */
	private function setSubGroup( $name, $arguments = null, $value = null ) {
		if ( ! is_null( $arguments ) ) {
			$this->arguments = $arguments;
		}
		if ( ! is_null( $value ) ) {
			$this->value = $value;
		}

		$defaults = $this->defaultsMerged[ $this->groupKey ];
		if ( ! empty( $this->subGroups ) ) {
			foreach ( $this->subGroups as $subGroup ) {
				$defaults = $defaults[ $subGroup ];
			}
		}

		$groups = array_keys( $defaults );
		if ( in_array( $name, $groups, true ) ) {
			$this->subGroups[] = $name;
		}

		return $this;
	}

	/**
	 * Reset groups.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function resetGroups() {
		$this->groupKey  = null;
		$this->subGroups = [];
	}

	/**
	 * Converts an associative array of values into a structure
	 * that works with our defaults.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $defaults The defaults array we are currently working with.
	 * @param  array $values   The values to adjust.
	 * @param  array $keys     Parent keys for the current group we are parsing.
	 * @param  bool  $sanitize Whether or not we should sanitize the value.
	 * @return array           The modified values.
	 */
	protected function addValueToValuesArray( $defaults, $values, $keys = [], $sanitize = false ) {
		foreach ( $values as $key => $value ) {
			$option = $this->isAnOption( $key, $defaults, $keys );
			if ( $option ) {
				$preserveHtml   = ! empty( $option['preserveHtml'] );
				$newValue       = $sanitize ? $this->sanitizeField( $value, $option['type'], $preserveHtml ) : $value;
				$values[ $key ] = [
					'value' => $newValue
				];

				// If this is a localized string, let's save it to our localized options.
				if ( $sanitize && ! empty( $option['localized'] ) ) {
					$localizedKey = '';
					foreach ( $keys as $k ) {
						$localizedKey .= $k . '_';
					}

					$localizedKey  .= $key;
					$localizedValue = $newValue;
					if ( 'keywords' === $key ) {
						$keywords = json_decode( $localizedValue ) ? json_decode( $localizedValue ) : [];
						foreach ( $keywords as $k => $keyword ) {
							$keywords[ $k ] = $keyword->value;
						}

						$localizedValue = implode( ',', $keywords );
					}

					$this->localized[ $localizedKey ] = $localizedValue;
				}
				continue;
			}

			if ( ! is_array( $value ) ) {
				continue;
			}

			$keys[]         = $key;
			$values[ $key ] = $this->addValueToValuesArray( $defaults, $value, $keys, $sanitize );
			array_pop( $keys );
		}

		return $values;
	}

	/**
	 * Our options array has values (or defaults).
	 * This method converts them to how we would store them
	 * in the DB.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options The options array.
	 * @return array           The converted options array.
	 */
	public function convertOptionsToValues( $options, $optionKey = 'type' ) {
		foreach ( $options as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( ! isset( $value[ $optionKey ] ) ) {
				$options[ $key ] = $this->convertOptionsToValues( $value, $optionKey );
				continue;
			}

			$options[ $key ] = null;

			if ( isset( $value['value'] ) ) {
				$preserveHtml = ! empty( $value['preserveHtml'] );
				if ( $preserveHtml ) {
					if ( is_array( $value['value'] ) ) {
						foreach ( $value['value'] as $k => $v ) {
							$value['value'][ $k ] = html_entity_decode( $v, ENT_NOQUOTES );
						}
					} else {
						$value['value'] = html_entity_decode( $value['value'], ENT_NOQUOTES );
					}
				}
				$options[ $key ] = $value['value'];
				continue;
			}

			if ( isset( $value['default'] ) ) {
				$options[ $key ] = $value['default'];
			}
		}

		return $options;
	}

	/**
	 * This checks to see if the current array/option is really an option
	 * and not just another parent with a subgroup.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $key      The current array key we are working with.
	 * @param  array  $defaults The defaults array to check against.
	 * @param  array  $keys     The parent keys to loop through.
	 * @return bool             Whether or not this is an option.
	 */
	private function isAnOption( $key, $defaults, $keys ) {
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $k ) {
				$defaults = isset( $defaults[ $k ] ) ? $defaults[ $k ] : [];
			}
		}

		if ( isset( $defaults[ $key ]['type'] ) ) {
			return $defaults[ $key ];
		}

		return false;
	}

	/**
	 * Refreshes the options from the database.
	 *
	 * We need this during the migration to update through clones.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function refresh() {
		// Reset DB options to clear the cache.
		aioseo()->core->optionsCache->resetDb();
		$this->init();
	}

	/**
	 * Returns the DB options.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $optionsName The options name.
	 * @return array               The options.
	 */
	public function getDbOptions( $optionsName ) {
		$cache = aioseo()->core->optionsCache->getDb( $optionsName );
		if ( empty( $cache ) ) {
			$options = json_decode( get_option( $optionsName ), true );
			$options = ! empty( $options ) ? $options : [];

			// Set the cache.
			aioseo()->core->optionsCache->setDb( $optionsName, $options );
		}

		return aioseo()->core->optionsCache->getDb( $optionsName );
	}

	/**
	 * In order to not have a conflict, we need to return a clone.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool   $reInitialize Whether to reinitialize on the clone.
	 * @return object               The cloned Options object.
	 */
	public function noConflict( $reInitialize = false ) {
		$class          = clone $this;
		$class->isClone = true;

		if ( $reInitialize ) {
			$class->init();
		}

		return $class;
	}

	/**
	 * Get original instance. Since this could be a cloned object, let's get the original instance.
	 *
	 * @since 4.1.4
	 *
	 * @return self
	 */
	public function getOriginalInstance() {
		if ( ! $this->isClone ) {
			return $this;
		}

		$class      = new \ReflectionClass( get_called_class() );
		$optionName = aioseo()->helpers->toCamelCase( $class->getShortName() );

		if ( isset( aioseo()->{ $optionName } ) ) {
			return aioseo()->{ $optionName };
		}

		return $this;
	}
}