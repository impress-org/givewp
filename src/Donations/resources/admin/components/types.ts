
type PaymentGateway = {
    id: string;
    name: string;
    label: string;
    transactionUrl: string;
}

/**
 * @since 4.6.0
 */
export type Donation = {
  id: number;
  campaignId: number;
  formId: number;
  formTitle: string;
  createdAt: {
    date: string;
    timezone: string;
    timezone_type: number;
  };
  updatedAt: {
    date: string;
    timezone: string;
    timezone_type: number;
  };
  status: string;
  mode: DonationMode;
  type: DonationType;
  amount: {
    value: number;
    currency: string;
  };
  feeAmountRecovered: {
    value: number;
    currency: string;
  };
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
  eventTicketsAmount: {
    value: number;
    currency: string;
  };
  eventTickets: EventTicket[];
  gateway: PaymentGateway;
  customFields: CustomField[];
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
