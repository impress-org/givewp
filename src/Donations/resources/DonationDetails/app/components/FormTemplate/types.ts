/**
 *
 * @unreleased
 */


export type FormTemplateProps = {
    data: {
        id: number;
        form: {
            id: number;
            name: string;
        };
        purchaseKey: string;
        createdAt: string;
        updatedAt: string;
        status: string;
        type: string;
        mode: string;
        amount: string;
        feeAmountRecovered: string | null;
        gatewayId: string;
        gateway: string;
        donorId: number;
        firstName: string;
        lastName: string;
        email: string;
        subscriptionId: number;
        billingAddress: {
            country: string;
            address1: string;
            address2: string;
            city: string;
            state: string;
            zip: string;
        };
        anonymous: boolean;
        gatewayTransactionId: string;
        company: string | null;
    };
}


