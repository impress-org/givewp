import {createRoot} from '@wordpress/element';
import SubscriptionDetailsPage from './components/SubscriptionDetailsPage';

const container = document.getElementById('give-admin-subscriptions-root');

if (container) {
    const root = createRoot(container);
    root.render(<SubscriptionDetailsPage />);
}
