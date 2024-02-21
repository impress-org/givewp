<?php

namespace Give\Helpers;

use ActionScheduler;

/**
 * @see https://actionscheduler.org/
 *
 * @unreleased
 */
class AsBackgroundJobs
{
    /**
     * @unreleased
     *
     * @param string $hook     The hook to trigger.
     * @param array  $args     Arguments to pass when the hook triggers.
     * @param string $group    The group to assign this job to.
     * @param bool   $unique   Whether the action should be unique.
     * @param int    $priority Lower values take precedence over higher values. Defaults to 10, with acceptable values falling in the range 0-255.
     *
     * @return int The action ID. Zero if there was an error scheduling the action.
     */
    public static function enqueueAsyncAction(
        string $hook,
        array $args,
        string $group,
        bool $unique = false,
        int $priority = 10
    ): int {
        $enqueuedAction = as_get_scheduled_actions(
            [
                'hook' => $hook,
                'args' => $args,
                'group' => $group,
                'per_page' => 1,
            ],
            'ids'
        );

        /**
         * We are checking if an action with the same hook, args, and group already was enqueued previously
         * to prevent duplicated jobs. IMPORTANT: this is different from the $unique parameter which is used
         * only to restrict the creation of multiple actions with the same hook.
         */
        if (empty($enqueuedAction)) {
            return as_enqueue_async_action($hook, $args, $group, $unique, $priority);
        } else {
            return $enqueuedAction[0];
        }
    }

    /**
     * @unreleased
     *
     * @param string $group        The group to assign this job to.
     * @param string $status       ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     *
     * @return array
     */
    public static function getActionsByGroup(
        string $group,
        string $status = '',
        string $returnFormat = OBJECT
    ): array {
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
     * @unreleased
     *
     * @param string $group  The group to assign this job to.
     * @param string $status ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
     *
     * @return int Total deleted actions.
     */
    public function deleteActionsByGroup(string $group, string $status = ''): int
    {
        $actions = self::getActionsByGroup($group, $status, 'ids');

        $deletedActions = 0;
        foreach ($actions as $actionID) {
            ActionScheduler::store()->delete_action($actionID);
            $deletedActions++;
        }

        return $deletedActions;
    }
}
