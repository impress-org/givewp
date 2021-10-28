<div class="give-donation-summary-section">

    <?php if( $heading = $this->getSummaryHeading() ): ?>
    <div class="heading"><?php echo $heading; ?></div>
    <?php endif; ?>

    <div class="give-donation-summary-table-wrapper">

        <table>
            <thead>
                <tr>
                    <th>Donation Summary</th>
                    <th><button class="back-btn">edit donation</button></th>
                </tr>
            </thead>
            <tbody>



                <!-- PAYMENT AMOUNT -->
                <tr>
                    <td>
                        <div>Payment Amount</div>
                    </td>
                    <td data-tag="amount"></td>
                </tr>



                <!-- GIVING FREQUENCY -->
                <tr>
                    <td>
                        <div>Giving Frequency</div>
                        <?php if( $this->isRecurringEnabled() ): ?>
                        <span class="give-donation-summary-help-text js-give-donation-summary-frequency-help-text">
                            <img src="<?php echo GIVE_PLUGIN_URL . 'src/DonationSummary/resources/images/info.svg'; ?>" alt="">
                            <?php
                                /* translators: 1: <button> open tag 2: close tag. */
                                echo sprintf( __( 'Consider making this donation %srecurring%s', 'give' ), '<button class="back-btn">', '</button>' );
                            ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span data-tag="recurring"></span>
                        <span data-tag="frequency"><?php echo __( 'One time', 'give' ); ?></span>
                    </td>
                </tr>



                <!-- COVER DONATION FEES -->
                <?php if( $this->isFeeRecoveryEnabled() ): ?>
                    <tr class="js-give-donation-summary-fees">
                        <td>
                            <div><?php echo __( 'Cover Donation Fees', 'give' ); ?></div>
                            <span class="give-donation-summary-help-text">
                                <img src="<?php echo GIVE_PLUGIN_URL . 'src/DonationSummary/resources/images/info.svg'; ?>" alt="">
                                <?php echo __( 'Ensures 100% of your donation reaches our cause', 'give' ); ?>
                            </span>
                        </td>
                        <td data-tag="fees">{fees}</td>
                    </tr>
                <?php endif; ?>



            </tbody>
            <tfoot>


                <!-- TOTAL DONATION AMOUNT (INCLUDING FEES) -->
                <tr>
                    <th><?php echo __( 'Donation Total', 'give' ); ?></th>
                    <th data-tag="total"></th>
                </tr>



            </tfoot>
        </table>
    </div>
</div>
