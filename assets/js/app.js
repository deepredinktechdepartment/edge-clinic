
// Avatar Random Colors
var colorsArray = ['#05668D', '#028090', '#00A896', '#02C39A', '#F0F3BD'];
var divsArray = document.getElementsByClassName('nav-avatar');
var uniqueColorIndex = 0;

for(var i=0; i < divsArray.length; i++){
uniqueColorIndex = Math.floor(Math.random() * (colorsArray.length - 0 + 1));
  divsArray[i].style['background-color'] = colorsArray[uniqueColorIndex];
};

// ON LOAD
$(window).on("load", function () {
  navAdjest();
  // flatpickr_days_adjest();
});

// ON READY
$(document).ready(function() {
  navAdjest();
  // flatpickr_days_adjest();
});
// ON RESIZE
$(window).resize(function () {
  navAdjest();
  commentAdjest();
  // flatpickr_days_adjest();
});

function navAdjest() {
  var h = $(".nav-sub-strip-container").height();
  $('.nav-sub-strip').css('max-height', h + 'px');
  var nav_h = $('.nav-body-header').outerHeight();
  $('.ice-body-container').css('min-height', 'calc(100vh - ' + nav_h +'px' );
  $('.ice-body-container').css('max-height', 'calc(100vh - ' + nav_h +'px' );
  commentAdjest();
}

function commentAdjest() {
  var tt_body_img = $(".tt-body-img").innerHeight();
  $('.tt-coments-wrap').css('height', tt_body_img + 'px');
}

$('.btn-burger').on('click', function() {
  $('.btn-burger').removeClass('hide');
  $(this).addClass('hide');
  $('.nav-sub-strip-container, .t_ice-body, .main-header').toggleClass('hide');
});

$('.nav-strip .nav').on('click', '.nav-link',  function() {
  $('.nav-strip .nav .nav-link').removeClass('active');
  $(this).addClass('active');
});
$('#pills-apply-leave-tab, .flatpickr-day').on('click',  function() {
  var flatpickr_day = $('.flatpickr-day').width();
  // alert(flatpickr_day);
  $('.flatpickr-day').css('height', flatpickr_day + 'px');
});

/**
* flatpickr
*/
var flatpickrExamples = document.querySelectorAll("[data-provider]");
Array.from(flatpickrExamples).forEach(function (item) {
  if (item.getAttribute("data-provider") == "flatpickr") {
    var dateData = {};
    var isFlatpickerVal = item.attributes;
    dateData.disableMobile = "true";
    if (isFlatpickerVal["data-date-format"])
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    if (isFlatpickerVal["data-enable-time"]) {
      (dateData.enableTime = true),
        (dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString() + " H:i");
    }
    if (isFlatpickerVal["data-altFormat"]) {
      (dateData.altInput = true),
        (dateData.altFormat = isFlatpickerVal["data-altFormat"].value.toString());
    }
    if (isFlatpickerVal["data-minDate"]) {
      dateData.minDate = isFlatpickerVal["data-minDate"].value.toString();
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-maxDate"]) {
      dateData.maxDate = isFlatpickerVal["data-maxDate"].value.toString();
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-deafult-date"]) {
      dateData.defaultDate = isFlatpickerVal["data-deafult-date"].value.toString();
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-multiple-date"]) {
      dateData.mode = "multiple";
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-range-date"]) {
      dateData.mode = "range";
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-inline-date"]) {
      (dateData.inline = true),
        (dateData.defaultDate = isFlatpickerVal["data-deafult-date"].value.toString());
      dateData.dateFormat = isFlatpickerVal["data-date-format"].value.toString();
    }
    if (isFlatpickerVal["data-disable-date"]) {
      var dates = [];
      dates.push(isFlatpickerVal["data-disable-date"].value);
      dateData.disable = dates.toString().split(",");
    }
    if (isFlatpickerVal["data-week-number"]) {
      var dates = [];
      dates.push(isFlatpickerVal["data-week-number"].value);
      dateData.weekNumbers = true
    }
    flatpickr(item, dateData);
  } else if (item.getAttribute("data-provider") == "timepickr") {
    var timeData = {};
    var isTimepickerVal = item.attributes;
    if (isTimepickerVal["data-time-basic"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.dateFormat = "H:i");
    }
    if (isTimepickerVal["data-time-hrs"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.dateFormat = "H:i"),
        (timeData.time_24hr = true);
    }
    if (isTimepickerVal["data-min-time"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.dateFormat = "H:i"),
        (timeData.minTime = isTimepickerVal["data-min-time"].value.toString());
    }
    if (isTimepickerVal["data-max-time"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.dateFormat = "H:i"),
        (timeData.minTime = isTimepickerVal["data-max-time"].value.toString());
    }
    if (isTimepickerVal["data-default-time"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.dateFormat = "H:i"),
        (timeData.defaultDate = isTimepickerVal["data-default-time"].value.toString());
    }
    if (isTimepickerVal["data-time-inline"]) {
      (timeData.enableTime = true),
        (timeData.noCalendar = true),
        (timeData.defaultDate = isTimepickerVal["data-time-inline"].value.toString());
      timeData.inline = true;
    }
    flatpickr(item, timeData);
  }
});