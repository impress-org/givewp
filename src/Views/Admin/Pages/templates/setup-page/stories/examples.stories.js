import styles from './src/styles';
import paypal from './src/paypal.js';
import stripe from './src/stripe.js';
import configuration from './src/configuration.js';
import givewp101 from './src/givewp101.js';
import addons from './src/addons.js';

import { withA11y } from '@storybook/addon-a11y';

export default {
	title: 'Setup Page/Examples',
	decorators: [ withA11y ],
};

const Styles = `
<style>
  ` + styles + `
</style>
`;

export const Configuration = () => Styles + `
<section>
  <header>
    <h2>Create your first donation form in minutes</h2>
    <span class="badge badge-complete">Complete</span>
  </header>
  <main>
    ` + configuration() + `
  </main>
</section>
`;

export const Gateways = () => Styles + `
<section>
  <header>
    <h2>Connect a payment gateway to begin accepting donations</h2>
  </header>
  <main>
    ` + paypal() + stripe() + `
  </main>
  <footer>
    Want to use a different gateway? GiveWP has support for many others including Authorize.net, Square, Razorpay and more!
    <a href="#">View all gateways</a>
  </footer>
</section>
`;

export const Resources = () => Styles + `
<section>
  <header>
    <h2>Level up your fundraising with these great resources</h2>
  </header>
  <main>
    ` + givewp101() + addons() + `
  </main>
</section>
`;
