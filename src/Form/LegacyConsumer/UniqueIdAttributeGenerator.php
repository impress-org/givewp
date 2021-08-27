<?php

namespace Give\Form\LegacyConsumer;

/**
 * @unreleased
 */
class UniqueIdAttributeGenerator {
	/**
	 * @var array
	 */
	private $formCounter = [];

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 */
	private function increaseCounter( $formId ) {
		if ( ! isset( $this->formCounter[ $formId ] ) ) {
			$this->formCounter[ $formId ] = 1;

			return;
		}

		$this->formCounter[ $formId ] ++;
	}

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getId( $formId, $fieldName ) {
		$id = "give-$fieldName-$formId-{$this->getCounterValue( $formId )}";
		$this->increaseCounter( $formId );

		return $id;
	}

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 */
	private function getCounterValue( $formId ) {
		return ! empty( $this->formCounter[ $formId ] ) ? $this->formCounter[ $formId ] : 1;
	}
}
