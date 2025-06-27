import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * @unreleased
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
 * @unreleased
 */
export default function BillingInformation({ name, email, address }: BillingInformationProps) {
  const { address1, address2, city, state, zip, country } = address;
  return (
    <address>
      <p>
        {name} (<a href={`mailto:${email}`}>{email}</a>)
        <br />
        {address1 && address1}
        <br />
        {address2 && address2 !== '' && address2 && <br />}
        {city && city}, {state && state} {zip && zip}
        <br/>
        {country && country}
      </p>
    </address>
  );
} 