import {ErrorBoundary} from 'react-error-boundary';
import Fallback from './Fallback';

export default function CampaignDetailsErrorBoundary({children}) {
    return (
        <ErrorBoundary
            FallbackComponent={Fallback}
            onReset={() => {
                window.location.reload();
            }}
        >
            {children}
        </ErrorBoundary>
    );
}
