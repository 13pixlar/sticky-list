/*
Description: Adds support for Multi Page view
Author: Ian Nicholson
Author URI: http://iannicholson.co.uk
*/

jQuery(document).ready(function($) {
  function hideDatePicker(){
    // Hide any datepicker icons
    if($('.ginput_container_date input').length){
      $('.ginput_container_date input').datepicker(

         "disable"
      );
    }
  }

  function updateProgressBar(page_no, pageCount){
    //Update % progress and Step x of y title
    var percent = Math.round((page_no / pageCount * 100)) + '%';

    $('.gf_progressbar_percentage').css('width', percent);
    $('div.gf_progressbar_percentage > span').html(percent);
    $('.gf_progressbar_title').html('Step ' + page_no + ' of ' + pageCount);
  }

  function updateSteps(page_no, pageCount, direction){
    // add/ remove classes to progress steps
    var current_step_el =  $("div.gf_page_steps .gf_step_active");

    if(direction == 'next'){
      $(current_step_el).prev().removeClass( "gf_step_previous" );
      $(current_step_el).removeClass( "gf_step_active" ).addClass( "gf_step_completed gf_step_previous" );
      $(current_step_el).next().removeClass( "gf_step_next gf_step_pending" ).addClass( "gf_step_active" );
      if((current_step_el).nextAll().eq(1).hasClass("gf_step")){
        $(current_step_el).nextAll().eq(1).addClass( "gf_step_next" );
      }
    }
    else{
      if((current_step_el).prevAll().eq(1).hasClass("gf_step")){
        $(current_step_el).prevAll().eq(1).addClass( "gf_step_previous" );
      }
      $(current_step_el).prev().removeClass( "gf_step_completed gf_step_previous" ).addClass( "gf_step_active" );
      $(current_step_el).removeClass( "gf_step_active gf_step_completed" ).addClass( "gf_step_next gf_step_pending" );
      $(current_step_el).next().removeClass( "gf_step_next" );
    }
  }

  function sl_pagination() {
    // Get the mode - Tells us if we're on the Sticky List view or edit page
    // NOTE: This will only work if the hidden field (name=mode) is added to -
    //       the form in classs-sticky-list.php

    var mode = $('input[name=mode]').val();
    if(mode == 'view'){

      // Hide icons beside any datepickers in both single and multi page forms
      hideDatePicker();

      // Get the number of pages in multi-page form - length is 0 for single page
      var num_pages = $('.gform_page').length;

      if(num_pages > 0){
        //Remove un-wanted attributes from buttons
        $('.gform_next_button').removeAttr('disabled');
        $('.gform_next_button').removeAttr('onclick');

        $('.gform_previous_button').removeAttr('disabled');
        $('.gform_previous_button').removeAttr('onclick');
        // Final previous button has type submit, change it to ordinary button
        $('.gform_previous_button').attr('type', 'button');

         // Initialise the Step Title under progress bar
        if($('.gf_progressbar_title').length){
          /*
          NOTE: Need to remove name of step from title. When displaying progress bar
                Gravity forms only writes first page title to page, so can't access
                other values as we navigate.
                I'm sure their will be a GF hook available but it's not a priority for me.
           */
          $('.gf_progressbar_title').html('Step 1 of ' + num_pages);
        }

        //Loop through each page element
        $.each($('.gform_page'),function(i,val){
          /*
          Add new attribute: 'current_page'to element so we can update
          progress bar/steps as we track back and forward through pages
          */
          $(this).attr('page_no', i+1);

          //Get previous and next elements of current page
          var next_page = $(this).next();
          var next_button = $(this).find('.gform_next_button');
          var prev_page = $(this).prev();
          var prev_button = $(this).find('.gform_previous_button');

          //Keep a referenece to current page
          var this_page = this;
          //Hide and display pages as appropriate
          $(next_button).click(function() {
            $(next_page).css('display', 'block');
            $(this_page).css('display', 'none');
            //Update our progress
            next_page_no = $(next_page).attr('page_no');
            if ($(".gf_progressbar").length){
              updateProgressBar(next_page_no, num_pages);
            }
            else if ($(".gf_page_steps").length){
              updateSteps(next_page_no, num_pages, 'next');
            }
          });

          $(prev_button).click(function() {
            $(prev_page).css('display', 'block');
            $(this_page).css('display', 'none');
            //Update our progress
            next_page_no = $(prev_page).attr('page_no');
            if ($(".gf_progressbar").length){
              updateProgressBar(next_page_no, num_pages);
            }
            else if ($(".gf_page_steps").length){
              updateSteps(next_page_no, num_pages, 'prev');
            }
          });
        });
      }
    }
  };
  sl_pagination();
});
