import React, { Fragment } from 'react';
import { __ } from '@wordpress/i18n';

/**
 * @unreleased
 */
type BillingInformationProps = {
  name: string;
  email: string;
  address: string[];
};

/**
 * @unreleased
 */
export default function BillingInformation({ name, email, address }: BillingInformationProps) {
  return (
      <address>
        <p>
          {name} (<a href={`mailto:${email}`}>{email}</a>)<br />
          {address.map((line, index) => (
            <Fragment key={index}>
              {line}
              {index < address.length - 1 && <br />}
            </Fragment>
          ))}
        </p>
      </address>
    );
} 