<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Form_Renderer
{
    public static function render($field, $old = [])
    {
        switch ($field->type) {

            case 'text':
                self::text($field, $old);
                break;

            case 'email':
                self::email($field, $old);
                break;

            case 'number':
                self::number($field, $old);
                break;

            case 'textarea':
                self::textarea($field, $old);
                break;

            case 'select':
                self::select($field, $old);
                break;

            case 'radio':
                self::radio($field, $old);
                break;

            case 'checkbox':
                self::checkbox($field, $old);
                break;
            case 'tel':
                self::tel($field, $old);
                break;

            case 'date':
                self::date($field, $old);
                break;

            case 'url':
                self::url($field, $old);
                break;

            case 'file':
                self::file($field, $old);
                break;
            default:
                self::text($field, $old);
                break;    
        }
    }
    
    private static function get_class($field)
    {
        return !empty($field->custom_class)
            ? esc_attr($field->custom_class)
            : '';
    }

    private static function text($field, $old)
    {
        ?>
        <input
            type="text"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }

    private static function email($field, $old)
    {
        ?>
        <input
            type="email"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }

    private static function number($field, $old)
    {
        ?>
        <input
            type="number"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }

    private static function textarea($field, $old)
    {
        ?>
        <textarea
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>><?php
                echo esc_textarea($old[$field->name] ?? '');
            ?></textarea>
        <?php
    }

    private static function select($field, $old)
    {
        $options = self::get_options($field);

        ?>

        <select
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>

            <option value="">
                <?php esc_html_e('Select Option', 'formnova-form'); ?>
            </option>

            <?php foreach ($options as $option) : ?>

                <option
                    value="<?php echo esc_attr($option); ?>"
                    <?php selected(
                        $old[$field->name] ?? '',
                        $option
                    ); ?>>

                    <?php echo esc_html($option); ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php
    }

    private static function radio($field, $old)
    {
        $options = self::get_options($field);

        $selected =
            $old[$field->name]
            ?? '';

        $required =
            !empty($field->required)
            ? 'required'
            : '';

        $first = true;

        foreach ($options as $option) :

            ?>

            <label class="formnova-option <?php echo esc_attr(self::get_class($field)); ?>">

                <input
                    type="radio"
                    name="<?php echo esc_attr($field->name); ?>"
                    data-label="<?php echo esc_attr($field->label); ?>"
                    value="<?php echo esc_attr($option); ?>"
                    <?php checked($selected, $option); ?>
                    <?php echo $first ? esc_attr($required) : ''; ?>>
                <?php echo esc_html($option); ?>
            </label>
            <?php
            $first = false;
        endforeach;
    }

    private static function checkbox($field, $old)
    {
        $options = self::get_options($field);

       $selected =
           $old[$field->name]
            ?? [];

       if (!is_array($selected)) {
           $selected = [];
        }

       $required =
           !empty($field->required)
           ? 'required'
            : '';

        $first = true;

        foreach ($options as $option) :

            ?>

            <label class="formnova-option <?php echo esc_attr(self::get_class($field)); ?>">

               <input
                   type="checkbox"
                   name="<?php echo esc_attr($field->name); ?>[]"
                   data-label="<?php echo esc_attr($field->label); ?>"
                   value="<?php echo esc_attr($option); ?>"
                   <?php checked(
                       in_array(
                           $option,
                           $selected,
                           true
                       )
                   ); ?>
                    <?php echo $first ? esc_attr($required) : ''; ?>>
                <?php echo esc_html($option); ?>
            </label>
            <?php
            $first = false;
       endforeach;
    }
    
    private static function get_options($field)
    {
        $options = json_decode(
            (string)$field->options,
            true
        );

        if (!is_array($options)) {

            $options = array_filter(
                array_map(
                    'trim',
                    preg_split(
                        '/[\r\n,]+/',
                        (string) $field->options
                    )
                )
            );
        }

        return $options;
    }

    private static function tel($field, $old)
    {
        ?>
        <input
            type="tel"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }
    
    private static function date($field, $old)
    {
        ?>
        <input
            type="date"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }
    
    private static function url($field, $old)
    {
        ?>
        <input
            type="url"
            name="<?php echo esc_attr($field->name); ?>"
            class="<?php echo esc_attr(self::get_class($field)); ?>"
            value="<?php echo esc_attr($old[$field->name] ?? ''); ?>"
            placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>"
            data-label="<?php echo esc_attr($field->label); ?>"
            <?php echo !empty($field->required) ? 'required' : ''; ?>>
        <?php
    }

    private static function file($field, $old)
    {
        $meta_model = new FormNova_FieldMetaModel();

        $meta = $meta_model->get(
            $field->id
        );

        $allowed_file_types =
            $meta->allowed_file_types ?? '';

        $max_file_size =
            $meta->max_file_size ?? 5;

        $accept = '';

        if (!empty($allowed_file_types)) {

            $types = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        strtolower($allowed_file_types)
                    )
                )
            );

            $accept = '.' . implode(',.', $types);
        }

    ?>
    <input
        type="file"
        name="<?php echo esc_attr($field->name); ?>"
        class="<?php echo esc_attr(
            self::get_class($field) . ' formnova-file-field'
        ); ?>"
        data-label="<?php echo esc_attr($field->label); ?>"
        accept="<?php echo esc_attr($accept); ?>"
        data-allowed="<?php echo esc_attr($allowed_file_types); ?>"
        data-max-size="<?php echo absint($max_file_size); ?>"
        <?php echo !empty($field->required) ? 'required' : ''; ?>
    >
    <?php if (!empty($allowed_file_types)): ?>
    <small>
        Allowed Types:
        <?php echo esc_html($allowed_file_types); ?>
    </small>
    <?php endif; ?>

    <?php if (!empty($meta->allowed_mimes)): ?>
        <small>
            Allowed MIME:
            <?php echo esc_html($meta->allowed_mimes); ?>
        </small>
    <?php endif; ?>

    <small>
        Max File Size:
        <?php echo esc_html($max_file_size); ?> MB
    </small>
    <?php
    }
}