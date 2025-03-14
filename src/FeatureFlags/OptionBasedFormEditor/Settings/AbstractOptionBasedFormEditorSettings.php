<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

use Give\FeatureFlags\OptionBasedFormEditor\OptionBasedFormEditor;

/**
 * @since 3.18.0
 */
abstract class AbstractOptionBasedFormEditorSettings
{
    /**
     * @since 3.18.0
     */
    abstract public function getDisabledOptionIds(): array;

    /**
     * @since 3.18.0
     */
    public function getDisabledSectionIds(): array
    {
        return [];
    }

    /**
     * @since 3.18.0
     */
    public function getNewDefaultSection(): string
    {
        return '';
    }

    /**
     * @since 3.18.0
     */
    final public function maybeDisableSections(array $sections): array
    {
        if (OptionBasedFormEditor::isEnabled()) {
            return $sections;
        }

        foreach ($sections as $key => $value) {
            if (in_array($key, $this->getDisabledSectionIds())) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    /**
     * @since 3.18.0
     */
    final public function maybeDisableOptions(array $options): array
    {
        foreach ($options as $key => $value) {
            if ( ! $this->isOptionDisabled($value['id']) && ! $this->isCurrentSectionDisabled()) {
                continue;
            }

            if (OptionBasedFormEditor::isEnabled()) {
                $options[$key]['name'] .= isset($value['name']) ? OptionBasedFormEditor::helperText() : '';
            } else {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * @since 3.18.0
     */
    final public function maybeSetNewDefaultSection($currentSection)
    {
        if (OptionBasedFormEditor::isEnabled()) {
            return $currentSection;
        }

        $newDefaultSection = $this->getNewDefaultSection();

        return ! empty($newDefaultSection) && $newDefaultSection != $currentSection ? $newDefaultSection : $currentSection;
    }

    /**
     * @since 3.18.0
     */
    private function isOptionDisabled($option): bool
    {
        return $option && in_array($option, $this->getDisabledOptionIds());
    }

    /**
     * @since 3.18.0
     */
    private function isCurrentSectionDisabled(): bool
    {
        return in_array(give_get_current_setting_section(), $this->getDisabledSectionIds());
    }
}
