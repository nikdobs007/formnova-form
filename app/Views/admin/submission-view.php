<?php

if (!defined('ABSPATH')) {
    exit;
}

$formnova_submission_id = formnova_request(
    'id',
    'get'
);

if (empty($formnova_submission_id)) {
    wp_die(
        esc_html__(
            'Submission ID missing.',
            'formnova-form'
        )
    );
}

$formnova_submission_id = absint(
    $formnova_submission_id
);

$formnova_model = new FormNova_Submission_Model();

$formnova_submission = $formnova_model->get($formnova_submission_id);

if (!$formnova_submission) {
    wp_die('Submission not found.');
}

/*
 * Decode submission JSON data
 */
$formnova_data = json_decode(
    $formnova_submission->data,
    true
);

$formnova_field_model = new FormNova_Field_Model();

$formnova_fields = $formnova_field_model->get_by_form(
    $formnova_submission->form_id
);

$formnova_field_labels = [];

if (!empty($formnova_fields)) {

    foreach ($formnova_fields as $formnova_field) {

        $formnova_field_labels[$formnova_field->name] = $formnova_field->label;
    }
}
?>

<div class="wrap formnova-submission-wrap">
    <h1>
        Submission #<?php echo esc_html($formnova_submission->id); ?>
    </h1>

    <table class="widefat striped formnova-submission-table">
        <tbody>

            <!-- Form ID -->
            <tr>
                <th>Form ID</th>
                <td>
                    <?php echo esc_html($formnova_submission->form_id); ?>
                </td>
            </tr>

            <!-- Dynamic submitted fields -->
            <?php if (!empty($formnova_data)): ?>
                <?php foreach ($formnova_data as $formnova_key => $formnova_value): ?>
                    <tr>
                        <th>
                            <?php

                            echo esc_html(
                                $formnova_field_labels[$formnova_key] ?? $formnova_key
                            );

                            ?>
                        </th>
                        <td>

                            <?php
                            /*
                             * If field value is array/object
                             */
                            if (is_array($formnova_value)) {

                                /*
                                 * File/Image field
                                 */
                                if (isset($formnova_value['url'])) {

                                    echo '<a href="' .
                                        esc_url($formnova_value['url']) .
                                        '" target="_blank">
                                            View File
                                        </a>';

                                    echo '<br><br>';

                                    echo '<div class="formnova-file-preview">';
                                    if (
                                        preg_match(
                                            '/\.(jpg|jpeg|png|gif|webp)$/i',
                                            $formnova_value['url']
                                        )
                                    ) {

                                        echo '<div class="formnova-file-preview">';
                                        echo '<img src="' .
                                            esc_url($formnova_value['url']) .
                                            '" alt="">';
                                        echo '</div>';
                                    }
                                    echo '</div>';

                                    if (isset($formnova_value['id'])) {
                                        echo '<span class="formnova-file-id">ID: ' .
                                            absint($formnova_value['id']) .
                                            '</span>';
                                    }

                                } else {

                                    /*
                                     * Normal array (checkbox etc.)
                                     */
                                    echo esc_html(
                                        implode(', ', $formnova_value)
                                    );
                                }

                            } else {

                                /*
                                 * Normal value
                                 */
                                if (
                                    filter_var(
                                        $formnova_value,
                                        FILTER_VALIDATE_URL
                                    )
                                ) {

                                    echo '<a href="' .
                                        esc_url($formnova_value) .
                                        '" target="_blank">' .
                                        esc_html($formnova_value) .
                                        '</a>';

                                } else {

                                    echo esc_html($formnova_value);
                                }
                            }
                            ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Submitted At -->
            <tr>
                <th>Submitted At</th>
                <td>
                    <?php echo esc_html($formnova_submission->submitted_at); ?>
                </td>
            </tr>

        </tbody>
    </table>
</div>