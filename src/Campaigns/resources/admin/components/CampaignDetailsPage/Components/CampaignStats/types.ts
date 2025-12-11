/**
 * @since 4.0.0
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
 * @since 4.0.0
 */
export type CampaignOverViewStat = {
    amountRaised: number;
    donationCount: number;
    donorCount: number;
};

/**
 * @since 4.0.0
 */
export type PercentChangePillProps = {
    value: number;
    comparison: number;
}
