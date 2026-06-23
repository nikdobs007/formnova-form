<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap formnova-analytics-wrap">

    <h1 class="formnova-analytics-title">
        FormNova Analytics
    </h1>

    <table class="formnova-analytics-table">

        <tr>
            <th>Total Forms</th>
            <td>
                <?php echo esc_html($total_forms); ?>
            </td>
        </tr>

        <tr>
            <th>Total Submissions</th>
            <td>
                <?php echo esc_html($total_submissions); ?>
            </td>
        </tr>

        <tr>
            <th>Today's Submissions</th>
            <td>
                <?php echo esc_html($today_submissions); ?>
            </td>
        </tr>

        <tr>
            <th>Last 7 Days</th>
            <td>
                <?php echo esc_html($last_7_days); ?>
            </td>
        </tr>

        <tr>
            <th>Top Form ID</th>
            <td>
                <?php
                echo !empty($top_form)
                    ? esc_html($top_form->form_id)
                    : '-';
                ?>
            </td>
        </tr>

    </table>

</div>