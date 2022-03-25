export default function getWindowData(...props) {
    return props.map((prop) => {
        return window.giveNextGenExports[prop];
    });
}
