/**
 * Execute a function when the DOM is fully loaded.
 *
 * @param {function} fn
 */
export const domIsReady = fn => document.readyState !== 'loading'
  ? window.setTimeout(fn, 0)
  : document.addEventListener('DOMContentLoaded', fn);
