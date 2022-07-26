import {registerBlockType} from "@wordpress/blocks";
import {InspectorControls} from "@wordpress/block-editor";
import {PanelBody, TextControl, Button} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {Icon} from "@wordpress/icons";

registerBlockType('custom-block-editor/donation-amount-levels', {

    title: __('Donation Amount and Levels', 'custom-block-editor'),

    supports: {
        multiple: false,
        html: false, // Removes support for an HTML mode.
    },

    icon: () => <Icon icon={
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M11.5789 4.44118H20M4 7.52941L7.36842 4M7.36842 4V19M11.5789 8.85294H18.3158M11.5789 13.2647H16.6316M11.5789 17.6765H14.9474"
                stroke="#000C00" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    } />,

    attributes: {
        levels: {
            type: 'array',
            default: [
                '10',
                '25',
                '50',
                '100',
                '250',
            ],
        },
    },

    edit: function (props) {

        return (
            <>
                <div>
                    <div>
                        <input style={{width: '100%', marginBottom: '20px'}} type="text" />
                    </div>
                    {props.attributes.levels.length > 0 && (
                        <div style={{
                            textAlign: 'center',
                            display: 'grid',
                            gap: '20px',
                            gridTemplateColumns: '1fr 1fr 1fr',
                        }}>
                            {
                                props.attributes.levels.map((level, index) => {
                                    return (
                                        <div key={'level-option-' + index}
                                             style={{padding: '15px', border: '1px solid black', borderRadius: '3px'}}>
                                            <sup>$</sup>{level}</div>
                                    );
                                })
                            }
                            <div style={{
                                padding: '20px',
                                border: '1px solid black',
                                borderRadius: '3px',
                            }}>{__('Custom Amount')}</div>
                        </div>
                    )}
                </div>

                <InspectorControls>
                    <PanelBody title={__('Donation Levels', 'give')} initialOpen={true}>
                        {props.attributes.levels.length > 0 && (
                            <ul style={{listStyleType: 'none', padding: 0}}>
                                {
                                    props.attributes.levels.map((label, index) => {
                                        return (
                                            <li key={'level-option-inspector-' + index} style={{
                                                display: 'flex',
                                                justifyContent: 'space-between',
                                                alignItems: 'center',
                                            }}>
                                                <TextControl
                                                    value={label}
                                                    onChange={(val) => {
                                                        const levels = [...props.attributes.levels];
                                                        levels[index] = val;
                                                        props.setAttributes({levels: levels});
                                                    }}
                                                />
                                                <button
                                                    style={{
                                                        background: 'transparent',
                                                        border: 'none',
                                                        cursor: 'pointer',
                                                        color: 'red',
                                                    }}
                                                    onClick={() => {
                                                        props.attributes.levels.splice(index, 1);
                                                        props.setAttributes({levels: props.attributes.levels.slice()});
                                                    }}
                                                >Delete
                                                </button>
                                            </li>
                                        );
                                    })
                                }
                            </ul>
                        )}
                        <Button onClick={() => {
                            const levels = [...props.attributes.levels];
                            levels.push('');
                            props.setAttributes({levels: levels});
                        }}>Add new level</Button>
                    </PanelBody>
                </InspectorControls>
            </>
        );
    },

    save: function () {
        return null; // Save as attributes - not rendered HTML.
    },
});
