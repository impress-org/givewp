import React from 'react';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import { DonorNotes } from '@givewp/src/Admin/components/PrivateNotes';

/**
 * @since 4.5.0
 */
interface DonorPrivateNotesProps {
    donorId: number;
}

/**
 * @since 4.5.0
 */
export default function DonorPrivateNotes({donorId}: DonorPrivateNotesProps) {
    return (
        <OverviewPanel>
            <DonorNotes donorId={donorId} />
        </OverviewPanel>
    );
}
