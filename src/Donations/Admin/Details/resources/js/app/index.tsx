import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import {Interweave} from 'interweave';

const root = createRoot(document.getElementById('givewp-admin-donation-details-root'));

// @ts-ignore
const data = window.giveDonationDetails;
console.log(data);

root.render(
    <StrictMode>
        <Interweave content={data.legacyMeta} />
    </StrictMode>
);
