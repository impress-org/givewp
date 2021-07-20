<?php


namespace Give\Framework\FieldsAPI\Concerns;


trait MergeWithJsonSerializeFromTraits {
	/**
	 * Merge serialized data from traits using methods called "jsonSerialize[TRAIT NAME]".
	 *
	 * @unreleased
	 *
	 * @param array $baseSerializedData
	 *
	 * @return array
	 */
	protected function mergeWithJsonSerializeFromTraits( $baseSerializedData = [] ) {
		return array_reduce(
		// Merge the data that should be serialized from each trait.
			class_uses( $this ),
			function( $serializedDataAsArray, $fullyQualifiedTraitName ) {
				// Get the trait’s name from the fully qualified trait name
				$fullyQualifiedTraitNameAsArray = explode( '\\', $fullyQualifiedTraitName );
				$traitName                      = end( $fullyQualifiedTraitNameAsArray );

				// Merge the trait’s serialized data with the other serialized data.
				$traitSerializeMethod = [ $this, "jsonSerialize{$traitName}" ];
				if ( method_exists( ...$traitSerializeMethod ) ) {
					return array_merge(
						[
							$serializedDataAsArray,
							$traitSerializeMethod(),
						]
					);
				}

				// Otherwise, do nothing for the trait.
				return $serializedDataAsArray;
			},
			$baseSerializedData
		);
	}
}
