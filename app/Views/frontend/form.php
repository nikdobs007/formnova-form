<?php

if (!defined('ABSPATH')) {
    exit;
}

$errors = is_array($errors ?? null)
    ? $errors
    : [];

$formnova_old = is_array($formnova_old ?? null)
    ? $formnova_old
    : [];

?>

<form method="post" class="formnova-form <?php echo esc_attr($formnova_form->custom_class ?? ''); ?>"
    enctype="multipart/form-data" novalidate>

    <div class="formnova-response-top"></div>

    <?php
    wp_nonce_field(
        'formnova_submit',
        'formnova_nonce'
    );
    ?>

    <input type="hidden" name="form_id" value="<?php echo esc_attr($formnova_form->id); ?>">

    <h3>

        <?php echo esc_html(
            $formnova_form->title
        ); ?>

    </h3>

    <?php if (!empty($formnova_fields)): ?>

        <?php foreach ($formnova_fields as $formnova_field): ?>

            <div class="formnova-field">

                <p>

                    <label>

                        <?php echo esc_html(
                            $formnova_field->label
                        ); ?>

                        <?php if ($formnova_field->required): ?>

                            <span class="required">*</span>

                        <?php endif; ?>

                    </label>

                </p>


                <?php
                FormNova_Form_Renderer::render(
                    $formnova_field,
                    $formnova_old ?? []
                );
                ?>

            </div>

        <?php endforeach; ?>

        <p style="display:none;">

            <input type="text" name="formnova_hp" style="display:none" tabindex="-1" autocomplete="off">
            <input type="hidden" name="formnova_start_time" value="<?php echo esc_attr(time()); ?>">

        </p>

        <?php

        $formnova_version =
            get_option(
                'formnova_recaptcha_version',
                'none'
            );

        $formnova_form_settings = [];

        if (!empty($formnova_form->settings)) {

            $formnova_form_settings = json_decode(
                $formnova_form->settings,
                true
            );
        }
                  
        if (
            !empty(
            $formnova_form_settings['captcha_enabled']
        )
            &&
            $formnova_version == 'v2'
        ):
            ?>

            <div class="formnova-recaptcha-wrap">

                <div class="g-recaptcha" data-sitekey="<?php
                echo esc_attr(
                    get_option(
                        'formnova_recaptcha_site_key'
                    )
                );
                ?>">
                </div>

            </div>

            <div class="formnova-captcha-error"></div>

        <?php endif; ?>

        <?php
        if (
            !empty(
            $formnova_form_settings['captcha_enabled']
        )
            &&
            $formnova_version === 'v3'
        ):
            ?>

            <input type="hidden" id="formnova-recaptcha-token" name="g-recaptcha-response" value="">

            <div class="formnova-captcha-error"></div>

        <?php endif; ?>

        <p>

            <button type="submit" name="formnova_submit" class="formnova-submit-btn">

                <?php esc_html_e(
                    'Submit',
                    'formnova-form'
                ); ?>

            </button>

        </p>

        <div class="formnova-response-bottom"></div>

    <?php else: ?>

        <div class="formnova-success-box">

            <?php esc_html_e(
                'This form has no fields configured yet.',
                'formnova-form'
            ); ?>

        </div>

    <?php endif; ?>

</form>