declare module '*.svg';
declare module '*.module.css';
declare module '*.module.scss';
declare module '*.png';
// this makes the wp or window.wp global variable available in the eyes of TypeScript
declare var wp;
// SCSS imported with ?inline compiles to a CSS string instead of an extracted file (see webpack.config.js)
declare module '*.scss?inline' {
    const css: string;
    export default css;
}
