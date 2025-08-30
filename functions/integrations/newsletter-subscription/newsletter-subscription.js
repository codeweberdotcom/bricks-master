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
      return inputs.length === 3; // email + 2 checkbox
    }

    bindEvents() {
      this.forms.forEach((form) => {
        form.element.off("submit.newsletter");
        form.element.on("submit.newsletter", (e) => {
          e.preventDefault();
          this.handleSubmit(form);
        });
      });
    }

    handleSubmit(form) {
      const formData = form.element.serialize();
      const responseContainer = form.element.find(".newsletter-responses");
      const errorResponse = form.element.find(".newsletter-error-response");
      const successResponse = form.element.find(".newsletter-success-response");

      errorResponse.hide();
      successResponse.hide();

      if (!this.validateForm(form)) {
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
        data: formData,
        dataType: "json",
        success: (response) => {
          if (response.success) {
            this.showSuccess(
              responseContainer,
              successResponse,
              response.message
            );
            form.element[0].reset();

            // Диспатчим кастомное событие для успешной подписки
            document.dispatchEvent(
              new CustomEvent("newsletter_subscription_success", {
                detail: response,
              })
            );
          } else {
            this.showError(responseContainer, errorResponse, response.message);

            // Диспатчим кастомное событие для ошибки подписки
            document.dispatchEvent(
              new CustomEvent("newsletter_subscription_error", {
                detail: response,
              })
            );
          }
        },
        error: () => {
          const errorMsg =
            this.translations.error_occurred ||
            "An error occurred. Please try again later.";
          this.showError(responseContainer, errorResponse, errorMsg);

          // Диспатчим кастомное событие для ошибки подписки
          document.dispatchEvent(
            new CustomEvent("newsletter_subscription_error", {
              detail: { message: errorMsg },
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
      const errorResponse = form.element.find(".newsletter-error-response");
      const responseContainer = form.element.find(".newsletter-responses");

      // Валидация email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email || !emailRegex.test(email)) {
        this.showError(
          responseContainer,
          errorResponse,
          this.translations.invalid_email ||
            "Please enter a valid email address"
        );
        return false;
      }

      // Валидация согласия на рассылку
      if (form.mailingConsentCheckbox.length > 0 && !mailingConsent) {
        this.showError(
          responseContainer,
          errorResponse,
          this.translations.mailing_consent_required ||
            "Consent to receive mailings is required"
        );
        return false;
      }

      // Валидация согласия на обработку данных
      if (form.dataProcessingCheckbox.length > 0 && !dataProcessingConsent) {
        this.showError(
          responseContainer,
          errorResponse,
          this.translations.data_processing_consent_required ||
            "Consent to process personal data is required"
        );
        return false;
      }

      // Дополнительная валидация для простых форм
      if (form.hasOnlyEmail) {
        const otherFields = form.element.find(
          'input:not([type="email"]):not([type="checkbox"]):not([type="hidden"]):not([type="submit"])'
        );
        if (otherFields.length > 0) {
          this.showError(
            responseContainer,
            errorResponse,
            this.translations.invalid_form || "Invalid form"
          );
          return false;
        }
      }

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
      });
      this.forms = [];
    }
  }

  window.newsletterSubscription = new NewsletterSubscription();

  $(document).ready(function () {
    window.newsletterSubscription.init();
  });
})(jQuery);
