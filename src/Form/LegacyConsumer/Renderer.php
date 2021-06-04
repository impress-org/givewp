<?php

namespace Give\Form\LegacyConsumer;

use DOMDocument;
use DOMElement;
use DOMNode;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FormField\FieldTypes;

/**
 * Is the array associative?
 *
 * Stolen from Illuminate\Support\Arr
 *
 * @param array $array
 *
 * @return bool
 */
function isAssoc( array $array ) {
	$keys = array_keys( $array );
	// The keys for
	return array_keys( $keys ) !== $keys;
}

/**
 * Class names helper
 *
 * @param $classNames
 *
 * @return string
 */
function cx( ...$classNames ) {
	$classList = [];

	array_walk(
		$classNames,
		static function ( $item ) use ( $classList ) {
			if ( is_string( $item ) ) {
				$classList[] = $item;
			}

			if ( is_array( $item ) && isAssoc( $item ) ) {
				array_walk(
					$item,
					static function ( $enabled, $className ) use ( $classList ){
						if ( $enabled ) {
							$classList[] = $className;
						}
					}
				);
			}
		}
	);

	return implode( ' ', $classList );
}

/**
 * DOMDocument instance helper
 *
 * Works kind of like a singleton
 *
 * @param $dom DOMDocument|null
 *
 * @return DOMDocument
 */
function dom( DOMDocument $dom = null ) {
	static $instance;

	// If the $dom param is set, then set that to the instance.
	if ( isset( $dom ) && $dom !== $instance ) {
		$instance = $dom;
	}

	// If the $instance is not set, create a new instance.
	if ( ! $instance instanceof DOMDocument ) {
		$instance = new DOMDocument();
	}

	return $instance;
}

/**
 * "Hyperscript" implementation, i.e. a declarative `DOMDocument::createElement`
 *
 * @param string $tagName
 * @param $attributes Attributes can be omitted and this can be used for the first child
 * @param $children
 *
 * return DOMElement
 */
function h( string $tagName, $attributes = [], ...$children ) {
	$element = dom()->createElement( $tagName );

	// If the attributes param is an associative array, then it truly is
	// attributes which need to be set.
	if ( is_array( $attributes ) && isAssoc( $attributes ) ) {
		array_walk(
			$attributes,
			static function ( $value, $attribute ) use ( $element ) {
				$element->setAttribute( $attribute, (string) $value );
			}
	   	);
	}
	// Otherwise, it is the start of the children array.
	else {
		$children = array_merge( [ $attributes ], $children );
	}

	// Append all children filtering null and booleans
	// Creates text nodes for strings and other types which cast to strings (i.e. numbers)
	array_walk_recursive(
		$children,
		static function ( $child ) use ( $element ) {
			if ( ! is_null( $child ) && ! is_bool( $child ) ) {
				$element->appendChild( $child instanceof DOMNode ? $child : dom()->createTextNode( (string) $child) );
			}
		}
	);

	return $element;
}

/**
 * Used by the legacy consumer to render the provided field collection
 */
class Renderer {
	/**
	 * Render the field collection
	 */
	public static function render( FieldCollection $collection ) {
		// Create a new document to use
		dom( new DOMDocument() );

		// Walk through the field collection and construct the DOM
		$collection->walk(
			static function ( FormField $field ) {
				$input = $field->getType() === FieldTypes::TYPE_RADIO
					? static::radioInput( $field )
					: static::baseInput( $field );

				// Most fields which visually display will need to use the wrapper
				dom()->appendChild(
					static::deriveConfigFromField( $field )[ 'useWrapper']
						? static::fieldWrapper( $field, $input )
						: $input
				);
			}
		);

		// Render the DOM as HTML
		echo dom()->saveHTML();
	}

	/**
	 * @param FormField $field
	 *
	 * @return DOMElement
	 */
	private static function baseInput( FormField $field ) {
		$config = (object) static::deriveConfigFromField( $field );

		return h(
			$config->elementType,
			array_merge(
				$field->getAttributes(),
				[
					'type'     => $config->inputType,
					'name'     => $field->getName(),
					'id'       => "give-{$field->getName()}",
					'class'    => cx(
						[
							'give-input' => true,
							'required'   => $field->isRequired(),
						]
					),
					'required' => $field->isRequired(),
					'readonly' => $field->isReadOnly(),
					'value'    => $field->getDefaultValue(),
				]
			),
			$field->getType() === FieldTypes::TYPE_SELECT
				? array_map(
					static function ( $label, $value ) {
						return h( 'option', compact( 'value' ), $label );
					},
					array_keys( $options = $field->getOptions() ),
					array_values( $options )
				)
				: null
		);
	}

