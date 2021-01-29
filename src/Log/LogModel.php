<?php

namespace Give\Log;

/**
 * Class LogModel
 * @package Give\Log
 *
 * @method error( string $message, string $source, array $context = [] )
 * @method warning( string $message, string $source, array $context = [] )
 * @method notice( string $message, string $source, array $context = [] )
 * @method success( string $message, string $source, array $context = [] )
 * @method info( string $message, string $source, array $context = [] )
 * @method http( string $message, string $source, array $context = [] )
 */
class LogModel {
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string
	 */
	private $category;

	/**
	 * @var string
	 */
	private $source;

	/**
	 * @var string|null
	 */
	private $migrationId;

	/**
	 * @var array
	 */
	private $context;

	/**
	 * @var int|null
	 */
	private $id;

	/**
	 * LogModel constructor.
	 *
	 * @param  string  $type
	 * @param  string  $message
	 * @param  string  $category
	 * @param  string  $source
	 * @param  string|null  $migrationId
	 * @param  array  $context
	 * @param  int|null $logId
	 */
	public function __construct( $type, $message, $category, $source, $migrationId, $context, $logId ) {
		$this->setType( $type );
		$this->category    = $category;
		$this->source      = $source;
		$this->migrationId = $migrationId;
		$this->context     = $context;
		$this->message     = $message;
		$this->id          = $logId;
	}

	/**
	 * @param string $type
	 */
	public function setType( $type ) {
		$this->type = array_key_exists( $type, LogType::getAllTypes() )
			? $type
			: LogType::NOTICE;
	}

	/**
	 * Set log message
	 * If not defined, fallback to default value
	 *
	 * @param string|null $message
	 */
	public function setMessage( $message ) {
		$this->message = is_null( $message )
			? esc_html__( 'Something went wrong', 'give' )
			: $message;
	}

	/**
	 * Set log source
	 * If not defined, fallback to default value
	 *
	 * @param string|null $source
	 */
	public function setSource( $source ) {
		$this->source = is_null( $source )
			? esc_html__( 'Give Core', 'give' )
			: $source;
	}

	 /**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @return string|null
	 */
	public function getMigrationId() {
		return $this->migrationId;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return array
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Get context data
	 *
	 * @param  bool  $jsonEncode
	 *
	 * @return string|array
	 */
	public function getData( $jsonEncode = false ) {
		$data = [
			'message' => $this->getMessage(),
			'context' => $this->getContext(),
		];

		if ( $jsonEncode ) {
			return json_encode( $data );
		}

		return $data;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function addContext( $key, $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = print_r( $value, true );
		}
		$this->context[ $key ] = $value;
	}

	/**
	 * @param string $type
	 * @param array $args
	 */
	public function __call( $type, $args ) {
		list ( $message, $source, $context ) = array_pad( $args, 3, null );

		$this->setType( $type );
		$this->setMessage( $message );
		$this->setSource( $source );

		// Set additional context
		if ( is_array( $context ) ) {
			foreach ( $context as $key => $value ) {
				$this->addContext( $key, $value );
			}
		}

		$this->save();
	}

	/**
	 * Save log record
	 */
	public function save() {
		give( LogRepository::class )->insertLog( $this );
	}
}
