import {ErrorBoundary} from 'react-error-boundary';
import {__} from '@wordpress/i18n';

const FallbackComponent = ({error, resetErrorBoundary}) => {
    return (
        <div role="alert">
            <p>
                {__(
                    'An error occurred in the form.  Please notify the site administrator.  The error message is:',
                    'give'
                )}
            </p>
            <pre style={{padding: '0.5rem'}}>{error.message}</pre>
            <button type="button" onClick={resetErrorBoundary}>
                {__('Reload form', 'give')}
            </button>
        </div>
    );
};

export default function FormBuilderErrorBoundary({children}) {
    return (
        <ErrorBoundary
            FallbackComponent={FallbackComponent}
            onReset={() => {
                window.location.reload();
            }}
        >
            {children}
        </ErrorBoundary>
    );
}