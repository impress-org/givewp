import {PanelBody} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {InspectorControls} from "@wordpress/block-editor";
import DeleteButton from "./delete-button";
import AddButton from "./add-button";
import {CurrencyControl} from "../../../../common/currency";

const Inspector = ({attributes, setAttributes}) => {

    const {levels} = attributes;

    return (
        <InspectorControls>
            <PanelBody title={__('Donation Levels', 'give')} initialOpen={true}>
                {levels.length > 0 && (
                    <ul style={{
                        listStyleType: 'none',
                        padding: 0,
                        display: 'flex',
                        flexDirection: 'column',
                        gap: '16px',
                    }}>
                        {
                            levels.map((amount, index) => {
                                return (
                                    <li key={'level-option-inspector-' + index} style={{
                                        display: 'flex',
                                        gap: '16px',
                                        justifyContent: 'space-between',
                                        alignItems: 'center',
                                    }}>
                                        <CurrencyControl
                                            value={amount}
                                            onValueChange={(value) => {
                                                const newLevels = [...levels];

                                                newLevels[index] = value;
                                                setAttributes({levels: newLevels});
                                            }}
                                        />
                                        <DeleteButton onClick={() => {
                                            levels.splice(index, 1);
                                            setAttributes({levels: levels.slice()});
                                        }} />
                                    </li>
                                );
                            })
                        }
                    </ul>
                )}
                <AddButton onClick={() => {
                    const newLevels = [...levels];
                    newLevels.push('');
                    setAttributes({levels: newLevels});
                }} />
            </PanelBody>
        </InspectorControls>
    );
};

export default Inspector;
