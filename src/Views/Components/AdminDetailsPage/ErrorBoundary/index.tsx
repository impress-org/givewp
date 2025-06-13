import {ErrorBoundary} from 'react-error-boundary';
import Fallback from './Fallback';

export default function ErrorBoundaryComponent({children}) {
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
