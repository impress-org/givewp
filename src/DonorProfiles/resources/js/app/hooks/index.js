import { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';

// From use hooks, see: https://usehooks.com/useWindowSize/

export const useAccentColor = () => {
	return useSelector( ( state ) => state.accentColor );
};

export const useWindowSize = () => {
	// Initialize state with undefined width/height so server and client renders match
	// Learn more here: https://joshwcomeau.com/react/the-perils-of-rehydration/
	const [ windowSize, setWindowSize ] = useState( {
		width: undefined,
		height: undefined,
	} );

	useEffect( () => {
		// Handler to call on window resize
		function handleResize() {
			// Set window width/height to state
			setWindowSize( {
				width: window.top.innerWidth,
				height: window.top.innerHeight,
			} );
		}

		// Add event listener
		window.top.addEventListener( 'resize', handleResize );

		// Call handler right away so state gets updated with initial window size
		handleResize();

		// Remove event listener on cleanup
		return () => window.top.removeEventListener( 'resize', handleResize );
	}, [] ); // Empty array ensures that effect is only run on mount

	return windowSize;
};
