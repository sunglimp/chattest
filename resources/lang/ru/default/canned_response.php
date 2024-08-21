<?php
 return [
     'ui_elements_messages' => [
        'response' => 'Response',
        'shortcut' => 'Shortcut',
        'date' => 'Date',
        'action' => 'Action',
        'canned_response'=>'Canned Response',
        'add_canned_response'=>'Add Canned Response',
        'shortcuts'=>'Shortcuts',
        'canned_response_text'=>'Canned response text',
        'submit'=>'Submit',
        'cancel'=>'Cancel',
        'edit_canned_response'=>'Edit Canned Response',
        'show'=>'Show',
        'enteries'=>'enteries',
        'previous'=>'Previous',
        'next'=>'Next',
        'search'=>'Search',
        'canned_response_delete_confirm' => 'Are you sure to delete this canned response?',
        'first_page' => 'First',
        'previous_page' => 'Previous',
        'next_page' => 'Next',
        'last_page' => 'Last',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'yes' => 'Yes',
        'no' => 'No'
     ],
     'success_messages' => [
        'canned_response_sucess'=>'Canned Response added successfully',
        'canned_response_edit_sucess'=>'Canned Response edited successfully',
        'canned_response_fetch_sucess'=>'Canned Response fetched successfully',
        'canned_response_delete_sucess'=>'Canned Response deleted successfully',
     ],
     'fail_messages' => [
        'canned_response_fail'=> 'Canned Response failed',
        'canned_response_edit_fail'=>'Canned Response edit failed',
        'canned_response_fetch_fail'=>'Canned Response not fetched',
        'something_went_wrong' => 'Something went wrong'
     ],
     'validation_messages' => [
        'shortcut_max_length' => 'Only 20 characters allowed',
        'unique_combination' => 'Duplicate combination of shortcut and response is not allowed',
        'response_required' => 'This field is required',
        'shortcut_required' => 'This field is required',
        'canned_response_id_required' =>  'Canned Response Id required',
        'no_data_available' => 'No data available',
        'no_matching_records_found' => 'No matching records found'
     ]
 ];
