import type {Gateway} from '@givewp/forms/types';

type PropTypes = {
    firstName: string;
    lastName?: string;
    email: string;
    status: string;
    gateway: Gateway;
    amount: number;
    total: number;
};

export default function DonationReceipt({firstName, lastName, email, status, gateway, amount, total}: PropTypes) {
    return (
        <section>
            <h2>A great big thank you!</h2>
            <p>
                {firstName}, your contribution means a lot and will be put to good use in making a difference. We’ve
                sent your donation receipt to {email}.
            </p>
            <div>
                <div>
                    <h3>Donor Details</h3>
                    <ul>
                        <li>
                            <b>Donor Name:</b> {firstName} {lastName}
                        </li>
                        <li>
                            <b>Email Address:</b> {email}
                        </li>
                    </ul>
                </div>
                <div>
                    <h3>Donation Details</h3>
                    <ul>
                        <li>
                            <b>Payment Status:</b> {status}
                        </li>
                        <li>
                            <b>Payment Method:</b> {gateway.settings.label}
                        </li>
                        <li>
                            <b>Donation Amount:</b> {amount}
                        </li>
                        <li>
                            <b>Donation Total:</b> {total}
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    );
}
