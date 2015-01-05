(function() {
  function updateTimezoneLabel(val) {
    if (val) {
      $('label[for="timezones"] .glyphicon')
        .removeClass('glyphicon-remove').removeClass('text-danger')
        .addClass('glyphicon-ok').addClass('text-success');
    } else {
      $('label[for="timezones"] .glyphicon')
        .removeClass('glyphicon-ok').removeClass('text-success')
        .addClass('glyphicon-remove').addClass('text-danger');
    }
  }

  function updateImportForm(timezonesVal) {
    $toImport = $('.panel-to-import');
    $('input[name="timezones"]', $toImport).val(timezonesVal);
    if (timezonesVal) {
      $('button[type="submit"]', $toImport)
        .removeAttr('disabled')
        .removeClass('btn-danger')
        .addClass('btn-success')
        .text('Import selection');
    } else {
      $('button[type="submit"]', $toImport)
        .attr('disabled', 'disabled')
        .removeClass('btn-success')
        .addClass('btn-danger')
        .text('Please select your timezone');
    }

    $.get('/', {
      "raw": "yass",
      "timezone": timezonesVal
    }, function(data) {
      console.log(data);
    });
  }

	$('#timezones').on('change', function() {
    var val = $(this).val();

    if (window.localStorage) {
      localStorage.setItem('googlefeedbit-timezones', val);
    }

    updateTimezoneLabel(val);
    updateImportForm(val);
  });

  var timezones = window.localStorage && localStorage.getItem('googlefeedbit-timezones');
  $('#timezones').val(timezones);
  updateTimezoneLabel(timezones);
  updateImportForm(timezones);
})();
