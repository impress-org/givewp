/**
 * @unreleased
 */
export type Donor = {
  id: number;
  userId?: number;
  createdAt: string;
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
 * @unreleased
 */
export type DonorStatus = 'current' | 'prospective' | 'retained' | 'lapsed' | 'new' | 'recaptured' | 'recurring';

/**
 * @unreleased
 */
export type DonorAddress = {
    address1: string;
    address2: string;
    city: string;
    state: string;
    country: string;
    zip: string;
};
