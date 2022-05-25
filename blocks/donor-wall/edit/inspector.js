/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Panel, SelectControl, ToggleControl, TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import giveDonorWallOptions from '../data/options';

import ColumnSelector from '../../components/column-selector';
import ToggleOptions from '../../components/toggle';


/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { donorsPerPage, ids, formID, categories, tags, orderBy, order, columns, avatarSize, showAvatar, showName, showCompanyName, showForm, showTotal, showDate, showComments, showAnonymous, onlyComments, commentLength, readMoreText, loadMoreText, toggleOptions } = attributes;

    const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};

    console.log(toggleOptions);

    return (
		<InspectorControls key="inspector">
                <Panel>
                    <PanelBody title="Layout" initialOpen={ true }>
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
                            options={ giveDonorWallOptions.columns }
                            onChange={ ( value ) => saveSetting( 'columns', value ) } />
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title="Display Elements" initialOpen={ true }>
                        <ToggleOptions
                            options={[__( 'Donor info', 'give' ), __( 'Wall attributes', 'give' )  ]}
                            onClick={( value ) => saveSetting( 'toggleOptions', value ) }
                            selected={toggleOptions}/>
                                {toggleOptions === 'Donor info' ?
                                    <>
                                        <ToggleControl
                                            name="showName"
                                            label={ __( 'Show Name', 'give' ) }
                                            checked={ !! showName }
                                            onChange={ ( value ) => saveSetting( 'showName', value ) } />
                                        <ToggleControl
                                            name="showName"
                                            label={ __( 'Show Company Name', 'give' ) }
                                            checked={ !! showCompanyName }
                                            onChange={ ( value ) => saveSetting( 'showCompanyName', value ) } />
                                        <ToggleControl
                                            name="showAnonymous"
                                            label={ __( 'Show Anonymous', 'give' ) }
                                            checked={ !! showAnonymous }
                                            onChange={ ( value ) => saveSetting( 'showAnonymous', value ) } />
                                        <ToggleControl
                                            name="showAvatar"
                                            label={ __( 'Show Avatar', 'give' ) }
                                            checked={ !! showAvatar }
                                            onChange={ ( value ) => saveSetting( 'showAvatar', value ) } />
                                        <TextControl
                                            name="avatarSize"
                                            label={ __( 'Avatar Size (px)', 'give' ) }
                                            value={ avatarSize }
                                            onChange={ ( value ) => saveSetting( 'avatarSize', value ) } />
                                    </> :
                                    <>
                                        <ToggleControl
                                            name="showForm"
                                            label={ __( 'Show Donation Form', 'give' ) }
                                            checked={ !! showForm }
                                            onChange={ ( value ) => saveSetting( 'showForm', value ) } />
                                        <ToggleControl
                                            name="showTotal"
                                            label={ __( 'Show Total', 'give' ) }
                                            checked={ !! showTotal }
                                            onChange={ ( value ) => saveSetting( 'showTotal', value ) } />
                                        <ToggleControl
                                            name="showComments"
                                            label={ __( 'Show Comments', 'give' ) }
                                            checked={ !! showComments }
                                            onChange={ ( value ) => saveSetting( 'showComments', value ) } />
                                        <TextControl
                                            name="commentLength"
                                            label={ __( 'Comment Length', 'give' ) }
                                            value={ commentLength }
                                            onChange={ ( value ) => saveSetting( 'commentLength', value ) } />
                                        <TextControl
                                            name="readMoreText"
                                            label={ __( 'Read More Text', 'give' ) }
                                            value={ readMoreText }
                                            onChange={ ( value ) => saveSetting( 'readMoreText', value ) } />
                                    </>
                                }
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title="Wall settings" initialOpen={ true }>
                        <SelectControl
                            label={ __( 'Order By', 'give' ) }
                            name="orderBy"
                            value={ orderBy }
                            options={ giveDonorWallOptions.orderBy }
                            onChange={ ( value ) => saveSetting( 'orderBy', value ) } />
                        <SelectControl
                            label={ __( 'Order', 'give' ) }
                            name="order"
                            value={ order }
                            options={ giveDonorWallOptions.order }
                            onChange={ ( value ) => saveSetting( 'order', value ) } />
                        <SelectControl
                            label={ __( 'Filter', 'give' ) }
                            name="filter"
                            value={ order }
                            options={ giveDonorWallOptions.filter }
                            onChange={ ( value ) => saveSetting( 'order', value ) } />
                    </PanelBody>
                </Panel>
                <Panel>
                    <PanelBody title="Wall interaction" initialOpen={ true }>
                        <TextControl
                            name="donorsPerPage"
                            label={ __( 'Donors Per Page', 'give' ) }
                            value={ donorsPerPage }
                            onChange={ ( value ) => saveSetting( 'donorsPerPage', value ) }
                            help={ __('How many donors should show up on the initial page load?', 'give' ) }
                        />
                        <TextControl
                            name="loadMoreText"
                            label={ __( 'Load More Text', 'give' ) }
                            value={ loadMoreText }
                            onChange={ ( value ) => saveSetting( 'loadMoreText', value ) } />
                    </PanelBody>
                </Panel>


				<TextControl
					name="ids"
					label={ __( 'Donor IDs', 'give' ) }
					value={ ids }
					onChange={ ( value ) => saveSetting( 'ids', value ) }
                    help={ __('By default, all donors will display. Use this setting to restrict the donor wall to only display certain donors. Use a comma-separated list of donor IDs.', 'give') }
                />
				<TextControl
					name="formID"
					label={ __( 'Form IDs', 'give' ) }
					value={ formID }
					onChange={ ( value ) => saveSetting( 'formID', value ) }
                    help={ __('By Default, donations to all forms will display. Use this setting to restrict the donor to display only donations to certains forms. Use a comma-separated list of form IDs.', 'give') }
                />
                <TextControl
                    name="categories"
                    label={ __( 'Categories', 'give' ) }
                    value={ categories }
                    onChange={ ( value ) => saveSetting( 'categories', value ) }/>
                <TextControl
                    name="tags"
                    label={ __( 'Tags', 'give' ) }
                    value={ tags }
                    onChange={ ( value ) => saveSetting( 'tags', value ) }/>

				<ToggleControl
					name="onlyComments"
					label={ __( 'Only Donors with Comments', 'give' ) }
					checked={ !! onlyComments }
					onChange={ ( value ) => saveSetting( 'onlyComments', value ) } />


		</InspectorControls>
	);
};

export default Inspector;
