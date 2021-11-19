import {withA11y} from '@storybook/addon-a11y';

export default {
    title: 'Setup Page/Icons',
    decorators: [withA11y],
};

import paypal from '../../../../../../../assets/dist/images/setup-page/paypal@2x.min.png';
export const paypalIcon = () =>
    `
  <img class="icon" src="` +
    paypal +
    `" alt="PayPal">
`;

import stripe from '../../../../../../../assets/dist/images/setup-page/stripe@2x.min.png';
export const stripeIcon = () =>
    `
  <img class="icon" src="` +
    stripe +
    `" alt="Stripe">
`;

import addons from '../../../../../../../assets/dist/images/setup-page/addons@2x.min.png';
export const addonsIcon = () =>
    `
  <img class="icon" src="` +
    addons +
    `" alt="addons">
`;

import configuration from '../../../../../../../assets/dist/images/setup-page/configuration@2x.min.png';
export const configurationIcon = () =>
    `
  <img class="icon" src="` +
    configuration +
    `" alt="configuration">
`;

import givewp101 from '../../../../../../../assets/dist/images/setup-page/givewp101@2x.min.png';
export const givewp101Icon = () =>
    `
  <img class="icon" src="` +
    givewp101 +
    `" alt="GiveWP 101">
`;
