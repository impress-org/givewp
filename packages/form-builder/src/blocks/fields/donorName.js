import {FormTokenField, PanelBody, PanelRow, ToggleControl} from '@wordpress/components';
import {InspectorControls} from "@wordpress/block-editor";
import {__} from "@wordpress/i18n";

const DonorName = ( props ) => {

    const {
        attributes: { showHonorific, honoriphics, requireLastName },
        setAttributes,
    } = props;

    const requiredText = ( text, isRequired = true ) => {
        if( isRequired ) {
            return text + '(' + __('required', 'give') + ')'
        }
        return text
    }

    return (
        <>
            <div style={{display: 'flex', gap: '15px'}}>
                { !! showHonorific && (<select style={{ width: '80px'}}>
                    <option value="mr">Mr.</option>
                    <option value="ms">Ms.</option>
                    <option value="mrs">Mrs.</option>
                </select>)}
                <input type="text" placeholder={requiredText(__('First Name', 'give'))} />
                <input type="text" placeholder={requiredText(__('Last Name', 'give'), requireLastName )} />
            </div>

            <InspectorControls>
                <PanelBody title={ __( 'Name Title Prefix', 'give' ) } initialOpen={true}>
                    <PanelRow>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '10px'}}>
                            <div>{/* Wrapper added to control spacing between control and help text. */}
                                <ToggleControl
                                    label={__('Show Name Title Prefix', 'give')}
                                    checked={showHonorific}
                                    onChange={() => setAttributes({ showHonorific: ! showHonorific })}
                                    help={ __('Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for the donor to choose from.', 'give')}
                                />
                            </div>
                            { !! showHonorific && (<FormTokenField
                                tokenizeOnSpace={true}
                                label={ __( 'Title Prefixes', 'give' )}
                                value={ honoriphics }
                                suggestions={ [ 'Mr', 'Ms', 'Mrs' ] }
                                placeholder={ __('Select some options', 'give')}
                                onChange={ ( tokens ) => setAttributes( { honoriphics: tokens } ) }
                                displayTransform={ ( token ) => token[0].toUpperCase() + token.slice(1) }
                                saveTransform={ ( token ) => token.trim().toLowerCase() }
                            />)}
                        </div>
                    </PanelRow>
                </PanelBody>
                <PanelBody title={ __( 'Last Name', 'give' ) } initialOpen={true}>
                    <PanelRow>
                        <ToggleControl
                            label={__('Require Last Name', 'give')}
                            checked={ requireLastName }
                            onChange={() => setAttributes({ requireLastName: ! requireLastName })}
                            help={ __('Do you want to force the Last Name field to be required?', 'give')}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    )

}

export default DonorName
