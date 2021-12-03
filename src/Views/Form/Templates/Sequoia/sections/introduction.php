<?php

use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;

$formInfo = get_post(FrontendFormTemplateUtils::getFormId());

/* @var \Give\Form\Template $formTemplate */
$formTemplate = Give()->templates->getTemplate();

// Get headline and description
$headline = $formTemplate->getFormHeading($formInfo->ID);
$description = $formTemplate->getFormExcerpt($formInfo->ID);
$image = $formTemplate->getFormFeaturedImage($formInfo->ID);
?>

<div class="give-section introduction">
    <h2 class="headline">
        <?php
        echo $headline; ?>
    </h2>
    <?php
    if ( ! empty($description)) : ?>
        <div class="seperator"></div>
        <p class="description">
            <?php
            echo $description; ?>
        </p>
    <?php
    endif; ?>
    <?php
    if ( ! empty($image)) : ?>
        <div class="image">
            <img src="<?php
            echo $image; ?>" />
        </div>
    <?php
    endif; ?>

    <?php
    require 'income-stats.php';
    require 'progress-bar.php';
    ?>
