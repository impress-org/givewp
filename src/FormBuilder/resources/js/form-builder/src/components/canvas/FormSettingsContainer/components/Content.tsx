import {useFormSettingsContext} from '@givewp/form-builder/components/canvas/FormSettingsContainer';

function renderRoutes(routes) {
    const [state] = useFormSettingsContext();

    return routes.map((route) => {
        if (route.children && route.children.length > 0) {
            return renderRoutes(route.children);
        }
        return state.activeRoute === route.path && route.element;
    });
}
export default function Content({routes}) {
    return (
        <div className={'givewp-form-settings__content'}>
            {renderRoutes(routes)}
        </div>
    );
}
