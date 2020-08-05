export default {
	fn: {
		/**
		 * Copy style.
		 *
		 * @since 2.8.0
		 *
		 * @param {object} sourceNode Source not selector
		 * @param {object} targetNode Target not selector
		 * @param {object} whileListedProperties List of style properties
		 */
		copyNodeStyle: function( sourceNode, targetNode, whileListedProperties ) {
			const computedStyle = window.getComputedStyle( sourceNode );

			if ( whileListedProperties ) {
				whileListedProperties.forEach( key => targetNode.style.setProperty( key, computedStyle.getPropertyValue( key ), computedStyle.getPropertyPriority( key ) ) );
			}
		},
	},
};
