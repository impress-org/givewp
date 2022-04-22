<?php

namespace Give\Log;

use DateTime;
use Give\Log\ValueObjects\LogType;

/**
 * Class LogModel
 * @package Give\Log
 *
 * @since 2.19.6 added debug
 * @since 2.10.0
 *
 * @method error(string $message, string $source, array $context = [])
 * @method warning(string $message, string $source, array $context = [])
 * @method notice(string $message, string $source, array $context = [])
 * @method success(string $message, string $source, array $context = [])
 * @method info(string $message, string $source, array $context = [])
 * @method http(string $message, string $source, array $context = [])
 * @method debug(string $message, string $source, array $context = [])
 */
class LogModel
{
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
     * @var array
     */
    private $context;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $date;

    /**
     * LogModel constructor.
     *
     * @param string   $type
     * @param string   $message
     * @param string   $category
     * @param string   $source
     * @param array    $context
     * @param int|null $logId
     * @param string   $date
     */
    public function __construct($type, $message, $category, $source, $context, $logId, $date)
    {
        $this->setType($type);
        $this->setDate($date);
        $this->category = $category;
        $this->source = $source;
        $this->context = $context;
        $this->message = $message;
        $this->id = $logId;
    }

    /**
     * Set log type
     * If log type is not supported, it will fallback to NOTICE type
     *
     * @param string $type
     */
    private function setType($type)
    {
        $this->type = in_array($type, LogType::getAll(), true)
            ? $type
            : LogType::getDefault();
    }

    /**
     * Set log message
     * If not defined, fallback to default value
     *
     * @param string|null $message
     */
    private function setMessage($message)
    {
        $this->message = is_null($message)
            ? esc_html__('Something went wrong', 'give')
            : $message;
    }

    /**
     * Set log date
     *
     * @param string $date
     */
    private function setDate($date)
    {
        $this->date = $this->isValidateDate($date)
            ? $date
            : date('Y-m-d H:i:s');
    }

    /**
     * Set log source
     * If not defined, fallback to default value
     *
     * @param string|null $source
     */
    private function setSource($source)
    {
        $this->source = is_null($source)
            ? esc_html__('Give Core', 'give')
            : $source;
    }

    /**
     * Helper method to check if given string is a valid date
     *
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    public function isValidateDate($date, $format = 'Y-m-d H:i:s')
    {
        $dateTime = DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get context data
     *
     * @param bool $jsonEncode
     *
     * @return string|array
     */
    public function getData($jsonEncode = false)
    {
        $data = [
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
        ];

        if ($jsonEncode) {
            return json_encode($data);
        }

        return $data;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    private function addContext($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            $value = print_r($value, true);
        }
        $this->context[$key] = $value;
    }

    /**
     * Add supplemental information to existing log.
     * Supplemental data will be added as a context with a key prefixed with the current timestamp.
     *
     * @param string $key
     * @param string $value
     */
    public function addSupplemental($key, $value)
    {
        $contextName = sprintf('[%s] %s', date('Y-m-d H:i:s'), $key);
        $this->addContext($contextName, $value);
    }

    /**
     * @param string $type
     * @param array  $args
     */
    public function __call($type, $args)
    {
        list ($message, $source, $context) = array_pad($args, 3, null);

        $this->setType($type);
        $this->setMessage($message);
        $this->setSource($source);

        // Set additional context
        if (is_array($context)) {
            foreach ($context as $key => $value) {
                $this->addContext($key, $value);
            }
        }

        $this->save();
    }

    /**
     * Save log record
     */
    public function save()
    {
        /**
         * var LogRepository $repository
         */
        $repository = give(LogRepository::class);

        if ($this->getId()) {
            $this->date = date('Y-m-d H:i:s');
            $repository->updateLog($this);
        } else {
            $this->id = $repository->insertLog($this);
        }
    }
}
