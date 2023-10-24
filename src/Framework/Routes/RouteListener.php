<?php

namespace Give\Framework\Routes;

class RouteListener
{
    /**
     * @var string
     */
    public $event;
    /**
     * @var string
     */
    public $listener;

    public function __construct(string $event, string $listener)
    {
        $this->event = $event;
        $this->listener = $listener;
    }

    /**
     * @since 3.0.0
     */
    public function isValid(array $request, callable $validation = null): bool
    {
        $eventValid = isset($request['givewp-event']) && $request['givewp-event'] === $this->event;
        $listenerValid = isset($request['givewp-listener']) && $request['givewp-listener'] === $this->listener;

        $validationValid = !$validation || $validation($request);

        return $eventValid && $listenerValid && $validationValid;
    }

    /**
     * @since 3.0.0
     */
    public function toUrl(string $originUrl, array $args = []): string
    {
        return esc_url_raw(
            add_query_arg(
                array_merge(
                    [
                        'givewp-event' => $this->event,
                        'givewp-listener' => $this->listener,
                    ],
                    $args
                ),
                $originUrl
            )
        );
    }
}