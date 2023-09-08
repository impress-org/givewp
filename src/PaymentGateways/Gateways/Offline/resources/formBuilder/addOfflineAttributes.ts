declare const window: {
    giveOfflineGatewaySettings: {
        defaultInstructions: string;
    };
} & Window;

export const offlineAttributes = {
    offlineEnabled: {
        type: 'boolean',
        default: true,
    },
    offlineUseGlobalInstructions: {
        type: 'boolean',
        default: true,
    },
    offlineDonationInstructions: {
        type: 'string',
        default: window.giveOfflineGatewaySettings.defaultInstructions,
    },
};

export default function addAttribute(settings, name) {
    if (name === 'givewp/payment-gateways') {
        settings.attributes = {
            ...settings.attributes,
            offlineAttributes,
        };
    }

    return settings;
}
