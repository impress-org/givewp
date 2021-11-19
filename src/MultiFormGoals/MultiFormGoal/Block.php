<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\MultiFormGoal\Model as MultiFormGoal;

class Block
{

    /**
     * Registers Multi-Form Goal block
     *
     * @since 2.9.0
     **/
    public function addBlock()
    {
        register_block_type(
            'give/multi-form-goal',
            [
                'render_callback' => [$this, 'renderCallback'],
            ]
        );
    }

    /**
     * Returns Progress Bar block markup
     *
     * @since 2.9.0
     **/
    public function renderCallback($attributes, $content)
    {
        $multiFormGoal = new MultiFormGoal(
            [
                'innerBlocks' => $content,
            ]
        );

        return $multiFormGoal->getOutput();
    }
}
