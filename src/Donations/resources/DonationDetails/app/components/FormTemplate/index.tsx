import React from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer} from '@givewp/components/AdminUI/ContainerLayout';
import BillingAddress from '../BillingAddress';
import DonorDetails from '../DonorDetails';

/**
 *
 * @unreleased
 */

export default function FormTemplate() {
    return (
        <>
            <PaymentInformation />
            <Container>
                <LeftContainer>
                    <DonorDetails />
                    <BillingAddress />
                </LeftContainer>
            </Container>
        </>
    );
}
