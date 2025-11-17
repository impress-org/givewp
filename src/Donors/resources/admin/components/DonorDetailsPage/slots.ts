import {createSlotFill} from '@wordpress/components';

/**
 * @since 4.4.0
 */
const {Slot: ProfileSectionsSlot, Fill: ProfileSectionsFill} = createSlotFill('GiveWP/DonorDetails/Profile/Sections');
/**
 * @since 4.10.0
 */
const {Slot: OverviewSidebarSlot, Fill: OverviewSidebarFill} = createSlotFill('GiveWP/DonorDetails/Overview/Sidebar');

export {
    ProfileSectionsSlot,
    ProfileSectionsFill,
    OverviewSidebarSlot,
    OverviewSidebarFill
};
