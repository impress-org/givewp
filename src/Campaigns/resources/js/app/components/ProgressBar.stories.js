/* eslint-disable no-unused-vars */

import React from 'react';
import ProgressBar from './ProgressBar';

export default {
	title: 'Components/ProgressBar',
	parameters: {
		backgrounds: {
			default: 'white',
		},
	},
};

export const ProgressBarExample = () => {
	return <ProgressBar percent="25" />;
};

