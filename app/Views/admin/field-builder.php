<?php

if (!defined('ABSPATH')) {
    exit;
}

$formnova_field_model = new FormNova_Field_Model();

$formnova_editing = false;
$formnova_current_field = null;

$formnova_edit_field = formnova_request(
    'edit_field',
    'get'
);

if (!empty($formnova_edit_field)) {

    $formnova_editing = true;

    $formnova_current_field = $formnova_field_model->get(
        absint($formnova_edit_field)
    );
}

$formnova_options_value = '';

if (
    $formnova_editing &&
    !empty($formnova_current_field->options)
) {

    $formnova_decoded_options = json_decode(
        $formnova_current_field->options,
        true
    );

    if (is_array($formnova_decoded_options)) {

        $formnova_options_value = implode(
            "\n",
            $formnova_decoded_options
        );
    }
}

?>

<h2>

    <?php if ($formnova_editing): ?>

        <?php esc_html_e(
            'Update Field',
            'formnova-form'
        ); ?>

    <?php else: ?>

        <?php esc_html_e(
            'Add Field',
            'formnova-form'
        ); ?>

    <?php endif; ?>

</h2>

<form method="post">

    <?php if ($formnova_editing): ?>

        <?php wp_nonce_field(
            'formnova_edit_field',
            'formnova_edit_nonce'
        ); ?>

        <input type="hidden" name="field_id" value="<?php echo esc_attr(
            $formnova_current_field->id
        ); ?>">

    <?php else: ?>

        <?php wp_nonce_field(
            'formnova_add_field'
        ); ?>

    <?php endif; ?>

    <input type="hidden" name="form_id" value="<?php echo esc_attr(
        $form->id
    ); ?>">

    <table class="form-table">

        <tr>

            <th>
                <label for="type">
                    <?php esc_html_e(
                        'Field Type',
                        'formnova-form'
                    ); ?>
                </label>
            </th>

            <td>

                <select name="type" id="type">

                    <option value="text" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'text'
                    ); ?>>
                        Text
                    </option>

                    <option value="email" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'email'
                    ); ?>>
                        Email
                    </option>

                    <option value="number" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'number'
                    ); ?>>
                        Number
                    </option>

                    <option value="textarea" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'textarea'
                    ); ?>>
                        Textarea
                    </option>

                    <option value="select" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'select'
                    ); ?>>
                        Select
                    </option>

                    <option value="radio" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'radio'
                    ); ?>>
                        Radio
                    </option>

                    <option value="checkbox" <?php selected(
                        $formnova_editing
                        ? $formnova_current_field->type
                        : '',
                        'checkbox'
                    ); ?>>
                        Checkbox
                    </option>

                </select>

            </td>

        </tr>

        <tr>

            <th>

                <label for="label">

                    <?php esc_html_e(
                        'Label',
                        'formnova-form'
                    ); ?>

                </label>

            </th>

            <td>

                <input type="text" name="label" id="label" class="regular-text" required value="<?php echo esc_attr(
                    $formnova_editing
                    ? $formnova_current_field->label
                    : ''
                ); ?>">

            </td>

        </tr>

        <tr>

            <th>

                <label for="name">

                    <?php esc_html_e(
                        'Field Name',
                        'formnova-form'
                    ); ?>

                </label>

            </th>

            <td>

                <input type="text" name="name" id="name" class="regular-text" required value="<?php echo esc_attr(
                    $formnova_editing
                    ? $formnova_current_field->name
                    : ''
                ); ?>">

                <p class="description">

                    Example:
                    your_name,
                    your_email

                </p>

            </td>

        </tr>

        <tr>

            <th>

                <?php esc_html_e(
                    'Required',
                    'formnova-form'
                ); ?>

            </th>

            <td>

                <label>

                    <input type="checkbox" name="required" value="1" <?php checked(
                        $formnova_editing
                        ? $formnova_current_field->required
                        : 0,
                        1
                    ); ?>>

                    Required

                </label>

            </td>

        </tr>

        <tr>

            <th>

                <?php esc_html_e(
                    'Sort Order',
                    'formnova-form'
                ); ?>

            </th>

            <td>

                <input type="number" name="sort_order" min="0" value="<?php echo esc_attr(
                    $formnova_editing
                    ? $formnova_current_field->sort_order
                    : 0
                ); ?>">

            </td>

        </tr>

        <tr>

            <th>

                <?php esc_html_e(
                    'Options',
                    'formnova-form'
                ); ?>

            </th>

            <td>

                <textarea name="options" rows="6" cols="40"><?php echo esc_textarea(
                    $formnova_options_value
                ); ?></textarea>

                <p class="description">

                    One option per line.
                    Used only for Select,
                    Radio and Checkbox fields.

                </p>

            </td>

        </tr>

    </table>

    <p>

        <?php if ($formnova_editing): ?>

            <button type="submit" name="formnova_update_field" class="button button-primary">

                <?php esc_html_e(
                    'Update Field',
                    'formnova-form'
                ); ?>

            </button>

        <?php else: ?>

            <button type="submit" name="formnova_add_field" class="button button-primary">

                <?php esc_html_e(
                    'Add Field',
                    'formnova-form'
                ); ?>

            </button>

        <?php endif; ?>

    </p>

</form>