/**
 * External dependencies
 */
import { isEmpty, pickBy, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

/**
 * Wordpress dependencies
 */
const { __ } = wp.i18n;
const { withAPIData } = wp.components;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import DonorWallPreview from './components/preview';

/**
 * Render Block UI For Editor
 */

const GiveDonorWall = ( props ) => {
	console.log( props )
	const { latestForms } = props;
	const { isLoading, data } = latestForms;

	// Render block UI
	let blockUI;

	if ( isLoading || isUndefined( data ) ) {
		blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
	} else if ( isEmpty( data ) ) {
		blockUI = <NoForms />;
	} else {
		blockUI = <DonorWallPreview
			html={ data }
			{ ... { ...props } } />;
	}

	return ( <div className={ props.className } key="GiveDonorWallBlockUI">{ blockUI }</div> );
};

/**
 * Export component attaching withAPIData
 */
export default withAPIData( ( props ) => {
	const { columns, showAvatar, showName, showTotal, showDate, showComments } = props.attributes;

	const parameters = stringify( pickBy( {
		columns: columns,
		show_avatar: showAvatar,
		show_name: showName,
		show_total: showTotal,
		show_time: showDate,
		show_comments: showComments,
		},
		value => ! isUndefined( value ) )
	);

	return {
		latestForms: `/${ giveApiSettings.rest_base }/donor-wall/?${ parameters }`,
	};
} )( GiveDonorWall );
