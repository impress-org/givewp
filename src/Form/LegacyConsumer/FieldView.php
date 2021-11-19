<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Types;

/**
 * @since 2.10.2
 * @since 2.14.0 Add field classes hook for setting custom class names on the wrapper.
 */
class FieldView
{
    const INPUT_TYPE_ATTRIBUTES = [
        Types::PHONE => 'tel',
        Types::EMAIL => 'email',
        Types::URL => 'url',
    ];

    /**
     * @since 2.10.2
     * @since 2.14.0 add $formId as a param
     * @since 2.14.0 Add filter to allow rendering logic for custom fields
     * @since 2.16.0 Add visibility conditions to field container
     *
     * @param Node $field
     * @param int  $formId
     *
     * @return void
     */
    public static function render(Node $field, $formId)
    {
        $type = $field->getType();
        $fieldIdAttribute = give(UniqueIdAttributeGenerator::class)->getId($formId, $field->getName());

        if ($type === Types::HIDDEN) {
            include static::getTemplatePath('hidden');

            return;
        }

        $classList = apply_filters("give_form_{$formId}_field_classes_{$field->getName()}", [
            'form-row',
            'form-row-wide',
        ]);
        $className = implode(' ', array_unique($classList));

        printf(
            '<div class="%1$s" data-field-type="%2$s" data-field-name="%3$s" %4$s>',
            $className,
            $field->getType(),
            $field->getName(),
            self::getVisibilityConditionAttribute($field)
        );

        // By default, new fields will use templates/label.html.php and templates/base.html.php
        switch ($type) {
            case Types::HTML:
            case Types::CHECKBOX:
            case Types::RADIO: // Radio provides its own label
                include static::getTemplatePath($type);
                break;
            // These fields need a label and have their own template.
            case Types::FILE:
            case Types::SELECT:
            case Types::TEXTAREA:
                include static::getTemplatePath('label');
                include static::getTemplatePath($type);
                break;
            // By default, include a template and use the base input template.
            case Types::DATE:
            case Types::EMAIL:
            case Types::PHONE:
            case Types::TEXT:
            case Types::URL:
                // Used in the template
                $typeAttribute = array_key_exists(
                    $type,
                    static::INPUT_TYPE_ATTRIBUTES
                ) ? static::INPUT_TYPE_ATTRIBUTES[$type] : 'text';
                include static::getTemplatePath('label');
                include static::getTemplatePath('base');
                break;
            default:
                /**
                 * Provide a custom function to render for a custom node type.
                 *
                 * @since 2.14.0
                 *
                 * @param Node $field The node to render.
                 * @param int  $formId The form ID that the node is a part of.
                 *
                 * @void
                 */
                do_action("give_fields_api_render_{$field->getType()}", $field, $formId);
        }
        echo '</div>';
    }

    /**
     * @since 2.12.0
     *
     * @param string $templateName
     *
     * @return string
     */
    protected static function getTemplatePath($templateName)
    {
        return plugin_dir_path(__FILE__) . "/templates/{$templateName}.html.php";
    }

    /**
     * @param Node $field
     *
     * @return string
     */
    private static function getVisibilityConditionAttribute(Node $field)
    {
        $visibilityConditions = method_exists($field, 'getVisibilityConditions') ? $field->getVisibilityConditions(
        ) : null;

        if ($visibilityConditions) {
            $visibilityConditionsJson = esc_attr(json_encode($visibilityConditions));

            return "data-field-visibility-conditions=\"$visibilityConditionsJson\"";
        }

        return '';
    }
}
