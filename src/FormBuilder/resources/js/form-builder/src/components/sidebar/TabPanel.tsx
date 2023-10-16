/**
 * @note This is a fork of the WordPress component.
 *
 * Substantive changes:
 *  - Replace useState hook with injected state for external control of the selected tab.
 *
 * Maintenance changes:
 *  - Removed import of useState.
 *  - Update relative component imports for NavigableMenu, Button.
 *  - Disabled eslint rule react-hooks/exhaustive-deps for useEffect in the TabPanel component.
 */

/**
 * External dependencies
 */
import classnames from 'classnames';
import {find, noop, partial} from 'lodash';

/**
 * WordPress dependencies
 */
import {useEffect} from '@wordpress/element';
import {useInstanceId} from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {Button, NavigableMenu} from '@wordpress/components';

const TabButton = ({tabId, onClick, children, selected, ...rest}) => (
    <Button
        role="tab"
        tabIndex={selected ? null : -1}
        aria-selected={selected}
        id={tabId}
        onClick={onClick}
        {...rest}
    >
        {children}
    </Button>
);

export default function TabPanel({
    className,
    children,
    tabs,
    initialTabName,
    orientation = 'horizontal' as 'horizontal' | 'vertical' | 'both',
    activeClass = 'is-active',
    onSelect = noop,
    state: [selected, setSelected] /** @note Injecting state for external control of the selected tab. */,
}) {
    const instanceId = useInstanceId(TabPanel, 'tab-panel');
    // const [selected, setSelected] = useState(null); /** @note Replaced with injected state. */

    const handleClick = (tabKey) => {
        setSelected(tabKey);
        onSelect(tabKey);
    };

    const onNavigate = (childIndex, child) => {
        child.click();
    };
    const selectedTab = find(tabs, {name: selected});
    const selectedId = `${instanceId}-${selectedTab?.name ?? 'none'}`;

    useEffect(() => {
        const newSelectedTab = find(tabs, {name: selected});
        if (!newSelectedTab) {
            setSelected(initialTabName || (tabs.length > 0 ? tabs[0].name : null));
        }
    }, [tabs]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <div className={className}>
            <NavigableMenu
                role="tablist"
                orientation={orientation}
                onNavigate={onNavigate}
                className="components-tab-panel__tabs"
            >
                {tabs.map((tab) => (
                    <TabButton
                        className={classnames('components-tab-panel__tabs-item', tab.className, {
                            [activeClass]: tab.name === selected,
                        })}
                        tabId={`${instanceId}-${tab.name}`}
                        aria-controls={`${instanceId}-${tab.name}-view`}
                        selected={tab.name === selected}
                        key={tab.name}
                        onClick={partial(handleClick, tab.name)}
                    >
                        {tab.title}
                    </TabButton>
                ))}
            </NavigableMenu>
            {selectedTab && (
                <div
                    key={selectedId}
                    aria-labelledby={selectedId}
                    role="tabpanel"
                    id={`${selectedId}-view`}
                    className="components-tab-panel__tab-content"
                >
                    {children(selectedTab)}
                </div>
            )}
        </div>
    );
}
