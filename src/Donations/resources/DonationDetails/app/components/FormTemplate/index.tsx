import React from 'react';

import PaymentInformation from '../PaymentInformation';
import {Container, LeftContainer, RightContainer} from '@givewp/components/AdminUI/ContainerLayout';
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
                </LeftContainer>

                <RightContainer>
                    <DonorDetails />
                </RightContainer>
            </Container>
        </>
    );
}
