import {FormTokenField, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {InspectorControls} from "@wordpress/block-editor";
import {__} from "@wordpress/i18n";

const DonorName = ( props ) => {

    const {
        attributes: { showHonorific, honoriphics, requireLastName },
        setAttributes,
    } = props;

    return (
        <>
            <div style={{display: 'flex', gap: '15px'}}>
                { !! showHonorific && (<select style={{ width: '80px'}}>
                    <option value="mr">Mr.</option>
                    <option value="ms">Ms.</option>
                    <option value="mrs">Mrs.</option>
                </select>)}
                <input type="text" placeholder={'First Name'} />
                <input type="text" placeholder={'Last Name'} />
            </div>

            <InspectorControls>
                <PanelBody title={ __( 'Title', 'give' ) } initialOpen={true}>
                    <PanelRow>
                        <div style={{ display: 'flex', flexDirection: 'column',}}>
                            <ToggleControl
                                label={__('Show Title', 'give')}
                                checked={showHonorific}
                                onChange={() => setAttributes({ showHonorific: ! showHonorific })}
                                help={'This is help text.'}
                            />
                            { !! showHonorific && (<FormTokenField
                                label={ __( 'Title', 'give' )}
                                value={ honoriphics || [ 'Mr', 'Ms', 'Mrs' ] }
                                suggestions={ [ 'Mr', 'Ms', 'Mrs' ] }
                                onChange={ ( tokens ) => setAttributes( { honoriphics: tokens } ) }
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
                            help={'This is help text.'}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    )

}

export default DonorName
