import { useFormSettingsContext } from "@givewp/form-builder/components/canvas/FormSettingsContainer";
import { Route } from "@givewp/form-builder/components/canvas/FormSettings";

/**
 * @unreleased
 */
function renderRoutes(routes: Route[]) {
    const [state] = useFormSettingsContext();

    return routes.map((route) => {
        if (route.childRoutes && route.childRoutes.length > 0) {
            return renderRoutes(route.childRoutes);
        }
        return state.activeRoute === route.path && route.element;
    });
}

/**
 * @unreleased
 */
export default function Content({routes}: {routes: Route[]}) {
    return <div className={'givewp-form-settings__content'}>{renderRoutes(routes)}</div>;
}
