<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 */
class Date extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'date';

	/**
	 * @param $name
	 */
	public function __construct( $name ) {
		parent::__construct( $name );

		$this->validationRules->rule( 'dateformat', 'mm/dd/yy' );
		$this->validationRules->rule( 'timeformat', 'h:mm tt' );
	}

	/**
	 * @unreleased
	 * @see https://api.jqueryui.com/datepicker/#utility-formatDate
	 *
	 * @param string $dateFormat
	 */
	public function dateFormat( $dateFormat ){
		$this->validationRules->rule( 'dateformat', $dateFormat );

		return $this;
	}

	/**
	 * @unreleased
	 */
	public function getDateFormat(){
		return $this->validationRules->getRule( 'dateformat' );
	}

	/**
	 * @unreleased
	 * @see https://api.jqueryui.com/datepicker/#utility-formatDate
	 *
	 * @param string $timeFormat
	 */
	public function timeFormat( $timeFormat ){
		$this->validationRules->rule( 'timeformat', $timeFormat );

		return $this;
	}

	/**
	 * @unreleased
	 */
	public function getTimeFormat(){
		return $this->validationRules->getRule( 'timeformat' );
	}
}
