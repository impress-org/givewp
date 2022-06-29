/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

import {InspectorControls, ColorPalette,} from '@wordpress/block-editor';
import {PanelBody, Panel, SelectControl, ToggleControl, TextControl, FormTokenField } from '@wordpress/components';

/**
 * Internal dependencies
 */
import giveDonorWallOptions from '../data/options';

import ColumnSelector from '../../components/column-selector';
import ToggleOptions from '../../components/toggle';

import './style.scss'
import Toggle from "../../components/toggle";

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {

const { donorsPerPage,
        ids,
        formID,
        categories,
        tags, orderBy,
        order,
        columns,
        avatarSize,
        showAvatar,
        showName,
        showCompanyName,
        onlyComments,
        showForm,
        showTotal,
        showComments,
        showAnonymous,
        commentLength, readMoreText,
        loadMoreText,
        toggleOptions,
        filterOptions,
        color,
        showTimestamp,
        showTributes  } = attributes;

	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
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

    const filterValue = () => {
        if(filterOptions === 'categories'){
            return <> <FormTokenField
                className="give-donor-wall-inspector"
                onChange ={(value) => saveSetting('categories', value)}
                value={getAsArray(categories)}/>
                <p className="components-form-token-field__help">
                    {__('Type the name of your category to add it to the list. Only forms within the categories you choose will be displayed in this grid.', 'give')}
                </p>
            </>

        } else if (filterOptions === 'tags'){
            return <> <FormTokenField
                className="give-donor-wall-inspector"
                name="tags"
                value={getAsArray(tags)}
                onChange ={(value) => saveSetting('tags', value)}/>
                <p className="components-form-token-field__help">
                    {__('Type the name of your tag to add it to the list. Only forms with these tags you choose will be displayed in this grid.', 'give')}
                </p>
            </>

        } else if (filterOptions === 'ids'){
            return  <> <FormTokenField
                className="give-donor-wall-inspector"
                name="ids"
                value={getAsArray(ids)}
                onChange ={(value) => saveSetting('ids', value)}/>
                <p className="components-form-token-field__help">
                    {__('By default, all donors will display. Use this setting to restrict the donor wall to only display certain donors. Use a comma-separated list of donor IDs.', 'give')}
                </p>
            </>

        } else if (filterOptions === 'formID' ){
            return <> <FormTokenField
                className="give-donor-wall-inspector"
                help={__('By Default, donations to all forms will display. Use this setting to restrict the donor to display only donations to certains forms. Use a comma-separated list of form IDs.', 'give')}
                name="formID"
                value={getAsArray(formID)}
                onChange ={(value) => saveSetting('formID', value)}/>
                <p className="components-form-token-field__help">
                    {__('Type the ID of your form to add it to the list. Only forms with these IDs you choose will be displayed in this grid.', 'give')}
                </p>
            </>
        }
    };
    return (
		<InspectorControls key="inspector">
                <Panel>
                    <PanelBody title= {__('Layout', 'give')} initialOpen={ true }>
                        <ColumnSelector
                            selected={columns}
                            onClick={(value) => saveSetting('columns', value)}
                            help={__('Controls how many columns of the Form Grid appear. All sizes will adjust responsively to the space available. The maximum number allowed per row is 4', 'give')}
                        />
                        <SelectControl
                            className="give-donor-wall-inspector"
                            name="columns"
                            label={ __( 'Columns', 'give' ) }
                            value={ columns }
                            options={ giveDonorWallOptions.columns }
                            onChange={ ( value ) => saveSetting( 'columns', value ) } />
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title= {__('Display Elements', 'give')} initialOpen={ false }>
                        <ToggleOptions
                            options={giveDonorWallOptions.toggleOptions}
                            onClick={( value ) => saveSetting( 'toggleOptions', value ) }
                            selected={toggleOptions}/>
                                {toggleOptions === 'donorInfo' ?
                                    <>
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showName"
                                            label={ __( 'Show Name', 'give' ) }
                                            checked={ !! showName }
                                            onChange={ ( value ) => saveSetting( 'showName', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showName"
                                            label={ __( 'Show Company Name', 'give' ) }
                                            checked={ !! showCompanyName }
                                            onChange={ ( value ) => saveSetting( 'showCompanyName', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showAnonymous"
                                            label={ __( 'Show Anonymous', 'give' ) }
                                            checked={ !! showAnonymous }
                                            onChange={ ( value ) => saveSetting( 'showAnonymous', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showAvatar"
                                            label={ __( 'Show Avatar', 'give' ) }
                                            checked={ !! showAvatar }
                                            onChange={ ( value ) => saveSetting( 'showAvatar', value ) } />
                                        <TextControl
                                            className="give-donor-wall-inspector"
                                            name="avatarSize"
                                            label={ __( 'Avatar Size', 'give' ) }
                                            help={__('Avatar size. Default height is 75. Accepts valid heights in px.', 'give')}
                                            value={ avatarSize }
                                            onChange={ ( value ) => saveSetting( 'avatarSize', value ) } />
                                    </> :
                                    <>
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showForm"
                                            label={ __( 'Show Donation Form', 'give' ) }
                                            checked={ !! showForm }
                                            onChange={ ( value ) => saveSetting( 'showForm', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showTotal"
                                            label={ __( 'Show Total', 'give' ) }
                                            checked={ !! showTotal }
                                            onChange={ ( value ) => saveSetting( 'showTotal', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showTimestamp"
                                            label={ __( 'Show Time', 'give' ) }
                                            checked={ !! showTimestamp }
                                            onChange={ ( value ) => saveSetting( 'showTimestamp', value ) } />
                                        { !!window.Give_Tribute && <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showTributes"
                                            label={ __( 'Show Tributes', 'give' ) }
                                            checked={ !! showTributes }
                                            onChange={ ( value ) => saveSetting( 'showTributes', value ) } />}
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="showComments"
                                            label={ __( 'Show Comments', 'give' ) }
                                            checked={ !! showComments }
                                            onChange={ ( value ) => saveSetting( 'showComments', value ) } />
                                        <ToggleControl
                                            className="give-donor-wall-inspector"
                                            name="onlyComments"
                                            label={ __( 'Only Comments', 'give' ) }
                                            checked={ !! onlyComments }
                                            onChange={ ( value ) => saveSetting( 'onlyComments', value ) } />
                                        <TextControl
                                            className="give-donor-wall-inspector"
                                            name="commentLength"
                                            label={ __( 'Comment Length', 'give' ) }
                                            help={__('Limits the amount of characters to be displayed on donations with comments.', 'give')}
                                            value={ commentLength }
                                            onChange={ ( value ) => saveSetting( 'commentLength', value ) } />
                                        <TextControl
                                            className="give-donor-wall-inspector"
                                            name="readMoreText"
                                            label={ __( 'Read More Text', 'give' ) }
                                            value={ readMoreText }
                                            onChange={ ( value ) => saveSetting( 'readMoreText', value ) } />
                                    </>
                                }
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title= {__('Wall Settings', 'give')} initialOpen={ false }>
                        <SelectControl
                            className="give-donor-wall-inspector"
                            label={ __( 'Sort By', 'give' ) }
                            name="orderBy"
                            value={ orderBy }
                            options={ giveDonorWallOptions.orderBy }
                            onChange={ ( value ) => saveSetting( 'orderBy', value ) } />
                        <SelectControl
                            className="give-donor-wall-inspector"
                            label={ __( 'Order', 'give' ) }
                            name="order"
                            value={ order }
                            options={ giveDonorWallOptions.order }
                            onChange={ ( value ) => saveSetting( 'order', value ) } />
                        <SelectControl
                            className="give-donor-wall-inspector"
                            label={ __( 'Filter', 'give' ) }
                            name="filter"
                            value={ filterOptions }
                            options={ giveDonorWallOptions.filter }
                            onChange={ ( value ) => saveSetting( 'filterOptions', value ) } />

                        {filterValue(filterOptions)}
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title= {__('Wall Interaction', 'give')} initialOpen={ true }>
                        <TextControl
                            className="give-donor-wall-inspector"
                            name="donorsPerPage"
                            label={ __( 'Donors Per Page', 'give' ) }
                            value={ donorsPerPage }
                            onChange={ ( value ) => saveSetting( 'donorsPerPage', value ) }
                            help={ __('How many donors should show up on the initial page load?', 'give' ) }
                        />
                        <TextControl
                            className="give-donor-wall-inspector"
                            name="loadMoreText"
                            label={ __( 'Load More Text', 'give' ) }
                            value={ loadMoreText }
                            onChange={ ( value ) => saveSetting( 'loadMoreText', value ) } />
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title= {__('Color', 'give')} initialOpen={ false }>
                        <ColorPalette
                            value={color}
                            onChange={( value ) => setAttributes( { color: value } )}
                        />
                    </PanelBody>
                </Panel>
	</InspectorControls>
	);
};

export default Inspector;
