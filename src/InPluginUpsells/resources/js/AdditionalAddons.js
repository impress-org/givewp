import {Hero} from './Hero';

const {heading, description} = window.GiveAddons.additionalAddons;

export const AdditionalAddons = () => (
	<article>
        <Hero heading={heading} description={description} />
	</article>
);

