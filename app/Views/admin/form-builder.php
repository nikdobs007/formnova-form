<?php
if (!defined('ABSPATH')) {
    exit;
}

$formnova_form_id = !empty($formnova_form->id) ? absint($formnova_form->id) : 0;
?>

<div class="wrap" id="formnova-form-editor">
    <h1 class="wp-heading-inline">
        <?php
        echo !empty($formnova_form->id)
            ? 'Edit Form'
            : 'Add New Form';
        ?>
    </h1>

    <div id="formnova-save-notice" class="notice notice-success inline" style="display:none;margin:15px 0;">
    </div>

    <div id="formnova-save-error" class="notice notice-error inline" style="display:none;margin:15px 0;">
    </div>

    <?php if (!empty($formnova_form->id)): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=formnova-add')); ?>" class="page-title-action">
            Add New Form
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <form id="formnova-builder-form">
        <?php wp_nonce_field('formnova_create_form'); ?>
        <input type="hidden" id="formnova-current-form-id" value="<?php echo esc_attr(absint($formnova_form_id)); ?>">
        <input type="hidden" id="formnova-pending-fields" name="fields" value="">
        <input type="hidden" id="formnova-deleted-fields" name="deleted_fields" value="">
        <?php if ($formnova_form_id): ?>
            <input type="hidden" name="form_id" value="<?php echo esc_attr(absint($formnova_form_id)); ?>">
        <?php endif; ?>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2 wp-clearfix">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" id="formnova-form-title" name="title"
                                value="<?php echo isset($formnova_form->title) ? esc_attr($formnova_form->title) : ''; ?>"
                                required>
                        </div>
                        <div class="inside">
                            <p class="description">
                                <?php if ($formnova_form_id): ?>
                                    <label>Use the shortcode below in your post, page, or text widget to embed the
                                        content.</label>
                                    <span class="formnova-shortcode wp-ui-highlight">
                                        <input type="text" id="formnova-shortcode" readonly onclick="this.select();"
                                            class="widefat"
                                            value='[formnova id="<?php echo esc_attr(absint($formnova_form_id)); ?>" title="<?php echo esc_attr($formnova_form->title); ?>"]'>
                                    </span>
                                    <!-- <p>
                                    <button type="button" class="button" id="copy-shortcode">
                                        Copy Shortcode
                                    </button>
                                </p> -->
                                <?php else: ?>

                                <p>
                                    Save form first to generate shortcode.
                                </p>

                            <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-1" class="postbox-container">
                    <section id="submitdiv" class="postbox">
                        <h2>Status</h2>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="minor-publishing-actions">
                                </div>
                                <div id="misc-publishing-actions">
                                </div>
                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <button type="button" id="formnova-save-form-btn" class="button button-primary">
                                            Save Form
                                        </button>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <div id="formnova-form-editor">
                        <h2 class="nav-tab-wrapper">
                            <a href="#" class="nav-tab nav-tab-active" data-tab="form-tab">
                                Form
                            </a>
                            <a href="#" class="nav-tab" data-tab="settings-tab">
                                Settings
                            </a>
                            <a href="#" class="nav-tab" data-tab="preview-tab">
                                Preview
                            </a>
                        </h2>

                        <div id="form-tab" class="formnova-tab">
                            <p>
                                <label>Form CSS Class</label>
                                <input type="text" id="formnova-form-class" class="widefat"
                                    value="<?php echo esc_attr($formnova_form->custom_class ?? ''); ?>"
                                    placeholder="container row g-3">
                            </p>
                            <div class="formnova-builder">
                                <div class="formnova-left">
                                    <div class="postbox">
                                        <div class="inside">
                                            <h3>Add Field</h3>
                                            <div id="formnova-add-field">
                                                <input type="hidden" name="form_id"
                                                    value="<?php echo esc_attr(absint($formnova_form_id)); ?>">
                                                <p>
                                                    <label>
                                                        Label
                                                    </label>
                                                    <input type="text" id="formnova-field-label" class="widefat">
                                                </p>
                                                <p>
                                                    <label>
                                                        Name
                                                    </label>
                                                    <input type="text" id="formnova-field-name" class="widefat">
                                                </p>
                                                <p>
                                                    <label>Type</label>
                                                    <select id="formnova-field-type" class="widefat">
                                                        <option value="text">Text</option>
                                                        <option value="email">Email</option>
                                                        <option value="number">Number</option>
                                                        <option value="tel">Phone</option>
                                                        <option value="textarea">Textarea</option>
                                                        <option value="select">Select</option>
                                                        <option value="radio">Radio</option>
                                                        <option value="checkbox">Checkbox</option>
                                                        <option value="date">Date</option>
                                                        <option value="url">URL</option>
                                                        <option value="file">File</option>
                                                    </select>
                                                </p>
                                                <div id="formnova-file-settings" style="display:none;">
                                                    <label>Allowed File Types *</label>
                                                    <input type="text" id="allowed_file_types" name="allowed_file_types"
                                                        class="widefat" placeholder="jpg,jpeg,png,pdf,doc,docx">
                                                </div>
                                                <div id="formnova-file-settings-mimes" style="display:none;">
                                                    <label>Allowed Mime Types *</label>
                                                    <input type="text" id="allowed_mimes" class="widefat"
                                                        placeholder="image/jpeg,image/png,application/pdf">
                                                </div>
                                                <div id="formnova-file-settings-size" style="display:none;">
                                                    <label>Max File Size (MB) *</label><br>
                                                    <input type="number" id="max_file_size" class="widefat"
                                                        name="max_file_size" value="5" min="1" max="5" readonly>
                                                </div>
                                                <p>
                                                    <label>Placeholder</label>
                                                    <input type="text" class="widefat" id="formnova-field-placeholder"
                                                        placeholder="Placeholder">
                                                </p>
                                                <p>
                                                    <label>CSS Class</label>

                                                    <input type="text" id="formnova-field-class" class="widefat"
                                                        placeholder="form-control col-md-6">
                                                </p>
                                                <div id="formnova-options-row" style="display:none;">

                                                    <label>Options</label>

                                                    <textarea id="formnova-field-options" rows="5"></textarea>

                                                </div>
                                                <p>
                                                    <label>
                                                        <input type="checkbox" id="formnova-field-required"
                                                            class="widefat">
                                                        Required
                                                    </label>
                                                </p>
                                                <p>
                                                    <button type="button" id="formnova-add-field-btn"
                                                        class="button button-primary">
                                                        Add Field
                                                    </button>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="formnova-right">

                                    <div id="sortable-fields">

                                        <?php if (!empty($formnova_fields)): ?>

                                            <?php foreach ($formnova_fields as $formnova_field): ?>

                                                <div class="formnova-field-card"
                                                    data-id="<?php echo absint($formnova_field->id); ?>">

                                                    <strong>
                                                        <?php echo esc_html($formnova_field->label); ?>
                                                    </strong>

                                                    <small>
                                                        <?php echo esc_html($formnova_field->type); ?>
                                                    </small>

                                                    <?php if ($formnova_field->required == 1): ?>
                                                        <div class="field-meta required-badge">
                                                            Required
                                                        </div>
                                                    <?php endif; ?>

                                                    <span>

                                                        <button type="button" class="button button-small formnova-edit-field"
                                                            data-id="<?php echo absint($formnova_field->id); ?>">

                                                            Edit

                                                        </button>

                                                        <button type="button" class="button button-small formnova-delete-field"
                                                            data-id="<?php echo absint($formnova_field->id); ?>"
                                                            data-label="<?php echo esc_attr($formnova_field->label); ?>">

                                                            Delete

                                                        </button>

                                                    </span>

                                                </div>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <p id="formnova-no-fields">
                                                No fields found.
                                            </p>

                                        <?php endif; ?>

                                    </div>
                                    <script>
                                        window.pendingFields =
                                            <?php echo wp_json_encode(
                                                array_values($formnova_fields ?? [])
                                            ); ?>;

                                        if (
                                            !Array.isArray(
                                                window.pendingFields
                                            )
                                        ) {

                                            window.pendingFields = [];
                                        }
                                        window.deletedFields = [];
                                    </script>
                                </div>

                            </div>

                        </div>

                        <div id="settings-tab" class="formnova-tab" style="display:none;">

                            <h2>
                                <?php
                                esc_html_e(
                                    'Security',
                                    'formnova-form'
                                );
                                ?>

                            </h2>

                            <p>
                                <label>
                                    <input type="checkbox" name="captcha_enabled" value="1" <?php checked(
                                        $formnova_form_settings['captcha_enabled'] ?? 0,
                                        1
                                    ); ?>>
                                    Enable Google reCAPTCHA
                                </label>
                            </p>

                            <?php
                            $formnova_form_settings = [];
                            if (!empty($formnova_form->settings)) {
                                $formnova_form_settings = json_decode(
                                    $formnova_form->settings,
                                    true
                                );

                                if (!is_array($formnova_form_settings)) {
                                    $formnova_form_settings = [];
                                }
                            }
                            ?>
                            <table class="form-table">
                                <tr>
                                    <th>Admin Email(s)</th>
                                    <td>
                                        <input type="text" name="admin_email" class="regular-text" required
                                            placeholder="admin@example.com, manager@example.com" value="<?php echo esc_attr(
                                                !empty($formnova_form_settings['admin_email'])
                                                ? implode(',', (array) $formnova_form_settings['admin_email'])
                                                : ''
                                            ); ?>">

                                        <p class="description">
                                            Multiple emails comma separated.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>CC Email</th>
                                    <td>
                                        <input type="text" name="cc_email" class="regular-text" required
                                            placeholder="admin@example.com, manager@example.com" value="<?php echo esc_attr(
                                                !empty($formnova_form_settings['cc_email'])
                                                ? implode(',', (array) $formnova_form_settings['cc_email'])
                                                : ''
                                            ); ?>">
                                        <p class="description">
                                            Multiple emails comma separated.
                                        </p>
                                </tr>
                                <tr>
                                    <th>Admin Subject</th>
                                    <td>
                                        <input type="text" name="subject_admin" class="regular-text"
                                            value="<?php echo esc_attr($formnova_form_settings['subject_admin'] ?? 'New Submission'); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>User Subject</th>
                                    <td>
                                        <input type="text" name="subject_user" class="regular-text"
                                            value="<?php echo esc_attr($formnova_form_settings['subject_user'] ?? 'Thank you'); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Available Mail Tags</th>
                                    <td>
                                        <div class="formnova-mail-tags">
                                            <?php if (!empty($formnova_fields)): ?>
                                                <?php foreach ($formnova_fields as $formnova_field): ?>
                                                    <button type="button" class="button formnova-copy-tag"
                                                        data-tag="{<?php echo esc_attr($formnova_field->name); ?>}">
                                                        {
                                                        <?php echo esc_html($formnova_field->name); ?>
                                                        }
                                                    </button>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <p class="description">
                                            Click any tag to copy and paste inside Admin Message.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Admin Message</th>
                                    <td>
                                        <textarea name="message_admin" rows="6" class="large-text"><?php echo esc_textarea(
                                            $formnova_form_settings['message_admin']
                                            ?? 'New submission: {all_fields}'
                                        ); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User Message</th>
                                    <td>
                                        <textarea name="message_user" rows="6" class="large-text"><?php echo esc_textarea(
                                            $formnova_form_settings['message_user']
                                            ?? 'Thanks {name}'
                                        ); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Send User Email</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="send_user_email" value="1" <?php checked(
                                                $formnova_form_settings['send_user_email'] ?? 0,
                                                1
                                            ); ?>>
                                            Enable
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="preview-tab" class="formnova-tab" style="display:none;">
                            <?php if (!empty($formnova_fields)): ?>
                                <table class="widefat">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($formnova_fields as $formnova_field): ?>
                                            <tr>
                                                <td>
                                                    <?php echo esc_html(
                                                        $formnova_field->label
                                                    ); ?>
                                                </td>
                                                <td>
                                                    <?php echo esc_html(
                                                        $formnova_field->type
                                                    ); ?>
                                                </td>
                                            </tr>
                                            <?php if ($formnova_field->type === 'file'): ?>

                                                <tr class="formnova-file-meta">

                                                    <td>Extensions</td>
                                                    <td> <?php
                                                    echo esc_html(
                                                        $formnova_field->allowed_file_types
                                                    );
                                                    ?>
                                                    </td>
                                                </tr>
                                                <tr class="formnova-file-meta">

                                                    <td>Max Size</td>
                                                    <td> <?php
                                                    echo absint(
                                                        $formnova_field->max_file_size
                                                    );
                                                    ?> MB
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>
                                    No fields available.
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- <div class="formnova-save-bar">

                            <button type="button" id="formnova-save-form-btn" class="button button-primary">
                                Save Form
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </form>

    <div id="formnova-edit-modal"
        style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); z-index:9999;">

        <div style="width:700px; background:#fff; margin:50px auto; padding:20px;">
            <h2>Edit Field</h2>

            <form id="formnova-edit-form">

                <input type="hidden" name="field_id">
                <input type="hidden" id="temp-field-index" value="">

                <!-- TWO COLUMN WRAPPER -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">

                    <div>
                        <label>Label</label>
                        <input type="text" name="label" class="widefat">
                    </div>

                    <div>
                        <label>Name</label>
                        <input type="text" name="name" class="widefat">
                    </div>

                    <div>
                        <label>Type</label>
                        <select name="type" class="widefat">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="number">Number</option>
                            <option value="tel">Phone</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="date">Date</option>
                            <option value="url">URL</option>
                            <option value="file">File</option>
                        </select>
                    </div>

                    <div>
                        <label>Placeholder</label>
                        <input type="text" name="placeholder" class="widefat">
                    </div>
                    <div>
                        <label>CSS Class</label>
                        <input type="text" name="custom_class" class="widefat">
                    </div>
                    <div id="edit-options-row" style="display:none;">

                        <label>Options</label>

                        <textarea name="options" rows="5"></textarea>

                    </div>

                </div>

                <!-- FILE SETTINGS (FULL WIDTH) -->
                <div id="edit-file-settings" style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">

                    <div>
                        <label>Allowed File Types *</label>
                        <input type="text" name="allowed_file_types" class="widefat"
                            placeholder="jpg,jpeg,png,pdf,doc,docx">
                    </div>

                    <div>
                        <label>Allowed Mime Types *</label>
                        <input type="text" name="allowed_mimes" class="widefat"
                            placeholder="image/jpeg,image/png,application/pdf">
                    </div>

                    <div>
                        <label>Max File Size (MB) *</label>
                        <input type="number" name="max_file_size" class="widefat" value="5" min="1" max="5" readonly>
                    </div>

                </div>

                <p>
                    <label>
                        <input type="checkbox" name="required" value="1">
                        Required
                    </label>
                </p>

                <div>
                    <button type="submit" class="button button-primary">Save Changes</button>
                    <button type="button" id="formnova-close-modal" class="button button-outline">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>