import {createSlotFill} from '@wordpress/components';

/**
 * @since 4.4.0
 */
const {Slot: ProfileSectionsSlot, Fill: ProfileSectionsFill} = createSlotFill('GiveWP/DonorDetails/Profile/Sections');
/**
 * @unreleased
 */
const {Slot: OverviewSidebarSlot, Fill: OverviewSidebarFill} = createSlotFill('GiveWP/DonorDetails/Overview/Sidebar');

export {
    ProfileSectionsSlot,
    ProfileSectionsFill,
    OverviewSidebarSlot,
    OverviewSidebarFill
};
