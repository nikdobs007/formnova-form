document.addEventListener("DOMContentLoaded", function () {

    const forms =
        document.querySelectorAll(".formnova-form");

    function clearMessage(box, delay = 4000) {
        setTimeout(() => {
            if (box) {
                box.innerHTML = "";
            }
        }, delay);
    }

    /*
     * Remove all field errors
     */
    function clearFieldErrors(form) {
        form.querySelectorAll(
            ".formnova-field-error"
        ).forEach(el => el.remove());
    }

    function showCaptchaError(form, message) {

        const box =
            form.querySelector(
                ".formnova-captcha-error"
            );

        if (box) {

            box.innerHTML =
                '<small class="formnova-field-error">' +
                message +
                '</small>';
        }
    }

    /*
     * Show field error below input
     */
    function showFieldError(field, message) {
        const oldError =
            field.parentNode.querySelector(
                ".formnova-field-error"
            );

        if (oldError) {
            oldError.remove();
        }

        const error =
            document.createElement("small");

        error.className =
            "formnova-field-error";

        error.innerText = message;

        field.parentNode.appendChild(error);
    }

    forms.forEach(form => {

        form.addEventListener(
            "submit",
            async function (e) {

                e.preventDefault();

                clearFieldErrors(form);

                const submitBtn =
                    form.querySelector(
                        'button[type="submit"]'
                    );

                const responseTop =
                    form.querySelector(
                        '.formnova-response-top'
                    );

                const responseBottom =
                    form.querySelector(
                        '.formnova-response-bottom'
                    );

                submitBtn.disabled = true;
                submitBtn.innerText =
                    "Submitting...";

                const formData =
                    new FormData(form);

                formData.append(
                    "action",
                    "formnova_submit"
                );

                formData.append(
                    "nonce",
                    formnova_ajax.nonce
                );

                /*
                * Generate reCAPTCHA v3 token
                */
                if (
                    formnova_ajax.recaptcha_version === "v3"
                    &&
                    typeof grecaptcha !== "undefined"
                    &&
                    document.getElementById(
                        "formnova-recaptcha-token"
                    )
                ) {

                    try {

                        const token =
                            await new Promise(
                                (resolve, reject) => {

                                    grecaptcha.ready(
                                        function () {

                                            grecaptcha.execute(
                                                formnova_ajax.site_key,
                                                {
                                                    action: "submit"
                                                }
                                            )
                                                .then(resolve)
                                                .catch(reject);

                                        }
                                    );

                                }
                            );

                        document.getElementById(
                            "formnova-recaptcha-token"
                        ).value = token;

                        formData.set(
                            "g-recaptcha-response",
                            token
                        );

                    } catch (error) {

                        responseTop.innerHTML =
                            `<div class="formnova-error-box">
                                Failed to verify captcha.
                            </div>`;

                        submitBtn.disabled = false;
                        submitBtn.innerText = "Submit";

                        return;
                    }
                }

                const fields =
                    form.querySelectorAll(
                        "input, textarea, select"
                    );

                /*
                 * Field validation
                 */
                for (let field of fields) {

                    const type = field.type;
                    const value =
                        field.value.trim();

                    const label =
                        field.dataset.label ||
                        field.name;

                    /*
                     * Required
                     */
                    if (
                        field.required &&
                        value === ""
                    ) {
                        showFieldError(
                            field,
                            `${label} is required.`
                        );

                        submitBtn.disabled = false;
                        submitBtn.innerText =
                            "Submit";
                        return;
                    }

                    /*
                     * Email
                     */
                    if (
                        type === "email" &&
                        value !== ""
                    ) {
                        const emailRegex =
                            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                        if (
                            !emailRegex.test(value)
                        ) {
                            showFieldError(
                                field,
                                `${label} invalid email format.`
                            );

                            submitBtn.disabled =
                                false;

                            submitBtn.innerText =
                                "Submit";

                            return;
                        }
                    }

                    /*
                     * Number
                     */
                    if (
                        type === "number" &&
                        value !== "" &&
                        isNaN(value)
                    ) {
                        showFieldError(
                            field,
                            `${label} only numeric value allowed.`
                        );

                        submitBtn.disabled = false;
                        submitBtn.innerText =
                            "Submit";

                        return;
                    }

                    /*
                     * Phone
                     */
                    if (
                        type === "tel" &&
                        value !== ""
                    ) {
                        const digitsOnly =
                            value.replace(
                                /\D/g,
                                ''
                            );

                        if (
                            digitsOnly.length < 7 ||
                            digitsOnly.length > 15
                        ) {
                            showFieldError(
                                field,
                                `${label} must be between 7 and 15 digits.`
                            );

                            submitBtn.disabled =
                                false;

                            submitBtn.innerText =
                                "Submit";

                            return;
                        }
                    }

                    /*
                     * URL
                     */
                    if (
                        type === "url" &&
                        value !== ""
                    ) {
                        try {
                            new URL(value);
                        } catch {
                            showFieldError(
                                field,
                                `${label} invalid URL.`
                            );

                            submitBtn.disabled =
                                false;

                            submitBtn.innerText =
                                "Submit";

                            return;
                        }
                    }
                }

                /*
                 * File Validation
                 */
                const fileFields =
                    form.querySelectorAll(
                        ".formnova-file-field"
                    );

                for (let field of fileFields) {

                    const allowed =
                        (field.dataset.allowed || '')
                            .split(',')
                            .map(v =>
                                v.trim().toLowerCase()
                            )
                            .filter(Boolean);

                    if (!allowed.length) {
                        continue;
                    }

                    for (let file of field.files) {

                        const ext =
                            file.name
                                .split('.')
                                .pop()
                                .toLowerCase();

                        if (
                            !allowed.includes(ext)
                        ) {
                            showFieldError(
                                field,
                                `Allowed file types: ${allowed.join(', ')}`
                            );

                            submitBtn.disabled =
                                false;

                            submitBtn.innerText =
                                "Submit";

                            return;
                        }

                        const maxSize =
                            parseInt(
                                field.dataset.maxSize || 5
                            );

                        if (
                            file.size >
                            maxSize *
                            1024 *
                            1024
                        ) {
                            showFieldError(
                                field,
                                `Maximum file size allowed is ${maxSize} MB.`
                            );

                            submitBtn.disabled =
                                false;

                            submitBtn.innerText =
                                "Submit";

                            return;
                        }
                    }
                }
                
                /*
                 * Submit via AJAX
                 */
                try {

                    const response =
                        await fetch(
                            formnova_ajax.ajax_url,
                            {
                                method: "POST",
                                body: formData
                            }
                        );

                    const text =
                        await response.text();

                    let result;

                    try {

                        result =
                            JSON.parse(text);

                        if (
                            result.success
                        ) {

                            responseBottom.innerHTML =
                                `<div class="formnova-success-box"></div>`;

                            responseBottom.querySelector(
                                ".formnova-success-box"
                            ).textContent =
                                result.data.message;

                            clearMessage(
                                responseBottom,
                                4000
                            );

                            form.reset();

                        } else {

                            if (
                                result.data &&
                                result.data.field === 'captcha'
                            ) {

                                showCaptchaError(
                                    form,
                                    result.data.message
                                );

                                return;
                            }

                            responseTop.innerHTML =
                                `<div class="formnova-error-box"></div>`;

                            responseTop.querySelector(
                                ".formnova-error-box"
                            ).textContent =
                                result.data.message;

                            clearMessage(
                                responseTop,
                                10000
                            );
                        }

                    } catch (e) {

                        responseTop.innerHTML =
                            `<div class="formnova-error-box">
                                Server Error
                            </div>`;

                        clearMessage(
                            responseTop,
                            10000
                        );
                    }

                } catch (error) {

                    responseTop.innerHTML =
                        `<div class="formnova-error-box">
                            Something went wrong.
                        </div>`;

                    clearMessage(
                        responseTop,
                        10000
                    );

                } finally {

                    submitBtn.disabled =
                        false;

                    submitBtn.innerText =
                        "Submit";
                }
            }
        );
    });
});