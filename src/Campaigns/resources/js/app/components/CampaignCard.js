import React from 'react';
import ProgressBar from './ProgressBar';
import { Icon } from '@wordpress/components';

const CampaignCard = ( props ) => {
	const styles = {
		container: {
			boxSizing: 'border-box',
			width: '552px',
			minWidth: '320px',
			textAlign: 'center',
			fontFamily: 'Montserrat',
		},
		wrapper: {
			position: 'relative',
			margin: '10px',
			backgroundColor: '#F9F9F9',
			border: '1px solid #CCD0D4',
			borderRadius: '8px',
			boxShadow: '0 3px 6px rgba(68, 68, 68, 0.05), 0 3px 6px rgba(68, 68, 68, 0.05)',
		},
		header: {
			padding: '20px',
			borderRadius: '8px',
			backgroundColor: 'white',
			borderBottom: '1px solid #F2F2F2',
		},
		title: {
			margin: 0,
			fontWeight: 700,
			fontSize: '20px',
			whiteSpace: 'nowrap',
		},
		innerContent: {
			padding: '20px',
		},
	};
	const children = React.Children.map( props.children || [], child => child );
	const content = children.filter( ( child ) => {
		return child.type !== FooterItem;
	} );
	const footerItems = children.filter( ( child ) => {
		return child.type === FooterItem;
	} );
	return (
		<div style={ styles.container }>
			<div style={ styles.wrapper }>
				{ !! props.editable && <Icon icon="edit" style={ { opacity: '.5', position: 'absolute', top: 10, right: 10 } } /> }
				<header style={ styles.header }>
					<h2 style={ styles.title }>
						{ props.title }
					</h2>
				</header>
				{ content }
				<section style={ { padding: '10px' } }>
					<ProgressBar percent={ props.progress * 100 } />
				</section>
				{ !! footerItems && <CardFooter children={ footerItems } /> }
			</div>
		</div>
	);
};

const CardFooter = ( props ) => {
	const styles = {
		footer: {
			fontSize: '14px',
			color: '#767676',
			fontWeight: 600,
			borderTop: '1px solid #F2F2F2',
			backgroundColor: '#FBFBFB',
			borderRadius: '0 0 8px 8px',
		},
		footerStrong: {
			fontSize: '18px',
			color: '#4C4C4C',
		},
		footerLayout: {
			display: 'flex',
		},
		footerLayoutItem: {
			flex: 1,
			padding: '20px',
			borderRadius: '0 0 8px 8px',
			border: '1px solid #F2F2F2',
		},
	};

	return (
		<footer style={ styles.footer }>
			<div style={ styles.footerLayout }>
				{ props.children }
			</div>
		</footer>
	);
};

const FooterItem = ( props ) => {
	const styles = {
		container: {
			flex: 1,
			padding: '20px',
			borderRadius: '0 0 8px 8px',
			border: '1px solid #F2F2F2',
		},
		strong: {
			fontSize: '18px',
			color: '#4C4C4C',
		},
	};
	return (
		<div style={ styles.container }>
			<strong style={ styles.strong }>{ props.title }</strong>
			<br />{ props.subtitle }
		</div>
	);
};

const CoverImage = ( props ) => {
	const style = {
		height: 300,
		backgroundImage: 'url(\'' + props.url + '\')',
		backgroundSize: 'cover',
	};
	return (
		<section style={ style }></section>
	);
};

export {
	CampaignCard,
	CoverImage,
	FooterItem,
};
