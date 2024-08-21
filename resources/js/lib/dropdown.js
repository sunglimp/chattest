function create_custom_dropdowns() {
  $('select').each(function (i, select) {
    // $(this) is select

    var search = false;
    if($(this).hasClass('dropdown-search')) search = true;


    if (!$(this).next().hasClass('dropdown')) {     
      if (!$(this).attr('multiple')) {
        $(this).after('<div class="dropdown ' + ($(this).attr('class') || '') + '" tabindex="0"><span class="current"></span><div class="list"><ul></ul></div></div>');
      }
      else { 
        $(this).after('<div class="dropdown multiple ' + ($(this).attr('class') || '') + '" tabindex="0"><span class="current"></span><div class="list"><ul></ul></div></div>');
      }

      var dropdown = $(this).next();
      var options = $(select).find('option');
      // all the options in the current section
      var selected = $(this).find('option:selected');

      // add a div with class dropdown and if any classes are added to select
      // if any option is selected
      dropdown.find('.current').html(selected.data('display-text') || selected.text());
      selected = '';
      // dropdown.find('.current').html('Select');
      if(search){
        dropdown.find('.list').prepend('<div class="dropdown-search-container"><input type="text" class="custom-input" id="dropdown-search" placeholder="Search"><i class="fas fa-search"></i></div>');
      }
      if (!$(this).attr('multiple')) {
        options.each(function (j, o) {
          var display = $(o).data('display-text') || '';
          dropdown.find('ul').append('<li class="option ' + ($(o).is(':selected') ? 'selected' : '') + '" data-value="' + $(o).val() + '" data-display-text="' + display + '" title="' + $(o).text() + '" ><span>' + $(o).text() + '</span></li>');
        });
      }
      else {
        options.each(function (j, o) {
          var display = $(o).data('display-text') || '';
          dropdown.find('ul').append('<li id="multiple-option" class="option ' + ($(o).is(':selected') ? 'selected' : '') + '" data-value="' + $(o).val() + '" data-display-text="' + display + '" title="' + $(o).text() + '" > <span class="multiselect-check ' + ($(o).is(':selected') ? '' : 'invisible') + '"><i class="fas fa-check"></i></span> <span>' + $(o).text() + '</span></li>');
        });
        var currentTitle = [];
        var len = dropdown.find('ul').find('.selected').length;
        dropdown.find('ul').find('.selected').each(function(i, text){
          if(i+1!==len){
            currentTitle.push(text.getAttribute('title')+', ');
          }
          else currentTitle.push(text.getAttribute('title'));
        })
        dropdown.find('.current').html(currentTitle);
      }

    }
  });

    var allDropdowns = $('.list');
    for(i=0;i<allDropdowns.length;i++){
        var ps = new PerfectScrollbar(allDropdowns[i],{
            minScrollbarLength: 40
        });
    }
}



// Event listeners

// Open/close
$(document).on('click', '.dropdown', function (event) {
  var isMultiple = false;
  var search = false;
  if ($(this).closest('.dropdown').hasClass('multiple')) isMultiple = true;
  $('.dropdown').not($(this)).removeClass('open');
  $(this).toggleClass('open');
  if ($(this).hasClass('open')) {
    $(this).find('.option').attr('tabindex', 0);
    $(this).find('.selected').focus();
  } else {
    $(this).find('.option').removeAttr('tabindex');
    $(this).focus();
  }
});



// Close when clicking outside
$(document).on('mousedown', function (event) {
  // console.log($('.popup').target);
  $('.login__inputs').removeClass('focused');
  if ($(event.target).closest('.dropdown').length === 0) {
    $('.dropdown').removeClass('open');
    $('.dropdown .option').removeAttr('tabindex');
  }
  event.stopPropagation();
});


$(document).on('click', '#multiple-option', function (event) {
  var selectedValues = [];
  var selectedText = [];
  event.stopImmediatePropagation();
  $(this).toggleClass('selected');
  $(this).find('.multiselect-check').toggleClass('invisible');

  $(this).closest('.dropdown').find('.option').each(function (i, data) {
    if (data.getAttribute('class').indexOf('selected') > 0) {
      selectedValues.push(data.getAttribute('data-value'));
      selectedText.push(data.getAttribute('title'));
      // console.log(data.getAttribute('title'))
    }
  });
  // console.log(selectedValues);
  var text = $(this).data('display-text') || $(this).text();
  if (selectedValues.length > 0) $(this).closest('.dropdown').find('.current').text(selectedText);
  else $(this).closest('.dropdown').find('.current').text('Select');
  $(this).closest('.dropdown').prev('select').val(selectedValues).trigger('change');
});

// prevent closing when clicked inside
$(document).on('click', '.ps__rail-y', function (event) {
  event.stopPropagation();
});

// Option click
$(document).on('click', '.dropdown .option', function (event) {
  var isMultiple = false;
  if ($(this).closest('.dropdown').hasClass('multiple')) isMultiple = true;
  if (!isMultiple) {
    $(this).closest('.list').find('.selected').removeClass('selected');
    $(this).addClass('selected');
  }
  else {
    if ($(this).hasClass('selected')) $(this).removeClass('selected');
    else $(this).addClass('selected');
  }
  var text = $(this).data('display-text') || $(this).text();
  $(this).closest('.dropdown').find('.current').text(text);
  $(this).closest('.dropdown').prev('select').val($(this).data('value')).trigger('change');
});

// Keyboard events
$(document).on('keydown', '.dropdown', function (event) {
  // console.log(event.keyCode)
  var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
  // console.log(focused_option)
  // Space or Enter
  if (event.keyCode == 13) {
    if ($(this).hasClass('open')) {
      focused_option.trigger('click');
    } else {
      $(this).trigger('click');
    }
    return false;
    // Down
  } else if (event.keyCode == 40) {

    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      focused_option.next().focus();
    }
    return false;
    // Up
  } else if (event.keyCode == 38) {
    // var focused_option = $(this).find('.list .option');
    // console.log(focused_option);
    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
      focused_option.prev().focus();
    }
    return false;
    // Esc
  } 
  // else if (event.keyCode == 27) {
  //   if ($(this).hasClass('open')) {
  //     $(this).trigger('click');
  //   }
  //   return false;
  // }
  else{
    $(this).find('.list .dropdown-search-container input').focus();
  }
});

$(document).ready(function () {
  create_custom_dropdowns();

});

$(document).on('click', '#dropdown-search', function (event) {
  event.preventDefault();
  event.stopImmediatePropagation();
});

$(document).on('keyup', '#dropdown-search', function (event) {
  // console.log($(this).val());
  var filter = $(this).val().toUpperCase();
  // console.log($(this).parent().next().find('li')[0].innerText);
  var li = $(this).parent().next().find('li');
  for (i = 0; i < li.length; i++) {
    txtValue = li[i].textContent || li[i].innerText;
    // console.log(txtValue)
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      li[i].style.display = "";
    } else {
      li[i].style.display = "none";
    }
  }
});
