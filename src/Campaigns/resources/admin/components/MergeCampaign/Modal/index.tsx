import {useEffect, useState} from 'react';
import {__} from '@wordpress/i18n';
import MergeCampaignsForm from './../Form';

/**
 * Remove the "action" query parameter from the current URL
 *
 * @unreleased
 */
const removeActionParam = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const actionParam = queryParams.get('action');

    if (actionParam) {
        queryParams.delete('action');
        window.history.replaceState(null, '', `${window.location.pathname}?${queryParams.toString()}`);
    }
};

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @unreleased
 */
const autoOpenModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const actionParam = queryParams.get('action');

    if (actionParam && !window.history.state) {
        removeActionParam();
        return false;
    }

    return actionParam === 'merge';
};

/**
 * Create Campaign Modal component
 *
 * @unreleased
 */
export default function MergeCampaignModal() {
    const [isOpen, setOpen] = useState<boolean>(autoOpenModal());
    //const openModal = () => setOpen(true);
    const closeModal = (response: ResponseProps = {}) => {
        removeActionParam();
        setOpen(false);

        /*if (response?.id) {
            window.location.href =
                getGiveCampaignsListTableWindowData().adminUrl +
                'edit.php?post_type=give_forms&page=give-campaigns&id=' +
                response?.id;
        }*/
    };

    useEffect(() => {
        // Function to update URL parameters
        const handleQueryParamsChange = () => {
            //const searchParams = new URLSearchParams(window.location.search);
            //const params = Object.fromEntries(searchParams.entries());
            //console.log('Updated parameters:', params);

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

    return (
        <>
            <MergeCampaignsForm
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Merge campaigns', 'give')}
                campaigns={window.history.state}
            />
        </>
    );
}

type ResponseProps = {
    id?: string;
};
