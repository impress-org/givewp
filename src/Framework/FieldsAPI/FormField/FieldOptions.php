<?php

namespace Give\Framework\FieldsAPI\FormField;

use Give\Helpers\Arr;

trait FieldOptions {

	/** @var FieldOption[] */
	protected $options = [];

	/**
	 * Does the field type support options?
	 *
	 * @return bool
	 */
	public function supportsOptions() {
		return in_array(
			$this->getType(),
			[
				FieldTypes::TYPE_SELECT,
				FieldTypes::TYPE_RADIO,
			],
			true
		);
	}

	/**
	 * Set the options
	 *
	 * @param array $options
	 *
	 * @return $this
	 */
	public function options( array $options ) {
		// Reset options, since they are meant to be set immutably
		$this->options = [];

		// Determine if the provided options are an associative array before the loop to avoid extra work.
		$areOptionsAnAssocArray = Arr::isAssoc( $options );

		// Loop through the options and transform them to the proper format.
		foreach ( $options as $maybeLabel => $fieldOptionOrArrayOrValue ) {
			if ( $fieldOptionOrArrayOrValue instanceof FieldOption ) {
				// Since the given format matches the proper format (i.e. an array of FieldOption),
				// we can avoid additional cycles of the loop by just setting the value and breaking
				// out of it early.
				$this->options = $options;
				break;
			}

			if ( is_array( $fieldOptionOrArrayOrValue ) ) {
				// In this case, what is provided is an array with the value, then the label.
				$this->options[] = new FieldOption( ...$fieldOptionOrArrayOrValue );
			} else {
				// In this case, we have a value and maybe a label if the original array was associative.
				$this->options[] = new FieldOption(
					$fieldOptionOrArrayOrValue,
					$areOptionsAnAssocArray ? $maybeLabel : null
				);
			}
		}

		return $this;
	}

	/**
	 * Access the options
	 *
	 * @return FieldOption[]
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Walk through the options
	 *
	 * @unreleased
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function walkOptions( callable $callback ) {
		foreach ( $this->options as $option ) {
			// Call the callback for each option.
			if ( $callback( $option ) === false ) {
				// Returning false breaks the loop.
				break;
			}
		}
	}
}
