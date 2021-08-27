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

	/**
	 * @var string
	 */
	const TYPE = 'date';

	/**
	 * @var string
	 */
	protected $dateFormat = 'mm/dd/yy';

	/**
	 * @var string
	 */
	protected $timeFormat = '';

	/**
	 * @unreleased
	 * @see https://api.jqueryui.com/datepicker/#utility-formatDate
	 *
	 * @param string $dateFormat
	 */
	public function dateFormat( $dateFormat ){
		$this->dateFormat = $dateFormat;

		return $this;
	}

	/**
	 * @unreleased
	 */
	public function getDateFormat(){
		return $this->dateFormat;
	}

	/**
	 * @unreleased
	 * @see https://api.jqueryui.com/datepicker/#utility-formatDate
	 *
	 * @param string $timeFormat
	 */
	public function timeFormat( $timeFormat ){
		$this->timeFormat = $timeFormat;

		return $this;
	}

	/**
	 * @unreleased
	 */
	public function getTimeFormat(){
		return $this->timeFormat;
	}
}
