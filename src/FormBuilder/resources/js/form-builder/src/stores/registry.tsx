import {useEffect, useState} from "@wordpress/element";
import {createRegistry, withRegistry, RegistryProvider} from '@wordpress/data';
import {createHigherOrderComponent} from "@wordpress/compose";
import type {FC} from 'react';
import coreStore from './core';

const registry = createRegistry({
    [coreStore.name]: coreStore,
});

const STORE_NAME = 'givewp/form-builder';

const withRegistryProvider = createHigherOrderComponent(
    (WrappedComponent: FC) =>
        withRegistry((props) => {
            const {registry, settings, ...additionalProps} = props;
            const defaultSettings = applyDefaultSettings(settings);
            const {persistenceKey, preferencesKey, defaultPreferences, customStores = []} = defaultSettings.iso || {};
            const [subRegistry, setSubRegistry] = useState(null);

            useEffect(() => {
                // Create a new registry for this editor. We have the STORE_NAME for storing blocks and other data
                // and a duplicate of `core/block-editor` for storing block selections
                const newRegistry = createRegistry(
                    {
                        'core/reusable-blocks': reusableStore,
                        'core/interface': interfaceStore,
                    },
                    registry
                );

                // Enable the persistence plugin so we use settings in `localStorage`
                if (persistenceKey) {
                    // @ts-ignore
                    newRegistry.use(plugins.persistence, {
                        persistenceKey,
                    });
                }

                // Create our custom store
                const store = newRegistry.registerStore(STORE_NAME, storeConfig(preferencesKey, defaultPreferences));

                // Create the core/block-editor store separatley as we need the persistence plugin to be active
                // const blockEditorStore = newRegistry.registerStore('core/block-editor', {
                //     ...blockEditorStoreConfig,
                //     persist: ['preferences'],
                // });

                // Duplicate the core/editor store so we can decorate it
                // const editorStore = newRegistry.registerStore('core/editor', {
                //     ...coreEditorStoreConfig,
                //     selectors: {
                //         ...coreEditorStoreConfig.selectors,
                //         ...decoratedEditor(coreEditorStoreConfig.selectors, newRegistry.select),
                //     },
                //     persist: ['preferences'],
                // });

                // Create any custom stores inside our registry
                customStores.map((store) => {
                    registries.push(newRegistry.registerStore(store.name, store.config));
                });

                registries.push(store);
                registries.push(blockEditorStore);
                registries.push(editorStore);

                // @ts-ignore
                setSubRegistry(newRegistry);

                return function cleanup() {
                    registries = registries.filter((item) => item !== store);
                };
            }, [registry]);

            if (!subRegistry) {
                return null;
            }

            return (
                <RegistryProvider value={subRegistry}>
                    {/* @ts-ignore */}
                    <WrappedComponent {...additionalProps} settings={defaultSettings} />
                </RegistryProvider>
            );
        }),
    'withRegistryProvider'
);
