import './style.scss'

const DonationItem = ({status, amount, time, donor, source}) => {

    const elapsed = '3 hours ago'

    return (
        <div>
            <div>
                {icon}
            </div>
            <div>
                <p>
                    <span>{amount}</span>
                    <span>{status}</span>
                    <span>{elapsed}</span>
                </p>
                <p>
                    {donor.name}<strong>(#{donor.id})</strong>
                </p>
                <p>
                    <span>{source}</span>
                </p>
            </div>
        </div>
    )
}

export default DonationItem