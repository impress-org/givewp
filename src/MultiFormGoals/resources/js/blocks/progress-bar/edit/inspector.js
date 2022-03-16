/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
const {InspectorControls} = wp.blockEditor;
const {PanelBody, TextControl} = wp.components;

/**
 * Internal dependencies
 */

import MultiSelectControl from '../../../components/multi-select-control';
import ColorControl from '../../../components/color-control';
import DateTimeControl from '../../../components/date-time-control';
import {useFormOptions, useTagOptions, useCategoryOptions} from '../data/utils';

/* eslint-disable-next-line no-undef */
const editorColorPalette = giveProgressBarThemeSupport.editorColorPalette;

/**
 * Render Inspector Controls
 */

const Inspector = ({attributes, setAttributes}) => {
    const {ids, categories, tags, goal, enddate, color} = attributes;
    const formOptions = useFormOptions();
    const tagOptions = useTagOptions();
    const categoryOptions = useCategoryOptions();
    const saveSetting = (name, value) => {
        setAttributes({
            [name]: value,
        });
    };
    return (
        <InspectorControls key="inspector">
            <PanelBody title={__('Goal', 'give')} initialOpen={true}>
                <TextControl
                    name="goal"
                    label={__('Goal amount', 'give')}
                    type="number"
                    onChange={(value) => saveSetting('goal', value)}
                    value={goal}
                />
                <DateTimeControl
                    name="enddate"
                    label={__('Goal end date', 'give')}
                    value={enddate}
                    onChange={(value) => saveSetting('enddate', value)}
                />
                <ColorControl
                    colors={editorColorPalette}
                    name="color"
                    label={__('Progress bar color', 'give')}
                    onChange={(value) => saveSetting('color', value)}
                    value={color}
                />
            </PanelBody>
            <PanelBody title={__('Filters', 'give')} initialOpen={false}>
                <MultiSelectControl
                    name="ids"
                    label={__('Filter by forms', 'give')}
                    value={formOptions.filter((option) => ids.includes(option.value))}
                    placeholder={`${__('All forms', 'give')}...`}
                    options={formOptions}
                    onChange={(value) => saveSetting('ids', value ? value.map((option) => option.value) : [])}
                />
                <MultiSelectControl
                    name="categories"
                    label={__('Filter by categories', 'give')}
                    value={categoryOptions.filter((option) => categories.includes(option.value))}
                    placeholder={`${__('All categories', 'give')}...`}
                    options={categoryOptions}
                    onChange={(value) => saveSetting('categories', value ? value.map((option) => option.value) : [])}
                />
                <MultiSelectControl
                    name="tags"
                    label={__('Filter by tags', 'give')}
                    value={tagOptions.filter((option) => tags.includes(option.value))}
                    placeholder={`${__('All tags', 'give')}...`}
                    options={tagOptions}
                    onChange={(value) => saveSetting('tags', value ? value.map((option) => option.value) : [])}
                />
            </PanelBody>
        </InspectorControls>
    );
};

export default Inspector;
