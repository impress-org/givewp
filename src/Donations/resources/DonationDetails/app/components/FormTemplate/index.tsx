import React, {Fragment} from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer, RightContainer} from '@givewp/components/AdminUI/ContainerLayout';
import BillingAddress from '../BillingAddress';
import DonorDetails from '../DonorDetails';
import DonorComments from '../DonorComments';

/**
 *
 * @unreleased
 */

export default function FormTemplate() {
    return (
        <Fragment>
            <PaymentInformation />
            <Container>
                <LeftContainer>
                    <DonorDetails />
                    <BillingAddress />
                </LeftContainer>
                <RightContainer>
                    <DonorComments />
                </RightContainer>
            </Container>
        </Fragment>
    );
}
