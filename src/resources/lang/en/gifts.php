<?php

return [

    'created' => [
        'email' => [
            'subject' => 'Thank You for Your Gift!',
            'intro' => 'Thank you for gifting :amount to :pet_name.',
        ],
        'sms' => [
            'body' => 'Gift :amount → :pet_name. ID: :gift_id.',
        ],
        'success' => 'Gift recorded successfully.',
        'failure' => 'Failed to record gift.',
    ],

];
