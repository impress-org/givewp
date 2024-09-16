import {CampaignDetailsTab} from './types';
import {__} from '@wordpress/i18n';
import {getGiveCampaignDetailsWindowData} from './index';
import {useFormContext} from 'react-hook-form';
import {useEffect} from 'react';

const {campaign} = getGiveCampaignDetailsWindowData();

// TODO: We need to remove this component after creating the final settings component
const TestSettings = () => {
    const {register, watch, formState} = useFormContext();
    const formWatch = watch();

    useEffect(() => {
        console.log('formWatch: ', formWatch);
        console.log('formState.isDirty: ', formState.isDirty);
    }, [formWatch]);

    return (
        <>
            <p>Settings component goes here...</p>
            <p>
                <label>Title:</label>
                <input {...register('title')} />
            </p>
            <p>
                <a
                    style={{fontSize: '1.5rem'}}
                    href={campaign.settings.landingPageUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Edit Campaign Landing Page ⭷
                </a>
            </p>
        </>
    );
};

const campaignDetailsTabs: CampaignDetailsTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => (
            <>
                <p>Overview component goes here...</p>
                <ul>
                    {Object.entries(campaign.properties).map(([property, value], index) => (
                        <li key={index}>
                            <span>
                                <strong>{property}:</strong> {String(value)}
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        id: 'settings',
        title: __('Settings', 'give'),
        content: () => <TestSettings />,
    },
    {
        id: 'forms',
        title: __('Forms', 'give'),
        content: () => (
            <>
                <p>Forms list table goes here...</p>
            </>
        ),
    },
];

export default campaignDetailsTabs;
