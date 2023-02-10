import {DonationAmountProps} from "@givewp/forms/propTypes";

export default function DonationAmount({
                                           fields: {
                                               amount: Amount,
                                               donationType: DonationType,
                                               currency: Currency,
                                               period: Period,
                                               installments: Installments,
                                               frequency: Frequency
                                           }
                                       }: DonationAmountProps) {
    return (
        <>
            <Amount/>
            <DonationType/>
            <Currency/>
            <Period/>
            <Frequency/>
            <Installments/>
        </>
    );
}
