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
            consentCheckbox: $(form).find(
              'input[name="soglasie-na-obrabotku"]'
            ),
            nameField: $(form).find('input[name="text-name"]'),
            surnameField: $(form).find('input[name="text-surname"]'),
            phoneField: $(form).find('input[type="tel"]'),
            submitButton: $(form).find('button[type="submit"]'), // Изменено на button
            hasOnlyEmail: this.hasOnlyEmailField(form),
          };

          this.forms.push(formObj);
        });
    }

    hasOnlyEmailField(form) {
      const inputs = $(form).find(
        'input:not([type="hidden"]):not([type="submit"])'
      );
      return inputs.length === 2; // email + checkbox
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

      const submitButton = form.submitButton; // Используем сохраненную кнопку
      const originalText = submitButton.text(); // Изменено с .val() на .text()
      submitButton
        .text(this.translations.sending || "Sending...") // Изменено с .val() на .text()
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
          } else {
            this.showError(responseContainer, errorResponse, response.message);
          }
        },
        error: () => {
          this.showError(
            responseContainer,
            errorResponse,
            this.translations.error_occurred ||
              "An error occurred. Please try again later."
          );
        },
        complete: () => {
          submitButton.text(originalText).prop("disabled", false); // Изменено с .val() на .text()
        },
      });
    }

    validateForm(form) {
      const email = form.emailField.val().trim();
      const consent = form.consentCheckbox.is(":checked");
      const errorResponse = form.element.find(".newsletter-error-response");
      const responseContainer = form.element.find(".newsletter-responses");

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

      if (!consent) {
        this.showError(
          responseContainer,
          errorResponse,
          this.translations.consent_required || "Consent is required"
        );
        return false;
      }

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
      element.text(message).show();
      container.show();

      setTimeout(() => {
        element.hide();
      }, 5000);
    }

    showSuccess(container, element, message) {
      element.text(message).show();
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
