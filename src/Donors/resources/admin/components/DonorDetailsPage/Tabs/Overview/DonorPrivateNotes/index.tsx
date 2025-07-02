import React from 'react';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import PrivateNotes from '@givewp/src/Admin/components/PrivateNotes';

/**
 * @unreleased
 */
interface DonorPrivateNotesProps {
    donorId: number;
}

/**
 * @unreleased
 */
export default function DonorPrivateNotes({donorId}: DonorPrivateNotesProps) {
    return (
        <OverviewPanel>
            <PrivateNotes donorId={donorId} />
        </OverviewPanel>
    );
} 