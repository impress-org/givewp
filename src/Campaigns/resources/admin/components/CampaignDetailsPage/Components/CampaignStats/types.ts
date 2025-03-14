/**
 * @unreleased
 */
export type StatWidgetProps = {
    label: string;
    value: number;
    previousValue?: number;
    description: string;
    formatter?: Intl.NumberFormat;
    loading?: boolean;
};

/**
 * @unreleased
 */
export type CampaignOverViewStat = {
    amountRaised: number;
    donationCount: number;
    donorCount: number;
};

/**
 * @unreleased
 */
export type PercentChangePillProps = {
    value: number;
    comparison: number;
}
