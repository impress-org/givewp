import giveNextGenExports from '../types/giveNextGenExports';

declare global {
    interface Window {
        giveNextGenExports: giveNextGenExports;
    }
}

export default function getWindowData() {
    return window.giveNextGenExports;
}
