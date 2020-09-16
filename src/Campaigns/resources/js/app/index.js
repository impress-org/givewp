/* eslint-disable no-unused-vars */

// Vendor dependencies
import React from 'react';
import ReactDOM from 'react-dom';

import { CampaignCard, FooterItem } from './components/CampaignCard';
import CampaignCardList from './components/CampaignCardList';

const App = ( props ) => {
	const campaigns = props.data.campaings.map( ( campaign ) => {
		const meta = Object.entries( campaign.meta ).map( ( [ subtitle, title ] ) => {
			return <FooterItem title={ title } subtitle={ subtitle } key={ title } />;
		} );

		return (
			<CampaignCard title={ campaign.title } progress={ campaign.progress } key={ campaign.title }>
				{ meta }
			</CampaignCard>
		);
	} );

	return (
		<div>
			<CampaignCardList>
				{ campaigns }
			</CampaignCardList>
		</div>
	);
};

// Render application
ReactDOM.render(
	<App data={ window.giveCampaigns } />,
	document.getElementById( 'root' )
);
