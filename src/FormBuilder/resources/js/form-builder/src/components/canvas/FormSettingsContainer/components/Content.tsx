import { useFormSettingsContext } from "@givewp/form-builder/components/canvas/FormSettingsContainer";
import { Route } from "@givewp/form-builder/components/canvas/FormSettings";
import { setFormSettings, useFormState, useFormStateDispatch } from "@givewp/form-builder/stores/form-state";

/**
 * @since 3.3.0
 */
function RenderRoutes({routes}: {routes: Route[]}) {
    const [state] = useFormSettingsContext();
    const {settings} = useFormState();
    const dispatch = useFormStateDispatch();
    const setSettings = (props: {}) => dispatch(setFormSettings(props));

    return (
        <>
            {routes.map((route) => {
                if (route.childRoutes && route.childRoutes.length > 0) {
                    return <RenderRoutes key={route.path} routes={route.childRoutes} />;
                }
                const Element = route.element;
                return (
                    state.activeRoute === route.path && (
                        <Element key={route.path} settings={settings} setSettings={setSettings} />
                    )
                );
            })}
        </>
    );
}

/**
 * @since 3.3.0
 */
export default function Content({routes}: {routes: Route[]}) {
    return (
        <div className={'givewp-form-settings__content'}>
            <RenderRoutes routes={routes} />
        </div>
    );
}
