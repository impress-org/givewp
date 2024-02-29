export interface GiveEventTickets {
    apiNonce: string;
    apiRoot: string;
    event: {
        title: string;
        description: string;
        startDateTime: string;
        endDateTime: string;
        createdAt: string;
        updatedAt: string;
    };
    adminUrl: string;
    pluginUrl: string;
}
