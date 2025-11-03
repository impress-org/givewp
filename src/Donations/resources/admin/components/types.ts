import { Campaign } from '@givewp/campaigns/admin/components/types';
import { Donor } from '@givewp/donors/admin/components/types';

type PaymentGateway = {
    id: string;
    name: string;
    label: string;
    transactionUrl: string;
}

export type Money = {
    value: number;
    valueInMinorUnits: number;
    currency: string;
}

/**
 * @since 4.13.0 added _embedded property and updated money types
 * @since 4.6.0
 */
export type Donation = {
    id: number;
    campaignId: number;
    formId: number;
    formTitle: string;
    createdAt: string;
    updatedAt: string;
    status: string;
    mode: DonationMode;
    type: DonationType;
    amount: Money;
    feeAmountRecovered: Money;
    exchangeRate: string;
    gatewayId: string;
    donorId: number;
    honorific: string;
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    subscriptionId?: number;
    billingAddress: DonationAddress;
    purchaseKey: string;
    donorIp: string;
    anonymous: boolean;
    levelId: string;
    gatewayTransactionId: string;
    company: string;
    comment: string;
    eventTicketsAmount: Money;
    eventTickets: EventTicket[];
    gateway: PaymentGateway;
    customFields: CustomField[];
    _embedded?: {
        'givewp:campaign': Campaign[];
        'givewp:donor': Donor[];
        'givewp:form': {
            id: number;
            title: string;
        }[];
    };
};

export type CustomField = {
    label: string;
    value: string;
};

export type Event = {
    id: number;
    title: string;
    description: string;
    startDateTime: string;
    endDateTime: string;
    ticketCloseDateTime: string;
    createdAt: string;
    updatedAt: string;
};

export type EventTicketType = {
    id: number;
    eventId: number;
    title: string;
    description: string;
    price: {
        value: number;
        currency: string;
    };
    capacity: number;
};
/**
 * @since 4.6.0
 */
export type EventTicket = {
    id: number;
    event: Event;
    ticketType: EventTicketType;
    amount: {
        value: number;
        currency: string;
    };
    createdAt: string;
    updatedAt: string;
};

/**
 * @since 4.6.0
 */
export type DonationMode = 'test' | 'live';

/**
 * @since 4.6.0
 */
export type DonationType = 'single' | 'renewal';



/**
 * @since 4.6.0
 */
export type DonationAddress = {
    address1: string;
    address2: string;
    city: string;
    state: string;
    country: string;
    zip: string;
};
