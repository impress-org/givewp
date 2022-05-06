/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n'
import { select } from '@wordpress/data';
import {InspectorControls, store } from '@wordpress/block-editor';
import {
    ColorPalette,
    FormTokenField,
    PanelBody,
    SelectControl,
    TextControl,
    ToggleControl
} from '@wordpress/components';

import ColumnSelector from '../../components/column-selector';

/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';

/**
 * Render Inspector Controls
 */

const Inspector = ({attributes, setAttributes}) => {
    const {
        formsPerPage,
        paged,
        imageSize,
        imageHeight,
        formIDs,
        excludeForms,
        excludedFormIDs,
        orderBy,
        order,
        categories,
        tags,
        columns,
        showTitle,
        showExcerpt,
        excerptLength,
        showGoal,
        showProgressBar,
        showFeaturedImage,
        showDonateButton,
        donateButtonBackgroundColor,
        donateButtonTextColor,
        displayType
    } = attributes;

    const saveSetting = (name, value) => {
        setAttributes({
            [name]: value,
        });
    };

    const getAsArray = value => {
        if (Array.isArray(value)) {
            return value;
        }

        // Backward compatibility
        if (formIDs.indexOf(',')) {
            return value.split(',');
        }

        return [value];
    };

    const getImageSizes = () => select(store).getSettings().imageSizes.map(({slug, name}) => {
        return {
            value: slug,
            label: name
        };
    });

    return (
        <InspectorControls key="inspector">
            <PanelBody title={__('Display Appearance', 'give')}>
                <ColumnSelector
                    label={__('Columns', 'give')}
                    selected={columns}
                    onClick={(value) => saveSetting('columns', value)}
                    help={__('Controls how many columns of the Form Grid appear. "Best Fit" will adjust responsively to the space available.', 'give')}
                />

                <ToggleControl
                    name="showTitle"
                    label={__('Show Title', 'give')}
                    checked={!!showTitle}
                    onChange={(value) => saveSetting('showTitle', value)}/>

                <ToggleControl
                    name="showExcerpt"
                    label={__('Show Excerpt', 'give')}
                    checked={!!showExcerpt}
                    onChange={(value) => saveSetting('showExcerpt', value)}
                />

                <TextControl
                    name="excerptLength"
                    label={__('Excerpt Length', 'give')}
                    value={excerptLength || ''}
                    className={!showExcerpt && 'hidden'}
                    onChange={(value) => saveSetting('excerptLength', Number.parseInt(value) || 0)}
                />

                <ToggleControl
                    name="showGoal"
                    label={__('Show Goal', 'give')}
                    checked={!!showGoal}
                    onChange={(value) => saveSetting('showGoal', value)}
                />

                {showGoal && (
                    <ToggleControl
                        name="showProgressBar"
                        label={__('Show Progress Bar', 'give')}
                        checked={!!showProgressBar}
                        onChange={(value) => saveSetting('showProgressBar', value)}
                    />
                )}

                <ToggleControl
                    align="right"
                    name="showFeaturedImage"
                    label={__('Show Featured Image', 'give')}
                    checked={!!showFeaturedImage}
                    onChange={(value) => saveSetting('showFeaturedImage', value)}
                />

                {showFeaturedImage && (
                    <>
                        <SelectControl
                            name="imageSize"
                            label={__('Image Size', 'give')}
                            value={imageSize}
                            options={getImageSizes()}
                            onChange={(value) => saveSetting('imageSize', value)}
                            help={__('Featured image size. Default "medium." Accepts WordPress image sizes, which by default are "thumbnail," "medium," "large," or "full." ', 'give')}
                        />

                        <TextControl
                            name="imageHeight"
                            label={__('Image Height', 'give')}
                            value={imageHeight}
                            onChange={(value) => saveSetting('imageHeight', value)}
                            help={__('Featured image height. Default "auto". Accepts valid heights in px, em, or rem.', 'give')}
                        />
                    </>
                )}

                <ToggleControl
                    name="showDonateButton"
                    label={__('Show Donate Button', 'give')}
                    checked={!!showDonateButton}
                    onChange={(value) => saveSetting('showDonateButton', value)}
                />
                {showDonateButton && (
                    <>
                        <p>
                            {__('Donate Button Background Color', 'give')}
                        </p>
                        <ColorPalette
                            clearable={false}
                            onChange={(value) => saveSetting('donateButtonBackgroundColor', value)}
                            value={donateButtonBackgroundColor}
                        />
                        <p>
                            {__('Donate Button Text Color', 'give')}
                        </p>
                        <ColorPalette
                            clearable={false}
                            onChange={(value) => saveSetting('donateButtonTextColor', value)}
                            value={donateButtonTextColor}
                        />
                    </>
                )}
            </PanelBody>

            <PanelBody title={__('Filters and Categories', 'give')}>

                <SelectControl
                    label={__('Order By', 'give')}
                    name="orderBy"
                    value={orderBy}
                    options={giveFormOptions.orderBy}
                    onChange={(value) => saveSetting('orderBy', value)}
                    help={__('The order forms are displayed in.', 'give')}
                />

                <SelectControl
                    label={__('Order', 'give')}
                    name="order"
                    value={order}
                    options={giveFormOptions.order}
                    onChange={(value) => saveSetting('order', value)}
                    help={__('Whether the order ascends or descends.', 'give')}
                />

                <FormTokenField
                    name="categories"
                    label={__('Categories', 'give')}
                    value={getAsArray(categories)}
                    onChange={(value) => saveSetting('categories', value)}
                />

                <p className="components-form-token-field__help">
                    {__('Type the name of your category to add it to the list. Only forms within the categories you choose will be displayed in this grid.', 'give')}
                </p>

                <FormTokenField
                    name="tags"
                    label={__('Tags', 'give')}
                    value={getAsArray(tags)}
                    onChange={(value) => saveSetting('tags', value)}
                />

                <p className="components-form-token-field__help">
                    {__('Type the name of your tag to add it to the list. Only forms with these tags you choose will be displayed in this grid.', 'give')}
                </p>

                <FormTokenField
                    name="formIDs"
                    label={__('Form IDs', 'give')}
                    value={getAsArray(formIDs)}
                    onChange={(value) => saveSetting('formIDs', value)}
                />

                <p className="components-form-token-field__help">
                    {__('Type the ID of your form to add it to the list. Only forms with these IDs you choose will be displayed in this grid.', 'give')}
                </p>

                <ToggleControl
                    name="excludeForms"
                    label={__('Exclude specific forms?', 'give')}
                    checked={!!excludeForms}
                    onChange={(value) => saveSetting('excludeForms', value)}
                />

                {excludeForms && (
                    <>
                        <FormTokenField
                            name="excludedFormIDs"
                            label={__('Excluded Form IDs', 'give')}
                            value={getAsArray(excludedFormIDs)}
                            onChange={(value) => saveSetting('excludedFormIDs', value)}
                        />
                        <p className="components-form-token-field__help">
                            {__('Type the ID of your form to exclude it from the list. Forms with these IDs you choose will not be displayed in this grid.', 'give')}
                        </p>
                    </>
                )}
            </PanelBody>

            <PanelBody title={__('Grid Behavior', 'give')}>
                <SelectControl
                    label={__('Display Type', 'give')}
                    name="displayType"
                    value={displayType}
                    options={giveFormOptions.displayType}
                    onChange={(value) => saveSetting('displayType', value)}
                    help={__('What should happen when a visitor clicks on a form within the grid? "Redirect" sends them to the individual form. "Modal" opens the form in a lightbox/popup on the same page.', 'give')}
                />
                <TextControl
                    name="formsPerPage"
                    label={__('Forms Per Page', 'give')}
                    value={formsPerPage}
                    onChange={(value) => saveSetting('formsPerPage', value)}
                    help={__('How many forms should display on the first page load? To restrict display to only one page, disable pagination below.', 'give')}
                />
                <ToggleControl
                    name="paged"
                    label={__('Show Pagination', 'give')}
                    checked={!!paged}
                    onChange={(value) => saveSetting('paged', value)}
                />
            </PanelBody>
        </InspectorControls>
    );
};

export default Inspector;
