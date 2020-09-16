/* eslint-disable no-unused-vars */

import React from 'react';

const CampaignCardList = ( props ) => {
	const styles = {
		display: 'flex',
		flexWrap: 'wrap',
	};
	return (
		<div style={ styles }>
			{ props.children }
		</div>
	);
};

export default CampaignCardList;
