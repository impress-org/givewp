<?php

namespace Give\Framework\Support\Facades\ActionScheduler;

use ActionScheduler;

/**
 * @see https://actionscheduler.org/
 *
 * @since 3.6.0
 */
class AsBackgroundJobsFacade
{
    /**
     * @since 3.6.0
     *
     * @param string $hook     The hook to trigger.
     * @param array  $args     Arguments to pass when the hook triggers.
     * @param string $group    The group to assign this job to.
     * @param bool   $unique   Whether the action should be unique.
     * @param int    $priority Lower values take precedence over higher values. Defaults to 10, with acceptable values falling in the range 0-255.
     *
     * @return int The action ID. Zero if there was an error scheduling the action.
     */
    public function enqueueAsyncAction(
        string $hook,
        array $args,
        string $group,
        bool $unique = false,
        int $priority = 10
    ): int {
        /**
         * We are checking if an action with the same hook, args, and group already was enqueued previously
         * to prevent duplicated jobs. IMPORTANT: this is different from the $unique parameter which is used
         * only to restrict the creation of multiple actions with the same hook.
         */
        $enqueuedAction = $this->getActionByHookArgsGroup($hook, $args, $group, 'ids');
        if (empty($enqueuedAction)) {
            return as_enqueue_async_action($hook, $args, $group, $unique, $priority);
        } else {
            return $enqueuedAction[0];
        }
    }

    /**
     * @since 3.6.0
     *
     * @param string $hook         The hook to trigger.
     * @param array  $args         Arguments to pass when the hook triggers.
     * @param string $group        The group to assign this job to.
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     * @param string $status       ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     *
     * @return array
     */
    public function getActionByHookArgsGroup(
        string $hook,
        array $args,
        string $group,
        string $returnFormat = OBJECT,
        string $status = ''
    ): array {
        $args = [
            'hook' => $hook,
            'args' => $args,
            'group' => $group,
            'per_page' => 1,
        ];

        if ( ! empty($status)) {
            $args['status'] = $status;
        }

        return as_get_scheduled_actions($args, $returnFormat);
    }

    /**
     * @since 3.6.0
     *
     * @param string $group        The group to assign this job to.
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     * @param string $status ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     *
     * @return array
     */
    public function getActionsByGroup(string $group, string $returnFormat = OBJECT, string $status = ''): array
    {
        $args = [
            'group' => $group,
            'per_page' => 0,
            'order' => 'DESC',
        ];

        if ( ! empty($status)) {
            $args['status'] = $status;
        }

        return as_get_scheduled_actions($args, $returnFormat);
    }

    /**
     * @since 3.6.0
     *
     * @param string $group  The group to assign this job to.
     * @param string $status ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     *
     * @return int Total deleted actions.
     */
    public function deleteActionsByGroup(string $group, string $status = ''): int
    {
        $actions = $this->getActionsByGroup($group, 'ids', $status);

        $deletedActions = 0;
        foreach ($actions as $actionID) {
            ActionScheduler::store()->delete_action($actionID);
            $deletedActions++;
        }

        return $deletedActions;
    }
}
