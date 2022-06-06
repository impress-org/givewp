/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n'
import { select } from '@wordpress/data';
import {InspectorControls, PanelColorSettings, store } from '@wordpress/block-editor';
import {
    ColorPalette,
    FormTokenField, Panel,
    PanelBody,
    SelectControl,
    TextControl,
    ToggleControl
} from '@wordpress/components';


/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';

import ColumnSelector from '../../components/column-selector';

import './style.scss'

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
        tagBackgroundColor,
        donateButtonTextColor,
        displayType,
        filter,
        tagTextColor
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

  const filterValue = () => {
      if (filter === 'categories') {
        return  <>
            <FormTokenField
                name="categories"
                value={getAsArray(categories)}
                onChange={(value) => saveSetting('categories', value)}
            />

            <p className="components-form-token-field__help">
                {__('Type the name of your category to add it to the list. Only forms within the categories you choose will be displayed in this grid.', 'give')}
            </p>
        </>
      } else if (filter === 'tags') {
          return <>
              <FormTokenField
                  name="tags"
                  value={getAsArray(tags)}
                  onChange={(value) => saveSetting('tags', value)}
              />

              <p className="components-form-token-field__help">
                  {__('Type the name of your tag to add it to the list. Only forms with these tags you choose will be displayed in this grid.', 'give')}
              </p></>
      } else if (filter === 'formIDs'){
          return <>
              <FormTokenField
                  name="formIDs"
                  value={getAsArray(formIDs)}
                  onChange={(value) => saveSetting('formIDs', value)}
              />

              <p className="components-form-token-field__help">
                  {__('Type the ID of your form to add it to the list. Only forms with these IDs you choose will be displayed in this grid.', 'give')}
              </p></>
      }
  }


    return (
        <InspectorControls key="inspector">
            <Panel>
                <PanelBody title= {__('Layout', 'give')} initialOpen={ true }>
                    <ColumnSelector
                        label={__('Columns', 'give')}
                        selected={columns}
                        onClick={(value) => saveSetting('columns', value)}
                        help={__('Controls how many columns of the Form Grid appear. "Best Fit" will adjust responsively to the space available.', 'give')}
                    />
                    <SelectControl
                        label={ __( 'Columns', 'give' ) }
                        name="columns"
                        value={ columns }
                        options={ giveFormOptions.columns }
                        onChange={ ( value ) => saveSetting( 'columns', value ) } />
                </PanelBody>
            </Panel>
            <Panel>
                <PanelBody title= {__('Display Elements', 'give')} initialOpen={ true }>
                    <ToggleControl
                        className="give-form-grid-inspector"
                        name="showTitle"
                        label={__('Show Title', 'give')}
                        checked={!!showTitle}
                        onChange={(value) => saveSetting('showTitle', value)}/>
                    <ToggleControl
                        className="give-form-grid-inspector"
                        name="showGoal"
                        label={__('Show Goal', 'give')}
                        checked={!!showGoal}
                        onChange={(value) => saveSetting('showGoal', value)}
                    />
                    <ToggleControl
                        className="give-form-grid-inspector"
                        name="showExcerpt"
                        label={__('Show Excerpt', 'give')}
                        checked={!!showExcerpt}
                        onChange={(value) => saveSetting('showExcerpt', value)}
                    />
                    <ToggleControl
                        className="give-form-grid-inspector"
                        name="showDonateButton"
                        label={__('Show Donate Button', 'give')}
                        checked={!!showDonateButton}
                        onChange={(value) => saveSetting('showDonateButton', value)}
                    />
                    <ToggleControl
                        className="give-form-grid-inspector"
                        align="right"
                        name="showFeaturedImage"
                        label={__('Show Featured Image', 'give')}
                        checked={!!showFeaturedImage}
                        onChange={(value) => saveSetting('showFeaturedImage', value)}
                    />
                </PanelBody>
            </Panel>
            <Panel>
                <PanelBody title= {__('Grid Settings', 'give')} initialOpen={ true }>
                    <SelectControl
                        className="give-form-grid-inspector"
                        label={__('Order By', 'give')}
                        name="orderBy"
                        value={orderBy}
                        options={giveFormOptions.orderBy}
                        onChange={(value) => saveSetting('orderBy', value)}
                        help={__('The order forms are displayed in.', 'give')}
                    />

                    <SelectControl
                        className="give-form-grid-inspector"
                        label={__('Order', 'give')}
                        name="order"
                        value={order}
                        options={giveFormOptions.order}
                        onChange={(value) => saveSetting('order', value)}
                        help={__('Whether the order ascends or descends.', 'give')}
                    />
                    <SelectControl
                        className="give-form-grid-inspector"
                        label={ __( 'Filter', 'give' ) }
                        name="filter" value={filter}
                        options={ giveFormOptions.filter }
                        onChange={ ( value ) => saveSetting( 'filter', value ) } />

                    {filterValue(filter)}

                    <ToggleControl
                        className="give-form-grid-inspector"
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
            </Panel>
            <Panel>
                <PanelBody title= {__('Grid Interaction', 'give')} initialOpen={ true }>
                    <SelectControl
                        className="give-form-grid-inspector"
                        label={__('Display Type', 'give')}
                        name="displayType"
                        value={displayType}
                        options={giveFormOptions.displayType}
                        onChange={(value) => saveSetting('displayType', value)}
                        help={__('What should happen when a visitor clicks on a form within the grid? "Redirect" sends them to the individual form. "Modal" opens the form in a lightbox/popup on the same page.', 'give')}
                    />
                    <TextControl
                        className="give-form-grid-inspector"
                        name="formsPerPage"
                        label={__('Forms Per Page', 'give')}
                        value={formsPerPage}
                        onChange={(value) => saveSetting('formsPerPage', value)}
                        help={__('How many forms should display on the first page load? To restrict display to only one page, disable pagination below.', 'give')}
                    />
                </PanelBody>
            </Panel>
            <PanelColorSettings
                title={ __( 'Color Settings' ) }
                colorSettings={ [
                    {
                        value: tagBackgroundColor
,                       onChange: (value) => saveSetting('tagBackgroundColor', value),
                        label: __('Tag Background Color', 'give')

                    },
                    {
                        value: donateButtonTextColor,
                        onChange: (value) => saveSetting('donateButtonTextColor', value),
                        label: __('Donate Button Text Color', 'give')
                    },
                    {
                        value: tagTextColor,
                        onChange: (value) => saveSetting('tagTextColor', value),
                        label: __('Tag Text Color', 'give')
                    },
                ] }
            />

        </InspectorControls>
    );
};

export default Inspector;
