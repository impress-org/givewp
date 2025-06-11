import React, {useState} from 'react';
import {__} from '@wordpress/i18n';
import DonorTransactions from './DonorTransactions';
import DonorSummary from './DonorSummary';
import DonorStats from './DonorStats';
import DonorContributions from './DonorContributions';
import DonorPrivateNotes from './DonorPrivateNotes';
import AdminPanelWrapper from '@givewp/src/Admin/components/AdminPanelWrapper';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';

/**
 * @unreleased
 */
export default function DonorDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donorId = parseInt(urlParams.get('id') ?? '0');

    return (
        <AdminPanelWrapper
            above={<DonorStats donorId={donorId} />}
            leftColumn={[
                <DonorContributions donorId={donorId} />,
                <DonorTransactions donorId={donorId} />,
                <DonorPrivateNotes donorId={donorId} />,
            ]}
            rightColumn={[
                <DonorSummary donorId={donorId} />,
            ]}
            below={<NotificationPlaceholder type="snackbar" />}
        />
    );
}
