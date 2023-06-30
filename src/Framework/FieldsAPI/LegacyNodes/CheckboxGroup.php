<?php

namespace Give\Framework\FieldsAPI\LegacyNodes;

use Give\Framework\FieldsAPI\Concerns;
use Give\Framework\FieldsAPI\Field;

/**
 * This class is a legacy node for the old Form Field Manager Checkbox field. It should not be used in any other context
 * and will be eventually be removed.
 *
 * @since 2.28.0 Moved here to discourage future use
 * @since 2.12.0
 */
class CheckboxGroup extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasOptions;
    use Concerns\HasPlaceholder;

    const TYPE = 'legacy-checkbox-group';

    /**
     * Helper for marking the checkbox as checked by default
     *
     * @since 2.12.0
     *
     * @param bool|callable $isChecked
     *
     * @return $this
     */
    public function checked($isChecked = true)
    {
        $default = is_callable($isChecked) ? $isChecked() : $isChecked;
        $this->defaultValue((bool)$default);

        return $this;
    }

    /**
     * Returns whether the checkbox is checked by default
     *
     * @since 2.12.0
     *
     * @return bool
     */
    public function isChecked()
    {
        return (bool)$this->defaultValue;
    }
}
