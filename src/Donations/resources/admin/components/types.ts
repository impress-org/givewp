

/**
 * @unreleased
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
};



/**
 * @unreleased
 */
export type DonationMode = 'test' | 'live';

/**
 * @unreleased
 */
export type DonationType = 'single' | 'renewal';

/**
 * @unreleased
 */
export type DonationAddress = {
    address1: string;
    address2: string;
    city: string;
    state: string;
    country: string;
    zip: string;
};
