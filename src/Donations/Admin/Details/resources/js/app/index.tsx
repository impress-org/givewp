import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';
import {Interweave} from 'interweave';
import {Markup} from 'interweave';
import {Link} from 'react-aria-components';

const root = createRoot(document.getElementById('givewp-admin-donation-details-root'));

// @ts-ignore
const data = window.giveDonationDetails;
console.log(data);

root.render(
    <StrictMode>
        {/* <div dangerouslySetInnerHTML={{__html: data.legacyMeta}} /> */}
        <Interweave
            content={data.legacyMeta}
            allowAttributes
            transform={(node) => {
                if (node.tagName === 'SELECT') {
                    const selectedOption = node.querySelector('option[selected]') || node.querySelector('option');
                    if (selectedOption) {
                        return <span>{selectedOption.textContent}</span>;
                    }
                }

                if (node.tagName === 'INPUT') {
                    return <span>{node.getAttribute('value')}</span>;
                }

                return undefined;
            }}
        />
    </StrictMode>
);
