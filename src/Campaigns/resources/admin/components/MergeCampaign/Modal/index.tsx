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
        window.history.replaceState(
            'merge-campaigns-modal-closed',
            '',
            `${window.location.pathname}?${queryParams.toString()}`
        );
    }
};

/**
 * Auto open modal if the URL has the query parameter action as "merge"
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
    const closeModal = () => {
        removeActionParam();
        setOpen(false);
    };

    const campaigns = {
        selected: window.history.state?.selected || [],
        names: window.history.state?.names || [],
    };

    useEffect(() => {
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
        const handleQueryParamsChange = () => {
            /**
             * This timeout prevents this error from being thrown in the browser console:
             * Warning: Cannot update a component (`MergeCampaignModal`) while rendering a different component (`ListTablePage`).
             *
             * @see https://github.com/facebook/react/issues/18178#issuecomment-595846312
             */
            const initializeModalState = () => {
                setOpen(autoOpenModal());
            };
            setTimeout(initializeModalState, 0);
        };
        window.addEventListener('popstate', handleQueryParamsChange);
        window.addEventListener('urlChange', handleQueryParamsChange);

        // Remove listeners when the component unmounts
        return () => {
            window.removeEventListener('popstate', handleQueryParamsChange);
            window.removeEventListener('urlChange', handleQueryParamsChange);

            // Restore the original pushState and replaceState functions
            window.history.pushState = originalPushState;
            window.history.replaceState = originalReplaceState;
        };
    }, [campaigns]);

    return (
        <>
            <MergeCampaignsForm
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Merge campaigns', 'give')}
                campaigns={campaigns}
            />
        </>
    );
}
