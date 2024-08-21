<?php

return [
    'fetch_fields'  => '/api/v1/form-fields',
    'create_ticket' => '/api/v1/ticket',
    'channel'       => 'livechat',
    'ticket_integration_url' => env('TICKET_INTEGRATION_URL', 'https://qa-ace.surbo.io'),
    'lqs_create_ticket' => '/api/v1/lead',
    'lqs_update_lead' => '/api/v1/update-lead',
    'ticket_details' => '/api/v1/ticket-details',
    'lead_details' => '/api/v1/lead-details',

    'ticket_attachment_size' => env('UPLOAD_MAX_FILESIZE', 10240),
    'ticket_attachment_folder' => env('TICKET_ATTACHMENT_FOLDER', '___ORG_ID__'.DIRECTORY_SEPARATOR.'ticket_attachments'),
    'ticket_attachment_disk' => 'local', //s3
    'ticket_attachment_file_suffix' => '_attachment',

    'alba_car_organization_id'=> env('ALBA_CARS_ORGANIZATION_ID')

];
