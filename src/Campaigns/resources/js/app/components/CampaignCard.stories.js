/* eslint-disable no-unused-vars */

import React from 'react';
import { CampaignCard, CoverImage, FooterItem } from './CampaignCard';
import CampaignCardList from './CampaignCardList';

export default {
	title: 'Components/CampaignCard',
};

export const Example = () => {
	return (
		<CampaignCard title="My Campaign" progress={ .5 }>
			<FooterItem title="$3,000" subtitle="raised" />
			<FooterItem title="50" subtitle="donations" />
			<FooterItem title="$10,000" subtitle="goal" />
		</CampaignCard>
	);
};

export const Editable = () => {
	return (
		<CampaignCard title="My Campaign" editable={ true }>
			<FooterItem title="$3,000" subtitle="raised" />
			<FooterItem title="50" subtitle="donations" />
			<FooterItem title="$10,000" subtitle="goal" />
		</CampaignCard>
	);
};

export const Empty = () => {
	return <CampaignCard title="My Campaign" />;
};

export const Footer = () => {
	return (
		<CampaignCard title="My Campaign" progress={ 3000 / 10000 }>
			<FooterItem title="$3,000" subtitle="raised" />
			<FooterItem title="50" subtitle="donations" />
			<FooterItem title="$10,000" subtitle="goal" />
		</CampaignCard>
	);
};

export const Content = () => {
	return (
		<CampaignCard title="My Campaign">
			<section style={ { fontSize: '24px', lineHeight: '1.2', padding: '40px 0' } }>
				<strong>10 New Donors</strong>
				<br /><span style={ { color: '#767676', fontSize: '14px' } }>this week</span>
			</section>
		</CampaignCard>
	);
};

export const Image = () => {
	return (
		<CampaignCard title="My Campaign">
			<CoverImage url="http://placehold.it/550x300" />
		</CampaignCard>
	);
};

export const List = () => {
	return (
		<CampaignCardList>
			<Example />
			<Example />
			<Example />
		</CampaignCardList>
	);
};
