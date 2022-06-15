<tr class="give-export-donors">
    <td scope="row" class="row-title">
        <h3>
            <span><?php
                esc_html_e('Export Donors', 'give'); ?></span>
        </h3>
        <p><?php
            esc_html_e('Download a CSV of donors.', 'give'); ?></p>
    </td>
    <td>
        <form method="post" id="give_donors_export" class="give-export-form">

            <h4 class="give-export-form--heading">
                <?php
                esc_html_e('Select Date Range:', 'give'); ?>
            </h4>

            <?php
            echo Give()->html->date_field(
                [
                    'id' => 'giveDonorExport-startDate',
                    'name' => 'giveDonorExport-startDate',
                    'placeholder' => esc_attr__('Start Date', 'give'),
                    'autocomplete' => 'off',
                ]
            );

            echo Give()->html->date_field(
                [
                    'id'           => 'giveDonorExport-endDate',
                    'name'         => 'giveDonorExport-endDate',
                    'placeholder'  => esc_attr__( 'End Date', 'give' ),
                    'autocomplete' => 'off',
                ]
            );

            printf(
                '<fieldset id="giveDonorExport-searchBy">
                    <label for="giveDonorExport-searchBy">
                        %s
                    </label>
                    <input type=radio id="giveDonorExport-searchByDonation"
                        name="searchBy" value="donation" checked/>
                    <label for="giveDonorExport-searchByDonation">
                        %s
                    </label>
                    <input type=radio id="giveDonorExport-searchByDonor" name="searchBy" value="donor"/>
                    <label for="giveDonorExport-searchByDonor">
                        %s
                    </label>
                </fieldset>',
                __('Search by:', 'give'),
                __('Donation date', 'give'),
                __('Donor creation date', 'give')
            );

            echo Give()->html->forms_dropdown(
                [
                    'name'   => 'forms',
                    'id'     => 'give_donor_export_form',
                    'chosen' => true,
                    'class'  => 'give-width-25em',
                ]
            );
            ?>
            <br>
            <input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="button-secondary"/>

            <div id="export-donor-options-wrap" class="give-clearfix">
                <p><?php esc_html_e( 'Export Columns:', 'give' ); ?></p>
                <ul id="give-export-option-ul">
                    <?php
                    $donor_export_columns = give_export_donors_get_default_columns();

                    foreach ( $donor_export_columns as $column_name => $column_label ) {
                        ?>
                        <li>
                            <label for="give-export-<?php echo esc_attr( $column_name ); ?>">
                                <input
                                    type="checkbox"
                                    checked
                                    name="give_export_columns[<?php echo esc_attr( $column_name ); ?>]"
                                    id="give-export-<?php echo esc_attr( $column_name ); ?>"
                                />
                                <?php echo esc_attr( $column_label ); ?>
                            </label>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>

            <?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
            <input type="hidden" name="give-export-class" value="Give_Donors_Export"/>
            <input type="hidden" name="give_export_option[query_id]" value="<?php echo uniqid( 'give_' ); ?>"/>
        </form>
    </td>
</tr>
