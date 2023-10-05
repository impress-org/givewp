<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class DonationGoal extends FormMigrationStep
{
    public function process()
    {
        $this->formV3->settings->enableDonationGoal = $this->formV2->isDonationGoalEnabled();
        $this->formV3->settings->goalType = $this->formV2->getDonationGoalType();
        $this->formV3->settings->goalAmount = $this->formV2->getDonationGoalAmount();
        $this->formV3->settings->enableAutoClose = $this->formV2->isAutoClosedEnabled();
        $this->formV3->settings->goalAchievedMessage = $this->formV2->getGoalAchievedMessage();
        // @note `_give_goal_color` is not supported in v3 forms (defers to the Form Design).
    }
}
