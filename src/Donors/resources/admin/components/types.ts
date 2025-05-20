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
  additionalEmails: string[];
  totalAmountDonated: number;
  totalNumberOfDonations: number;
};
