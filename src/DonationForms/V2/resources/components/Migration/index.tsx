import {useCallback, useState} from 'react';
import Banner from './Banner';
import Onboarding from './Onboarding';

import {updateOnboardingOption} from '../DonationFormsListTable'

export default function MigrationOnboarding() {
    const [showOnboarding, setShowOnboarding] = useState<Boolean>(Boolean(window.GiveDonationForms.showMigrationOnboarding));

    const handleClose = useCallback(() => {
        updateOnboardingOption('show_migration_onboarding').then(() => {
            setShowOnboarding(false);
        })
    }, []);

    return showOnboarding ? <Onboarding handleClose={handleClose} /> : <Banner />
}
