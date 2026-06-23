<?php

if (!defined('ABSPATH')) {
    exit;
}

settings_errors(
    'formnova_settings'
);

?>

<div class="wrap">

    <h1>
        FormNova Settings
    </h1>

    <form method="post">

        <?php
        wp_nonce_field(
            'formnova_settings'
        );
        ?>

        <table class="form-table">
            <tr>
                <th>Version</th>

                <td>

                    <select
                        name="recaptcha_version">

                        <option value="none"
                            <?php selected(
                                $recaptcha_version,
                                'none'
                            ); ?>>
                            None
                        </option>

                        <option value="v2"
                            <?php selected(
                                $recaptcha_version,
                                'v2'
                            ); ?>>
                            reCAPTCHA v2
                        </option>

                        <option value="v3"
                            <?php selected(
                                $recaptcha_version,
                                'v3'
                            ); ?>>
                            reCAPTCHA v3
                        </option>

                    </select>

                </td>
            </tr>
            <tr>

                <th>
                    Google reCAPTCHA Site Key
                </th>

                <td>

                    <input type="text" class="regular-text" name="recaptcha_site_key"
                        value="<?php echo esc_attr($site_key); ?>">

                </td>

            </tr>

            <tr>

                <th>
                    Google reCAPTCHA Secret Key
                </th>

                <td>

                    <input type="text" class="regular-text" name="recaptcha_secret_key"
                        value="<?php echo esc_attr($secret_key); ?>">

                </td>

            </tr>

        </table>

        <p>

            <button type="submit" name="formnova_save_settings" class="button button-primary">

                Save Settings

            </button>

        </p>

    </form>

</div>