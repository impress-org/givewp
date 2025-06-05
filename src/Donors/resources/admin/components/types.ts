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

export type DonorStatus = 'current' | 'prospective' | 'retained' | 'lapsed' | 'new' | 'recaptured' | 'recurring';
