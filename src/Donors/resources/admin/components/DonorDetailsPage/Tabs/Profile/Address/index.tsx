/**
 * External Dependencies
 */
import { useState } from "react";
import { useFormContext } from "react-hook-form";

/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import EditAddressDialog from './EditAddressDialog';
import DeleteAddressDialog from './DeleteAddressDialog';
import BlankSlate from './BlankSlate';
import AddressItem from './AddressItem';
import styles from './styles.module.scss';
import { DonorAddress as DonorAddressType } from "../../../../types";

/**
 * @since 4.4.0
 */
export default function DonorAddress() {
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [addressToDelete, setAddressToDelete] = useState<number | null>(null);
    const [addressToEdit, setAddressToEdit] = useState<number | null>(null);

    const {
        watch,
        setValue,
    } = useFormContext();

    const addresses: DonorAddressType[] = watch('addresses') || [];

    const handleEditAddressAction = (addressIndex: number) => {
        setAddressToEdit(addressIndex);
        setIsEditDialogOpen(true);
    };

    const handleSetAsPrimaryAction = (addressIndex: number) => {
        if (addressIndex >= 0 && addressIndex < addresses.length) {
            const selectedAddress = addresses[addressIndex];
            const remainingAddresses = addresses.filter((_, index: number) => index !== addressIndex);
            const reorderedAddresses = [selectedAddress, ...remainingAddresses];

            setValue('addresses', reorderedAddresses, { shouldDirty: true });
        }
    };

    const handleDeleteAddressAction = (addressIndex: number) => {
        setAddressToDelete(addressIndex);
        setIsDeleteDialogOpen(true);
    };

    const handleEditAddressConfirm = (newAddress: DonorAddressType, addressIndex?: number) => {
        if (addressIndex !== null) {
            const newAddresses = [...addresses];
            newAddresses[addressIndex] = newAddress;
            setValue('addresses', newAddresses, { shouldDirty: true });
        } else {
            setValue('addresses', [...addresses, newAddress], { shouldDirty: true });
        }

        setIsEditDialogOpen(false);
        setAddressToEdit(null);
    };

    const handleDeleteAddressConfirm = () => {
        setValue('addresses', addresses.filter((_, index: number) => index !== addressToDelete), { shouldDirty: true });

        setIsDeleteDialogOpen(false);
        setAddressToDelete(null);
    };

    const sectionId = 'donor-addresses';
    const descriptionId = `${sectionId}-description`;

    return (
        <>
            <AdminSection
                title={__('Address', 'give')}
                description={__('Manage the address of the donor', 'give')}
            >
                <AdminSectionField>
                    <div
                        className={styles.donorAddress}
                        role="region"
                        aria-labelledby={sectionId}
                        aria-describedby={descriptionId}
                    >
                        {addresses.length === 0 ? (
                            <BlankSlate />
                        ) : (
                            <div
                                className={styles.addresses}
                                role="list"
                                aria-label={__('Donor addresses', 'give')}
                                aria-live="polite"
                            >
                                {addresses.map((address, index) => (
                                    <AddressItem
                                        key={index}
                                        address={address}
                                        index={index}
                                        totalAddresses={addresses.length}
                                        onEdit={handleEditAddressAction}
                                        onSetAsPrimary={handleSetAsPrimaryAction}
                                        onDelete={handleDeleteAddressAction}
                                    />
                                ))}
                            </div>
                        )}

                        <div className={styles.add}>
                            <button
                                className={styles.addButton}
                                onClick={(event) => {
                                    event.preventDefault();
                                    setIsEditDialogOpen(true);
                                    setAddressToEdit(null);
                                }}
                                aria-label={
                                    addresses.length === 0
                                        ? __('Add the first address for this donor', 'give')
                                        : `${__('Add new address. Currently', 'give')} ${addresses.length} ${addresses.length === 1 ? __('address', 'give') : __('addresses', 'give')} ${__('total', 'give')}`
                                }
                                aria-describedby={descriptionId}
                            >
                                {__('Add address', 'give')}
                            </button>
                        </div>
                    </div>
                </AdminSectionField>
            </AdminSection>

            <EditAddressDialog
                isOpen={isEditDialogOpen}
                handleClose={() => setIsEditDialogOpen(false)}
                handleConfirm={handleEditAddressConfirm}
                address={addresses[addressToEdit]}
                addressIndex={addressToEdit}
            />

            <DeleteAddressDialog
                isOpen={isDeleteDialogOpen}
                address={addresses[addressToDelete]}
                addressIndex={addressToDelete}
                handleClose={() => setIsDeleteDialogOpen(false)}
                handleConfirm={handleDeleteAddressConfirm}
            />
        </>
    );
}
