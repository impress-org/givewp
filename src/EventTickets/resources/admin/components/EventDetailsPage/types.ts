import {ListTableColumn} from '@givewp/components';

export interface GiveEventTicketsDetails {
    apiNonce: string;
    apiRoot: string;
    event: {
        id: number;
        title: string;
        description: string;
        startDateTime: {
            date: string;
            timezone_type: number;
            timezone: string;
        };
        endDateTime: {
            date: string;
            timezone_type: number;
            timezone: string;
        };
        createdAt: string;
        updatedAt: string;
    };
    ticketTypesTable: {id: string; columns: ListTableColumn[]};
    adminUrl: string;
    pluginUrl: string;
}

export type ApiSettingsProps = GiveEventTicketsDetails & {
    table: {
        id: string;
        columns: ListTableColumn[];
    };
};
