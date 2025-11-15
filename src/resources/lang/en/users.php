<?php

return [

    'export' => [
        'queued' => [
            'email' => [
                'subject' => 'Your Data Export is Being Prepared',
                'intro' => 'We have received your data export request. You will receive another email when it is ready for download.',
            ],
            'sms' => [
                'body' => 'Your data export request is being processed. You will receive an email when it is ready.',
            ],
            'success' => 'Your data export request is queued. You will receive an email when it is ready.',
            'failure' => 'Failed to queue your data export request.',
        ],
        'ready' => [
            'email' => [
                'subject' => 'Your Data Export is Ready for Download',
            ],
        ],
    ],
    'delete' => [
        'queued' => [
            'email' => [
                'subject' => 'Your Data Deletion is Being Processed',
                'intro' => 'We have received your data deletion request. You will receive another email when it is complete.',
            ],
            'sms' => [
                'body' => 'Your data deletion request is being processed. You will receive an email when it is complete.',
            ],
            'success' => 'Your data deletion request is queued. You will receive an email when it is complete.',
            'failure' => 'Failed to queue your data deletion request.',
        ],
    ],

];
