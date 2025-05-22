export type Donor = {
    id?: number;
    name: string;
    firstName: string;
    lastName: string;
    // todo: complete the list of properties
}

export type DonorsListTableParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
    sort?: 'id' | 'createdAt' | 'name' | 'firstName' | 'lastName' | 'totalAmountDonated' | 'totalNumberOfDonations';
    direction?: 'ASC' | 'DESC';
    onlyWithDonations?: boolean;
    campaignId?: number;
    includeSensitiveData?: boolean;
    anonymousDonors?: 'include' | 'exclude' | 'redact';
}
