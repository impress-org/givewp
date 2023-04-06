import React from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer} from '@givewp/components/AdminUI/ContainerLayout';
import BillingAddress from '../BillingAddress';
import DonorDetails from '../DonorDetails';
<<<<<<< Updated upstream
=======
import DonorComments from '../DonorComments';
import {DonorNotes} from '../DonorNotes';
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
=======
                <RightContainer>
                    <DonorComments />
                    <DonorNotes />
                </RightContainer>
>>>>>>> Stashed changes
            </Container>
        </>
    );
}
