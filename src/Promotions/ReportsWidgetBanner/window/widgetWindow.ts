/**
 * @since 3.13.0
 */

type windowData = {
    apiRoot: string;
    apiNonce: string;
    banner: {
       id: string;
       header: string;
       actionText: string;
       actionUrl: string;
    };
};

declare const window: {
    giveReportsWidget: windowData;
} & Window;

export function getWidgetWindowData(): windowData {
    return window.giveReportsWidget;
}
