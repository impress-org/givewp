<tr class="give-export-donors-by-donation">
    <td scope="row" class="row-title">
        <h3>
            <span><?php esc_html_e( 'Export Donors By Donation Date', 'give' ); ?></span>
        </h3>
        <p><?php esc_html_e( 'Download a CSV of donors based on donation for a given time period.', 'give' ); ?></p>
    </td>
    <td>
        <form method="post" id="give_donors_by_donation_export" class="give-export-form">

            <?php
            echo Give()->html->date_field(
                [
                    'id'           => 'give_donors_by_donation_export_donation_start_date',
                    'name'         => 'start_date',
                    'placeholder'  => esc_attr__( 'Donation Start Date', 'give' ),
                    'autocomplete' => 'off',
                ]
            );

            echo Give()->html->date_field(
                [
                    'id'           => 'give_donors_by_donation_export_donation_end_date',
                    'name'         => 'end_date',
                    'placeholder'  => esc_attr__( 'Donation End Date', 'give' ),
                    'autocomplete' => 'off',
                ]
            );
            ?>
            <br>
            <input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="button-secondary"/>

            <?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
            <input type="hidden" name="give-export-class" value="Give_Donors_By_Donation_Export"/>
            <input type="hidden" name="give_export_option[query_id]" value="<?php echo uniqid( 'give_' ); ?>"/>
        </form>
    </td>
</tr>
