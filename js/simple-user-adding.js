(function ($) {
  $(function () {
    // Allow capitalizing a word, e.g. jOhN -> John
    String.prototype.capitalize = function () {
      return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
    }

    var firstName,
        lastName,
        firstNameField = $('#sua_first_name'),
        lastNameField = $('#sua_last_name'),
        additionalFieldsShown = false;

    // Detect email input change
    $("#sua_email").on('change keyup paste', function () {
      var val = $(this).val(),
          parts = val.substr(0, val.indexOf('@')).split('.');

      firstName = parts[0] ? parts[0] : '';
      lastName = parts[1] ? parts[1] : '';

      if (0 === firstName.length || firstNameField.val().length > 0 || lastNameField.val().length > 0) {
        $('#sua_email_note').addClass('hidden');
        return;
      }

      $('#sua_email_name').text($.trim(firstName.capitalize() + ' ' + lastName.capitalize()));
      $('#sua_email_note').removeClass('hidden');
    });

    $('#sua_email_note_insert').click(function (e) {
      e.preventDefault();

      if (firstNameField.val().length === 0)
        firstNameField.val(firstName.capitalize());

      if (lastNameField.val().length === 0)
        lastNameField.val(lastName.capitalize());

      if (!additionalFieldsShown)
        $('#sua_showmore').click();

      $('#sua_email_note').addClass('hidden');
    });

    // Show/hide additional fields on request
    $('#sua_showmore').click(function (e) {
      e.preventDefault();
      $(this).text(additionalFieldsShown ? $(this).attr('data-less') : $(this).attr('data-more'));
      additionalFieldsShown = !additionalFieldsShown;
      $('#sua_createuser .additional').toggleClass('hidden');
    });

    // JS form validation
    $('#sua_createuser').submit(function (e) {
      if (!validateForm(this))
        e.preventDefault();
    })
  });
}(jQuery));