jQuery(function ($) {

    window.pendingFields =
        Array.isArray(window.pendingFields)
            ? window.pendingFields
            : [];

    window.pendingFields =
        (window.pendingFields || []).map(function (field) {

            return {

                id: field.id || 0,

                label: field.label || '',

                name: field.name || '',

                type: field.type || 'text',

                placeholder: field.placeholder || '',

                custom_class: field.custom_class || '',

                options: field.options || '',

                required: field.required || 0,

                allowed_file_types:
                    field.allowed_file_types || '',

                allowed_mimes:
                    field.allowed_mimes || '',

                max_file_size:
                    field.max_file_size || 5
            };

        });

    window.deletedFields =
        Array.isArray(window.deletedFields)
            ? window.deletedFields
            : [];

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

    function renderPendingFields() {
        $('#sortable-fields').html('');

        if (window.pendingFields.length === 0) {

            $('#sortable-fields').html(
                '<p id="formnova-no-fields">No fields found.</p>'
            );

            return;
        }

        $.each(window.pendingFields, function (index, field) {

            let requiredBadge = '';

            if (parseInt(field.required) === 1) {
                requiredBadge =
                    '<div class="field-meta required-badge">Required</div>';
            }

            $('#sortable-fields').append(
                '<div class="formnova-field-card" data-index="' + index + '">' +

                '<strong>' + escHtml(field.label) + '</strong><br>' +

                '<small>' + escHtml(field.type) + '</small>' +

                requiredBadge +

                '<div style="margin-top:10px;">' +

                '<button type="button" class="button button-small formnova-temp-edit" data-index="' + index + '">Edit</button> ' +

                '<button type="button" class="button button-small formnova-temp-delete" data-index="' + index + '">Delete</button>' +

                '</div>' +

                '</div>'
            );
        });
    }

    $(document).on(
        'click',
        '.formnova-copy-tag',
        function () {
            const tag = $(this).data('tag');

            navigator.clipboard.writeText(tag);

            alert('Copied: ' + tag);
        }
    );

    $(document).on(
        'click',
        '.formnova-edit-field',
        function () {

            let id = $(this).data('id');

            let field = window.pendingFields.find(function (f) {
                return parseInt(f.id) === parseInt(id);
            });

            if (!field) {
                return;
            }

            $('#temp-field-index').val(
                window.pendingFields.indexOf(field)
            );

            $('#formnova-edit-form [name=label]').val(field.label);
            $('#formnova-edit-form [name=name]').val(field.name);
            $('#formnova-edit-form [name=type]')
                .val(field.type)
                .trigger('change');
            $('#formnova-edit-form [name=placeholder]').val(field.placeholder);
            $('#formnova-edit-form [name=custom_class]').val(field.custom_class || '');
            $('#formnova-edit-form [name=options]').val(field.options);
            
            let optionsValue = field.options || '';

            if (Array.isArray(optionsValue)) {
                optionsValue = optionsValue.join('\n');
            } else {
                try {
                    let parsed = JSON.parse(optionsValue);

                    if (Array.isArray(parsed)) {
                        optionsValue = parsed.join('\n');
                    } else {
                        optionsValue = String(optionsValue).replace(/,/g, '\n');
                    }
                } catch (e) {
                    optionsValue = String(optionsValue).replace(/,/g, '\n');
                }
            }

            $('#formnova-edit-form [name=options]').val(optionsValue);

            $('#formnova-edit-form [name=required]').prop(
                'checked',
                field.required == 1
            );

            $('#formnova-edit-form [name=allowed_file_types]').val(
                field.allowed_file_types || ''
            );

            $('#formnova-edit-form [name=allowed_mimes]').val(
                field.allowed_mimes || ''
            );

            $('#formnova-edit-form [name=max_file_size]').val(
                field.max_file_size || 5
            );

            if (field.type === 'file') {
                $('#edit-file-settings').show();
            } else {
                $('#edit-file-settings').hide();
            }

            $('#formnova-edit-modal').show();
        }
    );

    function resetFieldBuilder() {
        $('#formnova-field-label').val('');
        $('#formnova-field-name').val('');
        $('#formnova-field-placeholder').val('');
        $('#formnova-field-options').val('');
        $('#formnova-field-required')
            .prop('checked', false);
        $('#formnova-field-type')
            .prop('selectedIndex', 0);
        $('#allowed_file_types').val('');
        $('#allowed_mimes').val('');
        $('#max_file_size').val('5');

        $('#formnova-file-settings').hide();
        $('#formnova-file-settings-mimes').hide();
        $('#formnova-file-settings-size').hide();
    }

    $(document).on(
        'keyup',
        '#formnova-field-label',
        function () {

            let slug = $(this)
                .val()
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            $('#formnova-field-name').val(slug);
        }
    );

    $('.nav-tab').on('click', function (e) {
        e.preventDefault();

        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.formnova-tab').hide();
        $('#' + $(this).data('tab')).show();
    });


    $(document).on('click', '#copy-shortcode', function () {
        let input = document.getElementById('formnova-shortcode');

        input.select();
        document.execCommand('copy');

        alert('Shortcode copied');
    });


    if ($('#sortable-fields').length) {
        $('#sortable-fields').sortable({
            placeholder: 'formnova-sort-placeholder',

            update: function () {

                let order = [];

                $('.formnova-field-card').each(function () {

                    let index = $(this).data('index');

                    if (
                        typeof window.pendingFields[index] !== 'undefined'
                    ) {

                        order.push(
                            window.pendingFields[index]
                        );
                    }

                });

                window.pendingFields = order;

                renderPendingFields();

                $.post(
                    formnovaAdmin.ajax_url,
                    {
                        action: 'formnova_sort_fields',
                        nonce: formnovaAdmin.nonce,
                        order: order
                    }
                );
            }
        });
    }


    $(document).on('change', '#formnova-field-type', function () {

        let type = $(this).val();

        if (type === 'file') {

            $('#formnova-file-settings').show();
            $('#formnova-file-settings-mimes').show();
            $('#formnova-file-settings-size').show();

        } else {

            $('#formnova-file-settings').hide();
            $('#formnova-file-settings-mimes').hide();
            $('#formnova-file-settings-size').hide();
        }

        if (
            type === 'select' ||
            type === 'radio' ||
            type === 'checkbox'
        ) {

            $('#formnova-options-row').show();

        } else {

            $('#formnova-options-row').hide();
        }

    }).trigger('change');


    $(document).on(
        'click',
        '.formnova-temp-delete',
        function () {

            let index =
                $(this).data('index');

            let field =
                window.pendingFields[index];

            if (
                !confirm(
                    'Are you sure you want to delete "' +
                    field.label +
                    '" field?'
                )
            ) {
                return;
            }

            if (
                field.id &&
                !String(field.id).startsWith('temp_')
            ) {

                window.deletedFields.push(
                    parseInt(field.id)
                );
            }

            window.pendingFields.splice(
                index,
                1
            );

            renderPendingFields();

            $('#formnova-deleted-fields').val(
                JSON.stringify(
                    window.deletedFields
                )
            );
        }
    );

    $(document).on('submit', '#formnova-edit-form', function (e) {

        e.preventDefault();

        let index = $('#temp-field-index').val();

        let type = $('#formnova-edit-form [name=type]').val();

        if (type === 'file') {

            let fileTypes = $('#formnova-edit-form [name=allowed_file_types]').val();
            let mimes = $('#formnova-edit-form [name=allowed_mimes]').val();

            if (!fileTypes || fileTypes.trim() === '') {
                alert('Allowed file types required');
                return;
            }

            if (!mimes || mimes.trim() === '') {
                alert('Allowed mime types required');
                return;
            }
        }

        if (
            type === 'select' ||
            type === 'radio' ||
            type === 'checkbox'
        ) {

            $('#edit-options-row').show();

        } else {

            $('#edit-options-row').hide();
        }

        if (index === '') {
            return;
        }

        let fieldName =
            $('#formnova-edit-form [name=name]')
                .val()
                .trim()
                .toLowerCase();

        let duplicateFound = false;

        window.pendingFields.forEach(function (field, fieldIndex) {

            if (
                fieldIndex != index &&
                field.name &&
                field.name.toLowerCase() === fieldName
            ) {
                duplicateFound = true;
            }

        });

        if (duplicateFound) {

            let errorBox = $('#formnova-field-name-error');

            if (!errorBox.length) {

                $('#formnova-edit-form [name=name]')
                    .after(
                        '<div id="formnova-field-name-error" style="color:#d63638;margin-top:5px;font-size:13px;">Field name already exists.</div>'
                    );

            } else {

                errorBox.show();

            }

            return;
        }

        $('#formnova-field-name-error').hide();

        window.pendingFields[index] = {

            ...window.pendingFields[index],

            label:
                $('#formnova-edit-form [name=label]').val(),

            name:
                $('#formnova-edit-form [name=name]').val(),

            type:
                $('#formnova-edit-form [name=type]').val(),

            placeholder:
                $('#formnova-edit-form [name=placeholder]').val(),

            custom_class:
                $('#formnova-edit-form [name=custom_class]').val(),

            options:
                $('#formnova-edit-form [name=options]').val(),

            required:
                $('#formnova-edit-form [name=required]').is(':checked')
                    ? 1
                    : 0,

            allowed_file_types:
                $('#formnova-edit-form [name=allowed_file_types]').val(),

            allowed_mimes:
                $('#formnova-edit-form [name=allowed_mimes]').val(),

            max_file_size:
                $('#formnova-edit-form [name=max_file_size]').val()
        };

        $('#formnova-pending-fields').val(
            JSON.stringify(window.pendingFields)
        );

        renderPendingFields();

        $('#formnova-edit-modal').hide();

    });

    $(document).on(
        'click',
        '.formnova-temp-edit',
        function () {

            let index = $(this).data('index');
            let tempIndex = $('#temp-field-index').val();
            let field = pendingFields[index];

            $('#temp-field-index').val(index);

            $('#formnova-edit-form [name=label]')
                .val(field.label);

            $('#formnova-edit-form [name=name]')
                .val(field.name);

            $('#formnova-edit-form [name=type]')
                .val(field.type).trigger('change');

            $('#formnova-edit-form [name=placeholder]')
                .val(field.placeholder);

            $('#formnova-edit-form [name=custom_class]')
                .val(field.custom_class || '');

            let optionsValue = field.options || '';

            if (Array.isArray(optionsValue)) {

                optionsValue = optionsValue.join('\n');

            } else {

                optionsValue = String(optionsValue)
                    .replace(/,/g, '\n');
            }

            $('#formnova-edit-form [name=options]')
                .val(optionsValue);

            $('#formnova-edit-form [name=required]')
                .prop(
                    'checked',
                    field.required == 1
                );

            $('#formnova-edit-form [name=allowed_file_types]')
                .val(field.allowed_file_types || '');

            $('#formnova-edit-form [name=allowed_mimes]')
                .val(field.allowed_mimes || '');

            $('#formnova-edit-form [name=max_file_size]')
                .val(field.max_file_size || 5);

            $('#formnova-edit-modal').show();
        }
    );

    $(document).on('click', '.formnova-delete-field', function () {

        let button = $(this);

        let label = button.data('label');

        if (
            !confirm(
                'Are you sure you want to delete "' +
                label +
                '" field?'
            )
        ) {
            return;
        }

        let fieldId = button.data('id');

        // find index in pendingFields
        let indexToRemove = -1;

        window.pendingFields.forEach(function (field, index) {
            if (parseInt(field.id) === parseInt(fieldId)) {
                indexToRemove = index;
            }
        });

        if (indexToRemove !== -1) {
            window.pendingFields.splice(indexToRemove, 1);
        }

        // mark deleted only if already saved field
        if (fieldId && fieldId !== 0) {
            window.deletedFields.push(parseInt(fieldId));
        }

        // update hidden inputs
        $('#formnova-pending-fields').val(
            JSON.stringify(window.pendingFields)
        );

        $('#formnova-deleted-fields').val(
            JSON.stringify(window.deletedFields)
        );

        // remove UI
        button.closest('.formnova-field-card').remove();

        console.log('pendingFields:', window.pendingFields);
        console.log('deletedFields:', window.deletedFields);
    });


    $(document).on('click', '#formnova-close-modal', function () {
        $('#formnova-edit-modal').hide();
    });

    $(document).on(
        'change',
        '#formnova-edit-form [name=type]',
        function () {

            let type = $(this).val();

            // File Settings
            if (type === 'file') {

                $('#edit-file-settings').show();

            } else {

                $('#edit-file-settings').hide();
            }

            // Options Row
            if (
                type === 'select' ||
                type === 'radio' ||
                type === 'checkbox'
            ) {

                $('#edit-options-row').show();

            } else {

                $('#edit-options-row').hide();
            }

        }
    );

    $(document).on('click', '#formnova-save-form-btn', function () {

        var button = $(this);

        var title = $('#formnova-form-title').val().trim();

        if (!title) {
            alert('Form title is required');
            return;
        }

        $('#formnova-pending-fields').val(
            JSON.stringify(window.pendingFields || [])
        );

        button.prop('disabled', true);

        $.post(
            formnovaAdmin.ajax_url,
            {
                action: 'formnova_save_form',
                nonce: formnovaAdmin.nonce,

                form_id: $('#formnova-current-form-id').val(),

                title: title,

                custom_class: $('#formnova-form-class').val(),

                fields: $('#formnova-pending-fields').val(),

                deleted_fields:
                    JSON.stringify(
                        window.deletedFields
                    ),

                admin_email: $('[name="admin_email"]').val(),
                cc_email: $('[name="cc_email"]').val(),

                subject_admin: $('[name="subject_admin"]').val(),
                subject_user: $('[name="subject_user"]').val(),

                message_admin: $('[name="message_admin"]').val(),
                message_user: $('[name="message_user"]').val(),

                send_user_email:
                    $('[name="send_user_email"]').is(':checked') ? 1 : 0,

                captcha_enabled:
                    $('[name="captcha_enabled"]').is(':checked') ? 1 : 0,
            }
        )
            .done(function (response) {

                button.prop('disabled', false);

                console.log('Save Response:', response);

                if (response.success) {

                    $('#formnova-save-error').hide();

                    $('#formnova-save-notice')
                        .html(
                            '<p><strong>Form saved successfully.</strong></p>'
                        )
                        .stop(true, true)
                        .fadeIn();

                    if (response.data.form_id) {

                        $('#formnova-current-form-id').val(
                            response.data.form_id
                        );

                        if (
                            !window.location.href.includes('&id=')
                        ) {

                            setTimeout(function () {

                                window.location =
                                    formnovaAdmin.edit_url +
                                    '&id=' +
                                    response.data.form_id +
                                    '&_wpnonce=' +
                                    formnovaAdmin.edit_nonce;

                            }, 1500);
                        }
                    }

                    setTimeout(function () {
                        $('#formnova-save-notice').fadeOut();
                    }, 5000);

                } else {

                    $('#formnova-save-notice').hide();

                    let errorMessage =
                        response?.data?.message ||
                        'Unable to save form.';

                    $('#formnova-save-error')
                        .html(
                            '<p><strong>' +
                            errorMessage +
                            '</strong></p>'
                        )
                        .stop(true, true)
                        .fadeIn();

                    $('html, body').animate({
                        scrollTop:
                            $('#formnova-save-error').offset().top - 100
                    }, 300);
                }

            })
            .fail(function (xhr) {

                $('#formnova-save-notice').hide();

                $('#formnova-save-error')
                    .html('<p><strong>Server Error. Check browser console.</strong></p>')
                    .stop(true, true)
                    .fadeIn();

            });

    });

    $(document).on('click', '#formnova-add-field-btn', function () {

        var field = {

            label: $('#formnova-field-label').val(),

            name: $('#formnova-field-name').val(),

            type: $('#formnova-field-type').val(),

            placeholder: $('#formnova-field-placeholder').val(),

            custom_class: $('#formnova-field-class').val(),

            options: $('#formnova-field-options').val(),

            required:
                $('#formnova-field-required').is(':checked')
                    ? 1
                    : 0,

            allowed_file_types:
                $('#allowed_file_types').val(),

            allowed_mimes:
                $('#allowed_mimes').val(),

            max_file_size:
                $('#max_file_size').val()
        };

        if (field.label === '') {
            alert('Field Label is required');
            $('#formnova-field-label').focus();
            return;
        }

        if (field.name === '') {
            alert('Field Name is required');
            $('#formnova-field-name').focus();
            return;
        }

        if (field.type === '') {
            alert('Field Type is required');
            return;
        }

        let duplicate = false;

        $.each(window.pendingFields, function (i, existing) {
            if (
                existing.name.toLowerCase() ===
                field.name.toLowerCase()
            ) {
                duplicate = true;
                return false;
            }
        });

        if (duplicate) {
            alert(
                'Field name already exists'
            );
            return;
        }

        const reserved = [

            'id',
            'form_id',
            'action',
            'submit',
            'nonce',
            'post_id'
        ];

        if (
            reserved.includes(
                field.name.toLowerCase()
            )
        ) {
            alert(
                'Reserved field name'
            );
            return;
        }

        if (
            field.type === 'select' ||
            field.type === 'radio' ||
            field.type === 'checkbox'
        ) {

            if (
                field.options.trim() === ''
            ) {
                alert(
                    'Options required for '
                    + field.type
                );
                return;
            }
        }


        if (field.type === 'file') {

            if (!field.allowed_file_types || field.allowed_file_types.trim() === '') {
                alert('Allowed file types required.');
                $('#allowed_file_types').focus();
                return;
            }

            if (!field.allowed_mimes || field.allowed_mimes.trim() === '') {
                alert('Allowed mime types required.');
                $('#allowed_mimes').focus();
                return;
            }

            if (!field.max_file_size || parseInt(field.max_file_size) <= 0) {
                alert('Maximum file size must be greater than 0.');
                $('#max_file_size').focus();
                return;
            }
        }

        window.pendingFields.push({

            id: 0,

            temp_id:
                'temp_' +
                Date.now() +
                '_' +
                Math.floor(Math.random() * 100000),

            label: field.label,

            name: field.name,

            type: field.type,

            placeholder: field.placeholder,

            custom_class: field.custom_class,

            options: field.options,

            required: field.required,

            allowed_file_types:
                field.allowed_file_types,

            allowed_mimes:
                field.allowed_mimes,

            max_file_size:
                field.max_file_size
        });

        renderPendingFields();

        $('#formnova-pending-fields').val(
            JSON.stringify(window.pendingFields)
        );

        resetFieldBuilder();

    });
});    