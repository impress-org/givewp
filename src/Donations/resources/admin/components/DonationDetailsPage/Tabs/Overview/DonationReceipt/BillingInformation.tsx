import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * @since 4.6.0
 */
type BillingInformationProps = {
  name: string;
  email: string;
  address: {
    address1?: string;
    address2?: string;
    city?: string;
    state?: string;
    zip?: string;
    country?: string;
    [key: string]: any;
  };
};

/**
 * @since 4.6.0
 */
export default function BillingInformation({ name, email, address }: BillingInformationProps) {
  const { address1, address2, city, state, zip, country } = address;
  return (
    <address>
      <p>
        {name} ({email})
        {address1 && address1.length > 0 && <><br />{address1}</>}
        {address2 && address2.length > 0 && <><br />{address2}</>}
        {city && city.length > 0 && <><br />{city},</>} {state && state} {zip && zip}
        {country && <><br />{country}</>}
      </p>
    </address>
  );
}
