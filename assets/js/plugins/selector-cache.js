var Give_Selector_Cache = {
	collection: {},

	get: function (selector, parent, refresh_cache) {
		// Set default parent.
		parent        = parent || '';
		refresh_cache = ( refresh_cache === true );

		if (( undefined === this.collection[selector] ) || refresh_cache) {
			if (parent.length && selector.length) {
				this.collection[selector] = jQuery(selector, parent);
			} else {
				this.collection[selector] = jQuery(selector);
			}
		}

		return this.collection[selector];
	}
};