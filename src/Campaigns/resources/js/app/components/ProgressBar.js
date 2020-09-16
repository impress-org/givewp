/* eslint-disable no-unused-vars */

import React from 'react';

const ProgressBar = ( props ) => {
	const styles = {
		wrapper: {
			padding: '5px',
		},
		container: {
			height: '20px',
			overflow: 'hidden',
			borderRadius: '14px',
			backgroundColor: '#F1F1F1',
			boxShadow: 'inset 0px 1px 4px rgba(0, 0, 0, 0.09487)',
		},
		progress: {
			width: props.percent + '%',
			height: 'inherit',
			borderRadius: 'inherit',
			background: 'linear-gradient(180deg, #2bc253 0%, #2bc253 100%), linear-gradient(180deg, #fff 0%, #ccc 100%)',
			backgroundBlendMode: 'multiply',
		},
	};

	return (
		<div style={ styles.wrapper }>
			<div style={ styles.container }>
				<div style={ styles.progress }></div>
			</div>
		</div>
	);
};

export default ProgressBar;
