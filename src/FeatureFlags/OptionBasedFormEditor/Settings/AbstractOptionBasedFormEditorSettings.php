<?php

namespace Give\FeatureFlags\OptionBasedFormEditor\Settings;

use Give\FeatureFlags\OptionBasedFormEditor\OptionBasedFormEditor;

/**
 * @unreleased
 */
abstract class AbstractOptionBasedFormEditorSettings
{
    /**
     * @unreleased
     */
    abstract public function getNewDefaultSection(): string;

    /**
     * @unreleased
     */
    abstract public function getDisabledSectionIds(): array;

    /**
     * @unreleased
     */
    abstract public function getDisabledOptionIds(): array;

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    private function isOptionDisabled($option): bool
    {
        return $option && in_array($option, $this->getDisabledOptionIds());
    }

    /**
     * @unreleased
     */
    private function isCurrentSectionDisabled(): bool
    {
        return in_array(give_get_current_setting_section(), $this->getDisabledSectionIds());
    }
}
