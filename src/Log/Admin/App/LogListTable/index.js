import { Card, Table } from 'GiveComponents';
import { useLogFetch } from '../api';

const LogListTable = () => {
	const { data, isLoading } = useLogFetch( '/get' );

	const headerRow = [
		{ label: 'ID' },
		{ label: 'Type' },
		{ label: 'Category' },
		{ label: 'Source' },
		{ label: 'Message' },
		{
			label: 'Date',
			sort: true,
			sortCallback: ( direction ) => {
				// eslint-disable-next-line no-console
				console.log( 'sort by date column', direction );
			},
		},
	];

	const columns = [
		'id',
		'type',
		'category',
		'source',
		'message',
		'date',
	];

	const columnFilters = {
		type: ( type ) => {
			return (
				<strong>
					{ type }
				</strong>
			);
		},
	};

	return (
		<Card>
			<Table
				headerRow={ headerRow }
				columns={ columns }
				data={ data }
				columnFilters={ columnFilters }
				isLoading={ isLoading }
				stripped={ false }
			/>
		</Card>
	);
};

export default LogListTable;
