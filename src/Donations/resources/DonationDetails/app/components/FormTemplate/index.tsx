import React, {Fragment} from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer, RightContainer} from '@givewp/components/AdminUI/ContainerLayout';
import BillingAddress from '../BillingAddress';
import DonorDetails from '../DonorDetails';
import DonorComments from '../DonorComments';
import {DonorNotes} from '../DonorNotes';

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
                    <DonorNotes />
                </RightContainer>
            </Container>
        </Fragment>
    );
}
