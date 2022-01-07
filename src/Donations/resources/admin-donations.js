import ReactDOM from 'react-dom';

function AdminDonations() {
    // Fetch list and render...
    return (
        <div>
            <h1>Donations</h1>
            <table>
                <thead></thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
        </div>
    );
}

ReactDOM.render(<AdminDonations />, document.getElementById('give-admin-donations-root'));
