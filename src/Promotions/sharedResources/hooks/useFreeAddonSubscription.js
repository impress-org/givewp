import {useState, useCallback} from 'react';

/**
 * Handles making a request to add a user to the Free Addon email subscription. It returns whether the request worked,
 * failed, and the callback to trigger it all.
 *
 * @param {String} reason "rejected" or "subscribed"
 * @returns {boolean} Whether the request was successful.
 */
async function markSubscriptionComplete(reason) {
    const response = await fetch(giveFreeAddonModal.apiRoot, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-WP-Nonce': giveFreeAddonModal.nonce,
        },
        body: JSON.stringify({reason}),
    });

    return response.ok;
}

export default function useFreeAddonSubscription() {
    const [userSubscribed, setUserSubscribed] = useState(false);
    const [hasSubmissionError, setHasSubmissionError] = useState(false);

    const handleSubscribe = useCallback(
        /**
         * @param {String} firstName
         * @param {String} email
         * @param {String} siteUrl
         * @param {String} siteName
         * @returns {Promise<void>}
         */
        async (firstName, email, siteUrl, siteName) => {
            try {
                const response = await fetch('https://connect.givewp.com/activecampaign/subscribe/free-add-on', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        email,
                        website_url: siteUrl,
                        website_name: siteName,
                    }),
                });

                if (response.ok) {
                    setUserSubscribed(true);
                    markSubscriptionComplete('subscribed');
                } else {
                    setHasSubmissionError(true);
                }
            } catch (error) {
                setHasSubmissionError(true);
            }
        },
        [setUserSubscribed, setHasSubmissionError]
    );

    return {
        userSubscribed,
        hasSubscriptionError: hasSubmissionError,
        subscribeUser: handleSubscribe,
        rejectOffer: () => markSubscriptionComplete('rejected'),
    };
}
