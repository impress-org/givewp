import {useCallback, useState} from 'react';
import Banner from './Banner';
import Onboarding from './Onboarding';

export default function MigrationOnboarding() {
    const [showOnboarding, setShowOnboarding] = useState<Boolean>(window.GiveDonationForms.showMigrationOnboarding);

    const handleClose = useCallback(() => {
        setShowOnboarding(false);
        // ajax request to set the option to false
    }, []);

    return showOnboarding ? <Onboarding handleClose={handleClose} /> : <Banner />
}
