/**
 * @since 4.4.0
 */
export type Donor = {
  id: number;
  userId?: number;
  createdAt: {
    date: string;
    timezone: string;
    timezoneType: number;
  };
  name: string;
  prefix: string;
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  company: string;
  additionalEmails: string[];
  avatarId: number;
  avatarUrl: string;
  totalAmountDonated: number;
  totalNumberOfDonations: number;
  wpUserPermalink: string;
  status: DonorStatus;
};

/**
 * @since 4.4.0
 */
export type DonorStatus = 'current' | 'prospective' | 'retained' | 'lapsed' | 'new' | 'recaptured' | 'recurring';

/**
 * @since 4.4.0
 */
export type DonorAddress = {
    address1: string;
    address2: string;
    city: string;
    state: string;
    country: string;
    zip: string;
};
