//Wait for jQuery to load
jQuery(document).ready(function ($) {
  //Check if the question is on the current page
  if ($("#none-selected")) {
    selectOption($);
  }
});

function selectOption($) {
  //Get all forms into an array with their IDs
  const formArr = [
    {
      id: 5256,
      name: "Contact Private Training",
      value: "I want to contact you about private training",
      sub_id: 1733745,
    },
    {
      id: 9220,
      name: "Contact Make Cosmetics",
      value: "I want to learn how to make cosmetics",
      sub_id: 1706171,
    },
    {
      id: 9221,
      name: "Contact Make Formulation Service",
      value: "I have a question about your formulation service",
      sub_id: 1733741,
    },
    {
      id: 9222,
      name: "Contact Something else",
      value: "I want to contact you about something else",
      sub_id: 1733156,
    },
  ];

  render(null, formArr, $);
}

/**
 * Renders either the select radio options or the form
 *
 * @param {Number || null} selectedFormIndex
 * @param {Array} formArr
 * @param {function} $
 */
function render(selectedFormIndex, formArr, $) {
  const formWrapper = $(".form-wrapper");
  const radioWrapper = $(".radio-wrapper");
  //Append to the shortcode div
  if (selectedFormIndex) {
    formWrapper.empty();
    formWrapper.append("<p>Loading...</p>");
    generateFormShortCode(selectedFormIndex, formArr, $);
  } else {
    formWrapper.empty();
    const result = generateQuestionSelectList(formArr);
    radioWrapper.append(result);
    generateFormShortCode(0, formArr, $);

    $("input:radio[name=form-option]").change(function (e) {
      console.log("input select", e.target.value);
      render(e.target.value, formArr, $);
    });
  }
}

/**
 * Generate a list of radio items for the user to select
 *
 * @param {Array} arr
 */
function generateQuestionSelectList(arr) {
  let result = '<div class="select-list">';
  result += '<h2>Contact Us<span class="accent">.</span></h2>';
  arr.forEach((dat, i) => {
    result += `<span class="select-option"><input ${i === 0 && "checked"} id=${
      dat.id
    }  type="radio" name="form-option" value="${i}" required="required"><label for="${
      dat.id
    }">${dat.value}</label></span>`;
  });

  return (result += "</div>");
}

/**
 * Returns a form shortcode, along with a back button
 *
 * @param {Number} formI
 * @param {Array} arr
 */
function generateFormShortCode(formI, arr, $) {
  let result = '<div class="inner-form-wrapper">';
  const shortcode = `[contact-form-7 id=${arr[formI].id} title='${arr[formI].name}']`;
  $.ajax({
    url: window.location.origin + "/wp-admin/admin-ajax.php",
    type: "GET",
    data: {
      action: "request_custom_contact_form",
      shortcode: shortcode,
      tag_id: arr[formI].sub_id,
    },
    success: function (res) {
      result += res;
      // result += '<button class="select-again-button">Select Again</button>';
    },
    error: function (err) {
      console.log("error!", err);
      result +=
        "<p>There was a problem loading your form, please try again</p>";
      // result += '<button class="select-again-button">Select Again</button>';
    },
  }).always(function () {
    $(".form-wrapper").empty();
    $(".form-wrapper").append(result);

    $(".select-again-button").click(function () {
      console.log("rendering null!");
      render(null, arr, $);
    });
  });
}
