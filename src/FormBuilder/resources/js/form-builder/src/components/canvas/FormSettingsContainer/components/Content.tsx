import { useFormSettingsContext } from "@givewp/form-builder/components/canvas/FormSettingsContainer";

/**
 * @unreleased
 */
function renderRoutes(routes) {
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
export default function Content({routes}) {
    return (
        <div className={'givewp-form-settings__content'}>
            {renderRoutes(routes)}
        </div>
    );
}
