var Give_Selector_Cache = {
	collection: {},

	get: function (selector, parent, refresh_cache) {
		// Bailout.
		if( ! jQuery ) {
			return -1;
		}

		// Set default parent.
		parent        = ( undefined !== parent ) ? parent : undefined;
		refresh_cache = ( refresh_cache === true );

		if (( undefined === this.collection[selector] ) || refresh_cache) {
			if ( undefined !== parent ) {
				this.collection[selector] = jQuery(selector, parent);
			} else {
				this.collection[selector] = jQuery(selector);
			}
		}

		return this.collection[selector];
	}
};