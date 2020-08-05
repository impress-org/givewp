import { getWindowData } from '../../utils';

const DonationForm = () => {
	const formPreviewUrl = getWindowData( 'formPreviewUrl' );
	const iframeStyle = {
		border: 'none',
		width: '580px',
		height: '800px',
	};
	return (
		<iframe src={ formPreviewUrl } style={ iframeStyle } />
	);
};

export default DonationForm;
