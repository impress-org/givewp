<?php

namespace Give\Framework\Routes;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\Language;
use WP;

use function is_callable;
use function str_contains;

/**
 * @since 4.17.0 Add script routes served from a plugin-controlled URL
 * @since 3.0.0
 */
class Router
{
    /**
     * Base path segment for pretty script URLs. Fixed on purpose: these URLs are
     * pasted into third-party sites, so they must not follow any setting.
     *
     * @since 4.17.0
     */
    protected string $scriptBase = 'give';

    /**
     * @since 3.0.0
     * @param  string  $uri
     * @param  string|callable  $action
     * @param  string  $method
     *
     * @return void
     */
    public function get(string $uri, $action, $method = '__invoke')
    {
        $this->addRoute('GET', $method, $uri, $action);
    }

    /**
     * @since 3.0.0
     * @param  string  $uri
     * @param  string|callable  $action
     * @param  string  $method
     *
     * @return void
     */
    public function post(string $uri, $action, $method = '__invoke')
    {
        $this->addRoute('POST', $method, $uri, $action);
    }

    /**
     * Serve a built script from a URL the plugin controls, so the file can move
     * without breaking URLs already pasted elsewhere. Matching happens on
     * parse_request against the path WordPress already resolved, so no rewrite
     * rule is registered and nothing needs flushing. See scriptUrl() for the
     * URL shape per permalink setting.
     *
     * @since 4.17.0
     *
     * @param string $uri  Path below the base, e.g. "embed/donation-form/script.js"
     * @param string $file Absolute path to the built script
     *
     * @return ScriptResponse The response, so callers can chain localize()
     */
    public function script(string $uri, string $file): ScriptResponse
    {
        $response = new ScriptResponse($file);

        add_action('parse_request', function (WP $wp) use ($uri, $response) {
            $request = $this->scriptRequest($wp, $uri);

            if ($request === null) {
                return;
            }

            $response->send($request);
        });

        return $response;
    }

    /**
     * Pretty permalinks:  /give/{uri}
     * Index permalinks:   /index.php/give/{uri}
     * Plain permalinks:   /?givewp-route={uri}
     *
     * @since 4.17.0
     *
     * @param array $args Query arguments appended to the URL; the script's localize callable
     *                    receives them at request time.
     */
    public function scriptUrl(string $uri, array $args = []): string
    {
        global $wp_rewrite;

        if (!$wp_rewrite->using_permalinks()) {
            $url = $this->url($uri);
        } else {
            $prefix = $wp_rewrite->using_index_permalinks() ? $wp_rewrite->index . '/' : '';
            $url = home_url("/{$prefix}{$this->scriptBase}/{$uri}");
        }

        if (!$args) {
            return $url;
        }

        // Appended by hand: add_query_arg() would re-encode the givewp-route value's slashes.
        return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($args);
    }

    /**
     * @since 4.17.0
     */
    public function isScriptRequested(WP $wp, string $uri): bool
    {
        return $this->scriptRequest($wp, $uri) !== null;
    }

    /**
     * The request data for a script route when the current request is for it, null otherwise.
     * The data is the query string run through give_clean(), minus givewp-route itself, so a
     * `?form-id=42` argument arrives as `['form-id' => '42']`. Matching covers the pretty path and
     * the givewp-route query var, each with an optional numeric segment before the file name
     * (embed/donation-form/42/script.js), which comes back as `id`. The segment exists for caches
     * that drop query strings from their key; a query argument is the primary way to pass data
     * to a script route.
     *
     * @since 4.17.0
     */
    public function scriptRequest(WP $wp, string $uri): ?array
    {
        $directory = dirname($uri);
        $directory = $directory === '.' ? '' : preg_quote($directory, '#') . '/';
        $pattern = $directory . '(?:(\d+)/)?' . preg_quote(basename($uri), '#');

        $candidates = [
            '#^' . preg_quote($this->scriptBase, '#') . '/' . $pattern . '$#' => (string)$wp->request,
            '#^' . $pattern . '$#' => isset($_GET['givewp-route']) ? (string)$_GET['givewp-route'] : '',
        ];

        foreach ($candidates as $regex => $subject) {
            if ($subject === '' || !preg_match($regex, $subject, $matches)) {
                continue;
            }

            $request = $this->getDataFromGetRequest();
            unset($request['givewp-route']);

            if (!empty($matches[1])) {
                $request['id'] = (int)$matches[1];
            }

            return $request;
        }

        return null;
    }

    /**
     * @since 3.0.0
     */
    protected function isRouteValid(string $route): bool
    {
        return isset($_GET['givewp-route']) && $_GET['givewp-route'] === $route;
    }

    /**
     * @since 3.0.0
     */
    protected function getRequestDataByType(string $type): array
    {
        if ($type === 'POST'){
            return $this->getDataFromPostRequest();
        }

        return $this->getDataFromGetRequest();
    }

    /**
     * @since 3.0.0
     */
    protected function getDataFromPostRequest(): array
    {
        $requestData = [];

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return $requestData;
        }

        if (str_contains($_SERVER['CONTENT_TYPE'], "application/json")) {
            $requestData = file_get_contents('php://input');
            $requestData = json_decode($requestData, true);
            $requestData = give_clean($requestData);
        } else {
            $requestData = array_merge(
                give_clean($_REQUEST),
                give_clean($_FILES)
            );
        }

        return $requestData;
    }

    /**
     * @since 3.0.0
     */
    protected function getDataFromGetRequest(): array
    {
        return give_clean($_GET);
    }

    /**
     * @since 3.22.0 Add locale support
     * @since 3.0.0
     *
     * @param  string  $type
     * @param  string  $method
     * @param  string  $uri
     * @param $action
     *
     * @return void
     */
    protected function addRoute(string $type, string $method, string $uri, $action)
    {
        add_action('template_redirect', function () use ($type, $method, $uri, $action) {
            if (!$this->isRouteValid($uri)) {
                // fail silently for use with template_redirect
                return;
            }

            $request = $this->getRequestDataByType($type);
            $request['locale'] = ! empty($request['locale']) ? $request['locale'] : Language::getLocale();

            if (is_callable($action)) {
                return $action($request);
            }

            if (!method_exists($action, $method)) {
                throw new InvalidArgumentException("The method $method does not exist on $action");
            }

            return give($action)->$method($request);
        });
    }

    /**
     * @since 4.3.0 Use trailingslashit() method to prevent errors on websites installed in subdirectories
     * @since 3.0.0
     */
    public function url(string $uri, array $args = []): string
    {
        return add_query_arg(
            array_merge(
                ['givewp-route' => $uri],
                $args
            ),
            trailingslashit(home_url())
        );
    }
}
