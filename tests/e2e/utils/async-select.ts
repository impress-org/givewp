import {Locator, Page} from '@wordpress/e2e-test-utils-playwright';

/**
 * The class prefix the list filters and the assign-campaign modal give react-select.
 *
 * react-select's options carry no `role=option` here, so the accessible tree cannot name them and
 * the class is the one stable hook. It is the prefix the product passes in, not a generated hash.
 */
const OPTION = '[class*="searchableSelect__option"]';

/**
 * Types `search` into a searchable async select and clicks the option that reads `label`.
 *
 * The options load from the server as the text is typed, so the click waits for the matching one
 * to appear rather than pressing Enter on whatever is highlighted first. `label` is what the option
 * shows, which can be more than was searched for: a draft form reads "<title> (Draft)".
 */
export async function chooseOption(page: Page, combobox: Locator, search: string, label = search): Promise<void> {
    await combobox.fill(search);
    await page.locator(OPTION).filter({hasText: label}).first().click();
}

/**
 * The options currently in an open select's menu.
 */
export function openOptions(page: Page): Locator {
    return page.locator(OPTION);
}

/**
 * Scrolls the open menu to its last option, which is what makes an async select ask for the next page.
 */
export async function scrollMenuToEnd(page: Page): Promise<void> {
    await page.locator('[class*="searchableSelect__menu-list"]').evaluate((list) => {
        list.scrollTop = list.scrollHeight;
    });
}
