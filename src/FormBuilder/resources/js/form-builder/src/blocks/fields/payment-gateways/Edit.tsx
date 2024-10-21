import {ReactNode} from 'react';
import {BlockEditProps} from '@wordpress/blocks';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {applyFilters} from '@wordpress/hooks';
import {InspectorControls} from "@wordpress/block-editor";
import {__} from "@wordpress/i18n";
import {Icon} from '@wordpress/components';
import {external} from "@wordpress/icons";

const GatewayItem = ({label, icon}: {label: string; icon: ReactNode}) => {
    return (
        <div
            style={{
                backgroundColor: 'var(--givewp-gray-20)',
                padding: '16px',
                display: 'flex',
                justifyContent: 'space-between',
            }}
        >
            {label} {icon}
        </div>
    );
};

export default function Edit(props: BlockEditProps<any>) {
    const {gateways} = getFormBuilderWindowData();

    return (
        <div
            style={{
                fontSize: '16px',
                padding: '24px',
                textAlign: 'center',
                border: '1px dashed var(--givewp-gray-100)',
                borderRadius: '5px',
                backgroundColor: 'var(--givewp-gray-10)',
            }}
        >
            <div style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                {gateways
                    .filter((gateway) =>
                        applyFilters(
                            `givewp_form_builder_payment_gateway_enabled_${gateway.id}`,
                            gateway.enabled,
                            gateway
                        )
                    )
                    .map((gateway) => (
                        <GatewayItem
                            key={gateway.id}
                            label={gateway.label}
                            icon={
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M9.21293 14.6663H6.78626C6.63422 14.6664 6.48673 14.6144 6.36827 14.5191C6.2498 14.4238 6.16747 14.2909 6.13493 14.1423L5.8636 12.8863C5.50164 12.7277 5.15842 12.5294 4.84026 12.295L3.6156 12.685C3.47064 12.7312 3.31423 12.7265 3.17234 12.6715C3.03046 12.6166 2.91163 12.5148 2.8356 12.383L1.6196 10.2823C1.54436 10.1504 1.51612 9.99689 1.53949 9.84684C1.56287 9.69679 1.63647 9.55912 1.74826 9.45634L2.69826 8.58967C2.65506 8.19708 2.65506 7.80093 2.69826 7.40834L1.74826 6.54367C1.63631 6.44086 1.56261 6.30306 1.53923 6.15286C1.51585 6.00267 1.54419 5.84899 1.6196 5.71701L2.83293 3.61501C2.90896 3.48322 3.02779 3.3814 3.16968 3.32647C3.31156 3.27153 3.46797 3.26678 3.61293 3.31301L4.8376 3.70301C5.00026 3.58301 5.1696 3.47101 5.34426 3.36967C5.51293 3.27501 5.68626 3.18901 5.8636 3.11234L6.1356 1.85767C6.16798 1.70914 6.25015 1.57613 6.36849 1.48071C6.48684 1.38528 6.63424 1.33317 6.78626 1.33301H9.21293C9.36495 1.33317 9.51236 1.38528 9.6307 1.48071C9.74904 1.57613 9.83122 1.70914 9.8636 1.85767L10.1383 3.11301C10.4998 3.27252 10.8429 3.47079 11.1616 3.70434L12.3869 3.31434C12.5318 3.26829 12.6881 3.27312 12.8298 3.32805C12.9715 3.38298 13.0903 3.48469 13.1663 3.61634L14.3796 5.71834C14.5343 5.98967 14.4809 6.33301 14.2509 6.54434L13.3009 7.41101C13.3441 7.8036 13.3441 8.19975 13.3009 8.59234L14.2509 9.45901C14.4809 9.67101 14.5343 10.0137 14.3796 10.285L13.1663 12.387C13.0902 12.5188 12.9714 12.6206 12.8295 12.6755C12.6876 12.7305 12.5312 12.7352 12.3863 12.689L11.1616 12.299C10.8437 12.5332 10.5007 12.7313 10.1389 12.8897L9.8636 14.1423C9.83108 14.2908 9.74885 14.4236 9.63052 14.5189C9.51219 14.6142 9.36486 14.6662 9.21293 14.6663ZM7.99693 5.33301C7.28969 5.33301 6.61141 5.61396 6.11131 6.11406C5.61122 6.61415 5.33026 7.29243 5.33026 7.99967C5.33026 8.70692 5.61122 9.38519 6.11131 9.88529C6.61141 10.3854 7.28969 10.6663 7.99693 10.6663C8.70418 10.6663 9.38245 10.3854 9.88255 9.88529C10.3826 9.38519 10.6636 8.70692 10.6636 7.99967C10.6636 7.29243 10.3826 6.61415 9.88255 6.11406C9.38245 5.61396 8.70418 5.33301 7.99693 5.33301Z"
                                        fill="#1E1E1E"
                                    />
                                </svg>
                            }
                        />
                    ))}
            </div>
            <InspectorControls>
                <div style={{
                    marginTop: '-8px', // Adjust spacing between block card and link.
                    borderBottom: '1px solid #e0e0e0', // Emulate the border between block card and inspector controls.
                    padding: '0 0 var(--givewp-spacing-4) var(--givewp-spacing-13)' // Align with block card padding.
                }}>
                    <a
                        href={'/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=gateways-settings&group=v3'}
                        target="_blank">
                        <Icon style={{marginRight: '4px'}} icon={external} className='givewp-inspector-notice__externalIcon' />
                        {__('Enable more payment gateways', 'give')}
                    </a>
                </div>
            </InspectorControls>
        </div>
    );
}
