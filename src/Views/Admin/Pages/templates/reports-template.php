<?php
// Reports page markup
// #reports-app is replaced by React app
?>
<div id="reports-app">
    <div class="wrap give-settings-page">
        <div class="give-settings-header">
            <h1 class="wp-heading-inline">
                Reports
            </h1>
            <div class="givewp-filters" style="padding-right: 20px">
                <div class="givewp-period-selector">
                    <div class="group">
                        <button>
                            <?php
                            _e('Day', 'give'); ?>
                        </button>
                        <button class="selected">
                            <?php
                            _e('Week', 'give'); ?>
                        </button>
                        <button>
                            <?php
                            _e('Month', 'give'); ?>
                        </button>
                        <button>
                            <?php
                            _e('Year', 'give'); ?>
                        </button>
                        <button>
                            <?php
                            _e('All Time', 'give'); ?>
                        </button>
                    </div>
                </div>
                <div class="givewp-reports-settings__toggle">
                    <button class="givewp-reports-button givewp-reports-button--icon">
                        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M17 10.9727H14.82C14.65 11.6727 14.38 12.3227 14.01 12.9027L15.55 14.4427L13.45 16.5427L11.91 15.0027C11.33 15.3627 10.68 15.6327 10 15.7927V17.9727H7V15.7927C6.32 15.6327 5.67 15.3627 5.09 15.0027L3.55 16.5427L1.43 14.4227L2.97 12.8827C2.61 12.3027 2.34 11.6527 2.18 10.9727H0V8.00266H2.17C2.33 7.30266 2.61 6.65266 2.97 6.06266L1.43 4.52266L3.53 2.42266L5.07 3.96266C5.65 3.59266 6.31 3.32266 7 3.15266V0.972656H10V3.15266C10.68 3.31266 11.33 3.58266 11.91 3.94266L13.45 2.40266L15.57 4.52266L14.03 6.06266C14.39 6.65266 14.67 7.30266 14.83 8.00266H17V10.9727ZM8.5 12.4727C10.16 12.4727 11.5 11.1327 11.5 9.47266C11.5 7.81266 10.16 6.47266 8.5 6.47266C6.84 6.47266 5.5 7.81266 5.5 9.47266C5.5 11.1327 6.84 12.4727 8.5 12.4727Z"
                                  fill="#888888"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="nav-tab-wrapper give-nav-tab-wrapper" style="height: auto; overflow: visible;">
            <a class="nav-tab nav-tab-active" href="#/">
                <?php
                _e('Overview', 'give'); ?>
            </a>
            <a class="nav-tab"
               href="http://givewp.local/wp-admin/edit.php?post_type=give_forms&page=give-reports&legacy=1">
                <?php
                _e('Legacy Reports', 'give'); ?>
            </a>
        </div>
        <div class="givewp-loading-notice">
            <div class="givewp-loading-notice__card">
                <div class="givewp-spinner"></div>
                <h2>
                    <?php
                    echo __('Loading your latest', 'give') . '<br>' . __('donation activity', 'give'); ?>
                </h2>
            </div>
        </div>
    </div>
</div>
