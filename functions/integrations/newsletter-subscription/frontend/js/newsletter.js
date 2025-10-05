(function ($) {
  "use strict";

  class NewsletterSubscription {
    constructor() {
      this.forms = [];
      this.translations = newsletter_ajax.translations || {};
    }

    // Публичный метод для инициализации
    init(container) {
      this.container = container ? $(container) : $(document);
      this.findForms();
      this.bindEvents();
      return this;
    }

    findForms() {
      this.forms = [];
      this.container
        .find("form.newsletter-subscription-form")
        .each((index, form) => {
          const formObj = {
            element: $(form),
            id: $(form).attr("id"),
            emailField: $(form).find('input[type="email"]'),
            mailingConsentCheckbox: $(form).find(
              'input[name="soglasie-na-rassilku"]'
            ),
            dataProcessingCheckbox: $(form).find(
              'input[name="soglasie-na-obrabotku"]'
            ),
            privacyPolicyCheckbox: $(form).find(
              'input[name="privacy-policy-read"]'
            ),
            nameField: $(form).find('input[name="text-name"]'),
            surnameField: $(form).find('input[name="text-surname"]'),
            phoneField: $(form).find('input[type="tel"]'),
            submitButton: $(form).find('button[type="submit"]'),
            hasOnlyEmail: this.hasOnlyEmailField(form),
          };

          this.forms.push(formObj);
        });
    }

    hasOnlyEmailField(form) {
      const inputs = $(form).find(
        'input:not([type="hidden"]):not([type="submit"])'
      );
      return inputs.length === 4; // email + 3 checkbox (все обязательные)
    }

    bindEvents() {
      this.forms.forEach((form) => {
        form.element.off("submit.newsletter");
        form.element.on("submit.newsletter", (e) => {
          e.preventDefault();
          this.trackFormSubmitAttempt(form);
          this.handleSubmit(form);
        });

        // Трекинг клика по email полю с ID
        form.emailField.off("click.mtm");
        form.emailField.on("click.mtm", () => {
          const fieldId = form.emailField.attr("id") || "unknown-email-field";
          this.trackEvent(
            "Newsletter",
            "Button Click",
            `Email Input - ${fieldId}`
          );
        });
      });
    }

    // Метод для трекинга событий в Matomo
    trackEvent(category, action, name, value) {
      if (typeof _paq !== "undefined") {
        const eventParams = ["trackEvent", category, action];
        if (name) eventParams.push(name);
        if (value) eventParams.push(parseFloat(value));
        _paq.push(eventParams);
      }
    }

    // Трекинг попытки отправки формы с ID формы
    trackFormSubmitAttempt(form) {
      const formId = form.id || "unknown-form";
      this.trackEvent("Newsletter", "Form Submit Attempt", formId, 1);
    }

    // Трекинг успешной валидации с ID формы
    trackValidationSuccess(form) {
      const formId = form.id || "unknown-form";
      this.trackEvent("Newsletter", "Validation Success", formId, 1);
    }

    // Трекинг ошибок валидации с ID формы
    trackValidationError(errorType, form) {
      const formId = form.id || "unknown-form";
      this.trackEvent(
        "Newsletter",
        "Validation Error",
        `${errorType} - ${formId}`,
        1
      );
    }

    // Трекинг существующего email с ID формы
    trackExistingEmail(form) {
      const formId = form.id || "unknown-form";
      this.trackEvent("Newsletter", "Email Already Exists", formId, 1);
    }

    // Трекинг успешной подписки с ID формы
    trackSubscriptionSuccess(form) {
      const formId = form.id || "unknown-form";
      this.trackEvent("Newsletter", "Subscription Success", formId, 1);
    }

    // Трекинг ошибки подписки с ID формы
    trackSubscriptionError(errorType, form) {
      const formId = form.id || "unknown-form";
      this.trackEvent(
        "Newsletter",
        "Subscription Error",
        `${errorType} - ${formId}`,
        1
      );
    }

    handleSubmit(form) {
      const formData = form.element.serialize();
      const ajaxData =
        formData +
        "&action=newsletter_subscription&nonce=" +
        encodeURIComponent(newsletter_ajax.nonce);

      const responseContainer = form.element.find(".newsletter-responses");
      const errorResponse = form.element.find(".newsletter-error-response");
      const successResponse = form.element.find(".newsletter-success-response");

      errorResponse.hide();
      successResponse.hide();

      // Валидация и трекинг результатов валидации
      const validationResult = this.validateForm(form);
      if (!validationResult) {
        return false;
      }

      const submitButton = form.submitButton;
      const originalText = submitButton.text();
      submitButton
        .text(this.translations.sending || "Sending...")
        .prop("disabled", true);

      $.ajax({
        url: newsletter_ajax.ajax_url,
        type: "POST",
        data: ajaxData,
        dataType: "json",
        success: (response) => {
          if (response.success) {
            // Трекинг успешной подписки
            this.trackSubscriptionSuccess(form);

            this.showSuccess(
              responseContainer,
              successResponse,
              response.message
            );
            form.element[0].reset();

            // Диспатчим кастомное событие для успешной подписки
            document.dispatchEvent(
              new CustomEvent("newsletter_subscription_success", {
                detail: {
                  ...response,
                  formId: form.id,
                  emailFieldId: form.emailField.attr("id"),
                },
              })
            );
          } else {
            // Трекинг ошибок подписки
            if (
              (response.message &&
                response.message.toLowerCase().includes("уже подписан")) ||
              response.message.toLowerCase().includes("already subscribed") ||
              response.message.toLowerCase().includes("существует")
            ) {
              this.trackExistingEmail(form);
            } else {
              this.trackSubscriptionError("Server Error", form);
            }

            this.showError(responseContainer, errorResponse, response.message);

            // Диспатчим кастомное событие для ошибки подписки
            document.dispatchEvent(
              new CustomEvent("newsletter_subscription_error", {
                detail: {
                  ...response,
                  formId: form.id,
                  emailFieldId: form.emailField.attr("id"),
                },
              })
            );
          }
        },
        error: (xhr, status, error) => {
          console.error("AJAX Error:", status, error, xhr.responseText);

          // Трекинг AJAX ошибки
          this.trackSubscriptionError("AJAX Error", form);

          const errorMsg =
            this.translations.error_occurred ||
            "An error occurred. Please try again later.";
          this.showError(responseContainer, errorResponse, errorMsg);

          // Диспатчим кастомное событие для ошибки подписки
          document.dispatchEvent(
            new CustomEvent("newsletter_subscription_error", {
              detail: {
                message: errorMsg,
                formId: form.id,
                emailFieldId: form.emailField.attr("id"),
              },
            })
          );
        },
        complete: () => {
          submitButton.text(originalText).prop("disabled", false);
        },
      });
    }

    validateForm(form) {
      const email = form.emailField.val().trim();
      const mailingConsent = form.mailingConsentCheckbox.is(":checked");
      const dataProcessingConsent = form.dataProcessingCheckbox.is(":checked");
      const privacyPolicyConsent =
        form.privacyPolicyCheckbox.length > 0
          ? form.privacyPolicyCheckbox.is(":checked")
          : true;
      const errorResponse = form.element.find(".newsletter-error-response");
      const responseContainer = form.element.find(".newsletter-responses");

      // Валидация email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email || !emailRegex.test(email)) {
        const errorMsg =
          this.translations.invalid_email ||
          "Please enter a valid email address";

        // Трекинг ошибки валидации email
        this.trackValidationError("Invalid Email", form);

        this.showError(responseContainer, errorResponse, errorMsg);
        return false;
      }

      // Валидация согласия на рассылку
      if (form.mailingConsentCheckbox.length > 0 && !mailingConsent) {
        const errorMsg =
          this.translations.mailing_consent_required ||
          "Consent to receive mailings is required";

        // Трекинг ошибки согласия на рассылку
        this.trackValidationError("Mailing Consent Required", form);

        this.showError(responseContainer, errorResponse, errorMsg);
        return false;
      }

      // Валидация согласия на обработку данных
      if (form.dataProcessingCheckbox.length > 0 && !dataProcessingConsent) {
        const errorMsg =
          this.translations.data_processing_consent_required ||
          "Consent to process personal data is required";

        // Трекинг ошибки согласия на обработку данных
        this.trackValidationError("Data Processing Consent Required", form);

        this.showError(responseContainer, errorResponse, errorMsg);
        return false;
      }

      // Валидация согласия с политикой конфиденциальности
      if (form.privacyPolicyCheckbox.length > 0 && !privacyPolicyConsent) {
        const errorMsg =
          this.translations.privacy_policy_required ||
          "You must agree to the privacy policy";

        // Трекинг ошибки согласия с политикой конфиденциальности
        this.trackValidationError("Privacy Policy Required", form);

        this.showError(responseContainer, errorResponse, errorMsg);
        return false;
      }

      // Дополнительная валидация для простых форм
      if (form.hasOnlyEmail) {
        const otherFields = form.element.find(
          'input:not([type="email"]):not([type="checkbox"]):not([type="hidden"]):not([type="submit"])'
        );
        if (otherFields.length > 0) {
          const errorMsg = this.translations.invalid_form || "Invalid form";

          // Трекинг ошибки невалидной формы
          this.trackValidationError("Invalid Form Structure", form);

          this.showError(responseContainer, errorResponse, errorMsg);
          return false;
        }
      }

      // Трекинг успешной валидации
      this.trackValidationSuccess(form);

      return true;
    }

    showError(container, element, message) {
      element.html(message).show();
      container.show();

      setTimeout(() => {
        element.hide();
      }, 5000);
    }

    showSuccess(container, element, message) {
      element.html(message).show();
      container.show();

      setTimeout(() => {
        element.hide();
      }, 5000);
    }

    destroy() {
      this.forms.forEach((form) => {
        form.element.off("submit.newsletter");
        form.emailField.off("click.mtm");
      });
      this.forms = [];
    }
  }

  window.newsletterSubscription = new NewsletterSubscription();

  $(document).ready(function () {
    window.newsletterSubscription.init();
  });
})(jQuery);