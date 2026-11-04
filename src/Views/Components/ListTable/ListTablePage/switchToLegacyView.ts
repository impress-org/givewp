import type ListTableApi from '../api';

/**
 * Saves the admin's choice of the legacy screen, through the list endpoint's `/view` action, then
 * shows it. Reloads the page by default.
 *
 * @since TBD
 */
export default async function switchToLegacyView(
    api: ListTableApi,
    onSwitched: () => void = () => window.location.reload()
): Promise<void> {
    await api.fetchWithArgs('/view', {isLegacy: 1});
    onSwitched();
}
