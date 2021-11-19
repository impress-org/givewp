<?php

namespace Give\Session;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use stdClass;

/**
 * Class Access
 *
 * In legacy core session data load in array which contains multiple keys like give_purchase, receipt_access etc.
 * This class helps to convert them into objects. Every subclass will treat a specific key as group of session data.
 *
 * @package Give\Session
 */
abstract class Accessor
{
    /**
     * Session Id.
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * Session data as array.
     *
     * We use this array internally to perform database related operations.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Session data as object.
     * Session data in object format will be return when query.
     *
     * @var stdClass
     */
    protected $dataObj;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->get();
    }

    /**
     * Convert session data to object.
     *
     * @since 2.7.0
     *
     * @param array $data
     *
     * @return stdClass
     */
    protected function convertToObject($data)
    {
        $dataObj = new stdClass();
        $data = $this->renameArrayKeysToPropertyNames($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $dataObj->{$key} = $this->convertToObject($value);
                continue;
            }

            $dataObj->{$key} = $value;
        }

        return $dataObj;
    }

    /**
     * Get data from session.
     *
     * @since 2.7.0
     * @return stdClass
     */
    public function get()
    {
        if ($this->dataObj) {
            return $this->dataObj;
        }

        $this->data = Give()->session->get($this->sessionKey, $this->data);
        $this->dataObj = $this->convertToObject($this->data);

        return $this->dataObj;
    }

    /**
     * Get data from session.
     *
     * @since 2.7.0
     *
     * @param string $key
     *
     * @return stdClass
     */
    public function getByKey($key)
    {
        $result = null;

        if (null !== $this->dataObj) {
            $result = property_exists($this->dataObj, $key) ? $this->dataObj->{$key} : $result;
        }

        return $result;
    }

    /**
     * Save/Replace/Remove data into session
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return string
     */
    public function store($key, $data)
    {
        $this->validateData($data);

        if ( ! empty($this->data[$key])) {
            // Merge data.
            $this->data[$key] = array_merge(
                $this->data[$key],
                $data
            );
        } else {
            $this->data[$key] = $data;
        }

        return $this->set();
    }

    /**
     * Store data into session.
     *
     * @since 2.7.0
     * @return string
     */
    protected function set()
    {
        $this->dataObj = $this->convertToObject($this->data);

        return Give()->session->set($this->sessionKey, $this->data);
    }

    /**
     * Replace session data.
     *
     * @since 2.7.0
     *
     * @param mixed  $data
     *
     * @param string $key
     *
     * @return string
     */
    public function replace($key, $data)
    {
        $this->validateData($data);
        $this->data[$key] = $data;

        return $this->set();
    }

    /**
     * Delete session data.
     *
     * @since 2.7.0
     *
     * @param string $key
     *
     * @return string
     */
    public function delete($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this->set();
    }

    /**
     * Return session data in array format.
     *
     * @since 2.7.0
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Rename array key to property name
     *
     * @since 2.7.0
     *
     * @param array $data
     *
     * @return array
     */
    protected function renameArrayKeysToPropertyNames($data)
    {
        foreach ($data as $key => $value) {
            // Convert array key string to property name.
            // Remove other then char, dash, give related prefix and hyphen and prefix.
            $newName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
            $newName = preg_replace('/(-|_)?give(-|_)?/', '', $newName);
            $keyParts = preg_split('/(-|_)/', $newName);
            $keyParts = array_map('ucfirst', array_filter($keyParts));
            $newName = lcfirst(implode('', $keyParts));

            // Remove old key/value pair if renamed.
            if ($key !== $newName) {
                unset($data[$key]);
            }

            if (is_array($value)) {
                // Process array.
                $data[$newName] = $this->renameArrayKeysToPropertyNames($value);
                continue;
            }

            $data[$newName] = $value;
        }

        return $data;
    }

    /**
     * Validate data.
     *
     * @since 2.7.0
     *
     * @param mixed $data
     *
     */
    protected function validateData($data)
    {
        if (is_array($data) && isset($data[0])) {
            throw new InvalidArgumentException('Invalid value. Please pass an associative array');
        }
    }
}
