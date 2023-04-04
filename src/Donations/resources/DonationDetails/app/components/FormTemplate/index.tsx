import React from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer, RightContainer} from '@givewp/components/AdminUI/ContainerLayout';
import BillingAddress from '../BillingAddress';

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
                    <BillingAddress />
                </LeftContainer>
            </Container>
        </>
    );
}
