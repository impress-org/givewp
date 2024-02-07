import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function Layout({dispatch, publishSettings, formDesigns, designId}) {
    const designOptions = Object.values(formDesigns).map(({id, name}) => ({value: id, label: name}));
    const {
        settings: {designSettingsImageUrl, designSettingsImageStyle},
    } = useFormState();

    return (
        <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
            <PanelRow>
                <SelectControl
                    label={__('Form layout', 'give')}
                    value={designId}
                    onChange={(designId: string) => {
                        dispatch(
                            setFormSettings({
                                designId,
                                designSettingsImageUrl,
                                designSettingsImageStyle,
                            })
                        );
                        publishSettings({
                            designSettingsImageUrl,
                            designSettingsImageStyle,
                        });
                    }}
                    options={designOptions}
                    help={__(
                        'Change the appearance of your donation form on your site. Each option has a different layout.',
                        'give'
                    )}
                />
            </PanelRow>
        </PanelBody>
    );
}
