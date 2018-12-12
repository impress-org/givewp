/**
 * getSiteUrl from API root
 * @returns {string} siteurl
 */
export function getSiteUrl() {
	return wpApiSettings.root.replace( '/wp-json/', '' );
}
