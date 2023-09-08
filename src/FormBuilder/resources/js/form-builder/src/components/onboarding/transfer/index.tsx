import TransferNotice from './TransferNotice'
import TransferDialog from './TransferDialog'
import UpgradeDialog from './UpgradeDialog'
import ReturnButton from './ReturnButton';

export default function Transfer() {
    return (
        <>
            <TransferNotice />
            <TransferDialog />
            <UpgradeDialog />
            <ReturnButton />
        </>
    )
}
