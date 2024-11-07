import {useEffect, useState} from 'react';
import {__} from '@wordpress/i18n';
import {getGiveCampaignsListTableWindowData} from '../../CampaignsListTable';
import MergeCampaignForm from './../Form';

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @unreleased
 */
const autoOpenModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const newParam = queryParams.get('new');

    return newParam === 'merge';
};

/**
 * Create Campaign Modal component
 *
 * @unreleased
 */
export default function MergeCampaignModal() {
    const [isOpen, setOpen] = useState<boolean>(autoOpenModal());
    const openModal = () => setOpen(true);
    const closeModal = (response: ResponseProps = {}) => {
        setOpen(false);

        if (response?.id) {
            window.location.href =
                getGiveCampaignsListTableWindowData().adminUrl +
                'edit.php?post_type=give_forms&page=give-campaigns&id=' +
                response?.id;
        }
    };

    //const [queryParams, setQueryParams] = useState({});

    useEffect(() => {
        // Function to update URL parameters
        const handleQueryParamsChange = () => {
            const searchParams = new URLSearchParams(window.location.search);
            const params = Object.fromEntries(searchParams.entries());
            //setQueryParams(params);
            console.log('Updated parameters:', params);

            setOpen(autoOpenModal());
        };

        // Override pushState and replaceState to trigger a custom event
        const originalPushState = window.history.pushState;
        const originalReplaceState = window.history.replaceState;

        window.history.pushState = function (...args) {
            originalPushState.apply(window.history, args);
            window.dispatchEvent(new Event('urlChange'));
        };

        window.history.replaceState = function (...args) {
            originalReplaceState.apply(window.history, args);
            window.dispatchEvent(new Event('urlChange'));
        };

        // Add listeners for "popstate" and the custom "urlChange" event
        window.addEventListener('popstate', handleQueryParamsChange);
        window.addEventListener('urlChange', handleQueryParamsChange);

        // Call the function once to get the initial parameters
        //handleQueryParamsChange();

        // Remove listeners when the component unmounts
        return () => {
            window.removeEventListener('popstate', handleQueryParamsChange);
            window.removeEventListener('urlChange', handleQueryParamsChange);

            // Restore the original pushState and replaceState functions
            window.history.pushState = originalPushState;
            window.history.replaceState = originalReplaceState;
        };
    }, []);

    const apiSettings = getGiveCampaignsListTableWindowData();
    // Remove the /list-table from the apiRoot. This is a hack to make the API work while we don't refactor other list tables.
    apiSettings.apiRoot = apiSettings.apiRoot.replace('/list-table', '');

    return (
        <>
            <MergeCampaignForm
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Create your campaign', 'give')}
                apiSettings={apiSettings}
            />
        </>
    );
}

type ResponseProps = {
    id?: string;
};
