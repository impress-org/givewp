import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelColorSettings} from '@wordpress/block-editor';
import {PanelBody} from '@wordpress/components';

export default function Color({dispatch}) {
    const {
        settings: {primaryColor, secondaryColor},
    } = useFormState();

    const {publishColors} = useDonationFormPubSub();

    const defaultColors = [
        {name: 'Black', slug: 'black', color: '#000000'},
        {name: 'Dark Blue', slug: 'dark-blue', color: '#1E1AE2'},
        {name: 'Give Primary Default', slug: 'give-primary-default', color: '#69b86b'},
        {name: 'Red', slug: 'red', color: '#BD3D36'},
        {name: 'Orange', slug: 'orange', color: '#EB712E'},
        {name: 'Gray', slug: 'gray', color: '#5F7385'},
        {name: 'Light Blue', slug: 'light-blue', color: '#4492DD'},
        {name: 'Light Green', slug: 'light-green', color: '#63CC8A'},
        {name: 'Purple', slug: 'purple', color: '#9058D8'},
        {name: 'Teal', slug: 'teal', color: '#2BBAB1'},
        {name: 'Yellow', slug: 'yellow', color: '#F1BB40'},
    ];

    return (
        <PanelBody title={__('Color', 'give')}>
            <PanelColorSettings
                colorSettings={[
                    {
                        value: primaryColor,
                        onChange: (primaryColor: string) => {
                            dispatch(setFormSettings({primaryColor}));
                            publishColors({primaryColor});
                        },
                        label: __('Primary Color', 'give'),
                        disableCustomColors: false,
                        colors: defaultColors,
                    },
                    {
                        value: secondaryColor,
                        onChange: (secondaryColor: string) => {
                            dispatch(setFormSettings({secondaryColor}));
                            publishColors({secondaryColor});
                        },
                        label: __('Secondary Color', 'give'),
                        disableCustomColors: false,
                        colors: defaultColors,
                    },
                ]}
            />
        </PanelBody>
    );
}
