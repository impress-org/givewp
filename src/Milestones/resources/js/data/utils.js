const { useSelect } = wp.data;
const { __ } = wp.i18n;

export const useFormOptions = () => {
	const formOptions = useSelect( ( select ) => {
		const records = select( 'core' ).getEntityRecords( 'postType', 'give_forms' );
		if ( records ) {
			return records.map( ( record ) => {
				return {
					label: record.title.rendered ? record.title.rendered : __( '(no title)' ),
					value: record.id,
				};
			} );
		}
		return [];
	}, [] );
	return formOptions;
};

export const useTagOptions = () => {
	const tagOptions = useSelect( ( select ) => {
		const records = select( 'core' ).getEntityRecords( 'taxonomy', 'give_forms_tag', { per_page: 100 } );
		if ( records ) {
			return records.map( ( record ) => {
				return {
					label: record.name ? record.name : __( '(no title)' ),
					value: record.id,
				};
			} );
		}
		return [];
	}, [] );
	return tagOptions;
};

export const useCategoryOptions = () => {
	const categoryOptions = useSelect( ( select ) => {
		const records = select( 'core' ).getEntityRecords( 'taxonomy', 'give_forms_category', { per_page: 100 } );
		if ( records ) {
			return records.map( ( record ) => {
				return {
					label: record.name ? record.name : __( '(no title)' ),
					value: record.id,
				};
			} );
		}
		return [];
	}, [] );
	return categoryOptions;
};
