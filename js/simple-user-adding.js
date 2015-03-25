(function ($) {
  $(function () {
    // Allow capitalizing a word, e.g. jOhN -> John
    String.prototype.capitalize = function () {
      return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
    }

    // Detect email input change
    $("#sua_email").on('change keyup paste', function () {
      var val = $(this).val();
      var parts = val.substr(0, val.indexOf('@')).split('.');
      var name = parts[0] ? parts[0] : '';
      var surname = parts[1] ? parts[1] : '';
      //console.log(name.capitalize() + ' ' + surname.capitalize());

      // todo: Add notice beneath the input field a la "Is this X Y? *Insert Name*".
    });

    // Show/hide additional fields on request
    $('#sua_showmore').click(function (e) {
      e.preventDefault();
      $(this).text($(this).text() === $(this).attr('data-more') ? $(this).attr('data-less') : $(this).attr('data-more'));
      $('#sua_createuser .additional').toggleClass('hidden');
    });
  });
}(jQuery));