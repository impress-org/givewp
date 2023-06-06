import {ErrorBoundary} from 'react-error-boundary';
import DonationFormAppErrorFallback from './DonationFormAppErrorFallback';

export default function DonationFormErrorBoundary({children}) {
    return (
        <ErrorBoundary
            FallbackComponent={DonationFormAppErrorFallback}
            onReset={() => {
                window.location.reload();
            }}
        >
            {children}
        </ErrorBoundary>
    );
}