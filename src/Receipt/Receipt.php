<?php

namespace Give\Receipt;

use ArrayAccess;
use Iterator;

/**
 * Class Receipt
 *
 * This class represent receipt as object.
 * Receipt can have multiple sections and sections can have multiple line items.
 *
 * @package Give\Receipt
 * @since 2.7.0
 */
abstract class Receipt implements Iterator, ArrayAccess
{
    /**
     * Iterator initial position.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Receipt Heading.
     *
     * @since 2.7.0
     * @var string $heading
     */
    public $heading = '';

    /**
     * Receipt message.
     *
     * @since 2.7.0
     * @var string $message
     */
    public $message = '';

    /**
     * Receipt details group class names.
     *
     * @since 2.7.0
     * @var array
     */
    protected $sectionList = [];

    /**
     * Array of section ids to use for Iterator.
     * Note: this property helps to iterate over associative array.
     *
     * @var int
     */
    protected $sectionIds = [];

    /**
     * Get receipt sections.
     *
     * @since 2.7.0
     * @return array
     */
    public function getSections()
    {
        return $this->sectionList;
    }

    /**
     * Add receipt section.
     *
     * @since 2.7.0
     *
     * @param string $position Position can be set either "before" or "after" to insert section at specific position.
     * @param string $sectionId
     *
     * @param array  $section
     *
     * @return Section
     *
     */
    public function addSection($section, $position = '', $sectionId = '')
    {
        $this->validateSection($section);

        // Add default label.
        $label = isset($section['label']) ? $section['label'] : '';

        $sectionObj = new Section($section['id'], $label);

        if (isset($this->sectionList[$sectionId]) && in_array($position, ['before', 'after'])) {
            // Insert line item at specific position.
            $tmp = [];
            $tmpIds = [];

            foreach ($this->sectionList as $id => $data) {
                if ('after' === $position) {
                    $tmp[$id] = $data;
                    $tmpIds[] = $id;
                }

                if ($id === $sectionId) {
                    $tmp[$sectionObj->id] = $sectionObj;
                    $tmpIds[] = $sectionObj->id;
                }

                if ('before' === $position) {
                    $tmp[$id] = $data;
                    $tmpIds[] = $id;
                }
            }

            $this->sectionList = $tmp;
            $this->sectionIds = $tmpIds;
        } else {
            $this->sectionList[$sectionObj->id] = $sectionObj;
            $this->sectionIds[] = $sectionObj->id;
        }

        return $sectionObj;
    }

    /**
     * Remove receipt section.
     *
     * @since 2.7.0
     *
     * @param string $sectionId
     *
     */
    public function removeSection($sectionId)
    {
        $this->offsetUnset($sectionId);
    }

    /**
     * Set section.
     *
     * @since 2.7.0
     *
     * @param array  $value Section Data.
     * @param string $offset Section ID.
     */
    public function offsetSet($offset, $value)
    {
        $this->addSection($value);
    }

    /**
     * Return whether or not session id exist in list.
     *
     * @since 2.7.0
     *
     * @param string $offset Section ID.
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->sectionList[$offset]);
    }

    /**
     * Remove section from list.
     *
     * @since 2.7.0
     *
     * @param string $offset Section ID.
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->sectionList[$offset]);
            $this->sectionIds = array_keys($this->sectionList);
        }
    }

    /**
     * Get section.
     *
     * @since 2.7.0
     *
     * @param string $offset Session ID.
     *
     * @return Section|null
     */
    public function offsetGet($offset)
    {
        return isset($this->sectionList[$offset]) ? $this->sectionList[$offset] : null;
    }

    /**
     * Return current data when iterate or data.
     *
     * @since 2.7.0
     * @return mixed
     */
    public function current()
    {
        return $this->sectionList[$this->sectionIds[$this->position]];
    }

    /**
     * Update iterator position.
     *
     * @since 2.7.0
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return iterator position.
     *
     * @since 2.7.0
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Return whether or not valid array position.
     *
     * @since 2.7.0
     * @return bool|void
     */
    public function valid()
    {
        return isset($this->sectionIds[$this->position]);
    }
}
