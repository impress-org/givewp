import { createRef, useEffect, useLayoutEffect, useState, Fragment } from 'react';
import PropTypes from 'prop-types';

//Import ChartJS dependencies
import ChartJS from 'chart.js';
import { createConfig, getTrend, getHighlightValue, getTooltipText, numberToDigits } from './utils';

import Tooltip from '../tooltip';

import './style.scss';

const MiniChart = ( { title, data } ) => {
	//SVG up/positive icon
	const up = <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fillRule="evenodd" clipRule="evenodd" d="M7 13C10.3137 13 13 10.3137 13 7C13 3.68629 10.3137 1 7 1C3.68629 1 1 3.68629 1 7C1 10.3137 3.68629 13 7 13Z" fill="#69B868" fillOpacity="0.3" />
		<mask id="maskUp" mask-type="alpha" maskUnits="userSpaceOnUse" x="3" y="4" width="8" height="5">
			<path fillRule="evenodd" clipRule="evenodd" d="M7 4.6665L3.5 8.1665L4.3225 8.989L7 6.31734L9.6775 8.989L10.5 8.1665L7 4.6665Z" fill="white" />
		</mask>
		<g mask="url(#maskUp)">
			<rect x="-7.5835" y="-7.5835" width="29.1667" height="29.1667" fill="#69B868" />
		</g>
	</svg>;

	//SVG down/negative icon
	const down = <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fillRule="evenodd" clipRule="evenodd" d="M7 13C10.3137 13 13 10.3137 13 7C13 3.68629 10.3137 1 7 1C3.68629 1 1 3.68629 1 7C1 10.3137 3.68629 13 7 13Z" fill="#D75A4B" fillOpacity="0.3" />
		<mask id="maskDown" mask-type="alpha" maskUnits="userSpaceOnUse" x="3" y="5" width="8" height="5">
			<path fillRule="evenodd" clipRule="evenodd" d="M9.6775 5.01074L7 7.68241L4.3225 5.01074L3.5 5.83324L7 9.33324L10.5 5.83324L9.6775 5.01074Z" fill="white" />
		</mask>
		<g mask="url(#maskDown)">
			<rect x="-7.5835" y="-7.5835" width="29.1667" height="29.1667" fill="#D75A4B" />
		</g>
	</svg>;

	const [ highlightValue, setHighlightValue ] = useState( null );
	const [ trend, setTrend ] = useState( null );
	const [ indicator, setIndicator ] = useState( null );
	const [ showTooltip, setShowTooltip ] = useState( false );
	const [ tooltipText, setTooltipText ] = useState( null );
	const [ tooltipPosition, setTooltipPosition ] = useState( { x: 0, y: 0 } );
	const [ stacked, setStacked ] = useState( false );

	const canvas = createRef();
	const config = createConfig( data );

	const amountRef = createRef();

	useEffect( () => {
		const newHighlightValue = getHighlightValue( data );
		const newTrend = getTrend( data ) === 0 ? 0 : numberToDigits( getTrend( data ), 3, 2 );
		const newTooltipText = getTooltipText( data );
		let newIndicator;

		switch ( true ) {
			case newTrend < 0 : {
				newIndicator = <Fragment>
					{ down }
					<span style={ { color: '#D75A4B' } }>
						{ `${ Math.abs( newTrend ) }%` }
					</span>
				</Fragment>;
				break;
			}
			case newTrend > 0 : {
				newIndicator = <Fragment>
					{ up }
					<span style={ { color: '#69B868' } }>
						{ `${ Math.abs( newTrend ) }%` }
					</span>
				</Fragment>;
				break;
			}
			default: {
				newIndicator = <Fragment>
					<span style={ { color: '#82878c' } }>
						{ `${ Math.abs( newTrend ) }%` }
					</span>
				</Fragment>;
				break;
			}
		}

		setHighlightValue( newHighlightValue );
		setTooltipText( newTooltipText );
		setTrend( newTrend );
		setIndicator( newIndicator );

		const ctx = canvas.current.getContext( '2d' );
		const chart = new ChartJS( ctx, config );

		return function cleanup() {
			chart.destroy();
		};
	}, [ data ] );

	// Use layout effect to determine if MiniChart should be stacked
	useLayoutEffect( () => {
		// Set stacked to true if the amount is greater than half the card width
		function checkStacked() {
			const node = amountRef.current;
			const amountRect = node.getBoundingClientRect();
			const cardRect = node.closest( '.givewp-card' ).getBoundingClientRect();
			const stack = amountRect.width > cardRect.width * 0.5 ? true : false;
			setStacked( stack );
		}

		window.addEventListener( 'resize', checkStacked );
		checkStacked();

		return function cleanup() {
			window.removeEventListener( 'resize', checkStacked );
		};
	} );

	return (
		<div className="givewp-mini-chart">
			<div className="header">
				<div className="title">{ title }</div>
				{ trend !== 'NaN' && (
					<div className="indicator"
						onMouseEnter={ ( event ) => {
							const target = event.target.classList.contains( 'indicator' ) ? event.target : event.target.closest( '.indicator' );
							const rect = target.getBoundingClientRect();
							setTooltipPosition( { x: target.offsetLeft + ( rect.width / 2 ), y: target.offsetTop } );
							setShowTooltip( true );
						} }
						onMouseLeave={ () => {
							setShowTooltip( false );
						} }>
						{ showTooltip && ( <Tooltip body={ tooltipText } position={ tooltipPosition } /> ) }
						{ indicator }
					</div>
				) }
			</div>
			<div className={ stacked ? 'content stacked' : 'content' }>
				<div className="amount" ref={ amountRef }>{ highlightValue && ( highlightValue ) }</div>
				<div className="chart">
					<canvas width={ 100 } height={ 40 } ref={ canvas }></canvas>
				</div>
			</div>
		</div>
	);
};

MiniChart.propTypes = {
	//Chart title
	title: PropTypes.string.isRequired,
	//Chart data
	data: PropTypes.object.isRequired,
};

MiniChart.defaultProps = {
	title: null,
	data: null,
};

export default MiniChart;
