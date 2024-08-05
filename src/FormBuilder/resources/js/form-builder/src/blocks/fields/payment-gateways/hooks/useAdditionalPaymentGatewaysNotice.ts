import {useState} from "react";

declare const window: {
    additionalPaymentGatewaysNotificationData: {
        actionUrl: string;
        isDismissed: boolean;
    };
} & Window;

/**
 * @unreleased
 */
const useAdditionalPaymentGatewaysNotice = () => {

    const {actionUrl, isDismissed} = window.additionalPaymentGatewaysNotificationData;
    const [showNotification, setShowNotification] = useState(!isDismissed);
    const onDismissNotification = () => fetch(actionUrl, {method: 'POST'}).then(() => setShowNotification(false))

    return [showNotification, onDismissNotification];
}

export default useAdditionalPaymentGatewaysNotice;
