import styles from './src/styles';

import { withA11y } from '@storybook/addon-a11y';

export default {
	title: 'Setup Page/Badges',
	decorators: [ withA11y ],
};

const Styles = `
<style>
  ` + styles + `
</style>
`;

export const Complete = () => Styles + `
<section>
  <header>
    <h2>Complete</h2>
    <span class="badge badge-complete">Complete</span>
  </header>
</section>
`;

export const Review = () => Styles + `
<section>
  <header>
    <h2>Review</h2>
    <span class="badge badge-review">5-10 Minutes</span>
  </header>
</section>
`;
