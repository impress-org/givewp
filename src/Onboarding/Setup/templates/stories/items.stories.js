import styles from './src/styles';
import paypal from './src/paypal.js';
import stripe from './src/stripe.js';
import configuration from './src/configuration.js';
import givewp101 from './src/givewp101.js';
import addons from './src/addons.js';

import { withA11y } from '@storybook/addon-a11y';

export default {
	title: 'Setup Page/Row Items',
	decorators: [ withA11y ],
};

const Styles = `
<style>
  ` + styles + `
</style>
`;

export const Paypal = () => Styles + `
<section>
  ` + paypal() + `
</section>
`;

export const Stripe = () => Styles + `
<section>
  ` + stripe() + `
</section>
`;

export const Configuration = () => Styles + `
<section>
  ` + configuration() + `
</section>
`;

export const GiveWP101 = () => Styles + `
<section>
  ` + givewp101() + `
</section>
`;

export const Addons = () => Styles + `
<section>
  ` + addons() + `
</section>
`;