	/**
	 * @param FormField $field
	 *
	 * @return DOMElement
	 */
	private static function radioInput( FormField $field ) {
		return h(
			'fieldset',
			// The legend is for semantics, but not visuals
			h( 'legend', [ 'class' => 'screen-reader-text' ], $labelContent = static::makeLabelContent( $field ) ),
			// This is for visuals, but excluded from screen readers.
			h( 'div', [ 'class' => 'give-label', 'aria-hidden' => true ], $labelContent ),
			// Add the radio inputs
			array_map(
				static function ( $option, $value ) use ( $field ) {
					// TODO: figure out the selected option
					return h(
						'label',
						h(
							'input',
							[
								'type'  => 'radio',
								'name'  => $field->getName(),
								'value' => $value,
							]
						),
						$option
					);
				},
				array_keys( $options = $field->getOptions() ),
				array_values( $options )
			)
		);
	}

	/**
	 * @param FormField $field
	 * @param DOMElement $input
	 *
	 * @return DOMElement
	 */
	private static function fieldWrapper( FormField $field, DOMElement $input ) {
		return h(
			'div',
			[
				// TODO: determine if the row width should be configurable. Previous FFM functionality says, yes.
				'class'           => 'form-row form-row-wide',
				'data-field-name' => $field->getName(),
				'data-field-type' => $field->getType(),
			],
			static::deriveConfigFromField( $field )[ 'useLabel' ]
				? $field->getType() === FieldTypes::TYPE_CHECKBOX
					// Checkbox inputs should be wrapped inside their label (which shouldn’t have the regular label styles).
					? h( 'label', $input, static::makeLabelContent( $field ) )
					// Otherwise, place the label before the input and reference it with `for`.
					: [
						h(
							'label',
							[
								'for'   => $input->getAttribute( 'id' ),
								'class' => 'give-label',
							],
							static::makeLabelContent( $field )
						),
						$input,
					]
				// Render the input without the label
				: $input
		);
	}

	/**
	 * This can be spread as children in createElement
	 *
	 * @param FormField $field
	 *
	 * @return array
	 */
	private static function makeLabelContent( Formfield $field ) {
		$content = [ $field->getLabel() ];

		if ( $field->isRequired() ) {
			$content[] = ' '; // For spacing
			$content[] = h( 'span', [ 'class' => 'give-required-indicator' ], '*' );
		}

		if ( $helpText = $field->getHelpText() ) {
			$content[] = ' '; // For spacing
			$content[] = h(
				'span',
				[
					'class'      => 'give-tooltip hint--top hint--medium hint--bounce',
					'aria-label' => $helpText,
				],
				h( 'i', [ 'class' => 'give-icon give-icon-question' ] )
			);
		}

		return $content;
	}

	/**
	 * A helper for concatenating class names
	 *
	 * @param $classNames
	 *
	 * @return string
	 */
	/**
	 * Derive the render config from the field type
	 *
	 * @param FormField $field
	 *
	 * @return array
	 */
	private static function deriveConfigFromField( FormField $field ) {
		return [
			FieldTypes::TYPE_HIDDEN      => [
				'useWrapper'  => false,
				'useLabel'    => false,
				'elementType' => 'input',
				'inputType'   => 'hidden',
			],
			FieldTypes::TYPE_TEXTAREA    => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'textarea',
				'inputType'   => null,
			],
			FieldTypes::TYPE_TEXT        => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'text',
			],
			FieldTypes::TYPE_EMAIL       => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'email',
			],
			FieldTypes::TYPE_PHONE       => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'tel',
			],
			FieldTypes::TYPE_URL         => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'text',
			],
			FieldTypes::TYPE_DATE        => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'text',
			],
			FieldTypes::TYPE_CHECKBOX    => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'checkbox',
			],
			FieldTypes::TYPE_SELECT      => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'select',
				'inputType'   => 'option',
			],
			FieldTypes::TYPE_RADIO       => [
				'useWrapper'  => true,
				'useLabel'    => false,
				'elementType' => 'input',
				'inputType'   => 'radio',
			],
			FieldTypes::TYPE_FILE        => [
				'useWrapper'  => true,
				'useLabel'    => true,
				'elementType' => 'input',
				'inputType'   => 'text',
			],
		][ $field->getType() ];
	}
}
