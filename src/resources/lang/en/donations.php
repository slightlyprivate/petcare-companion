<?php

return [

    'created' => [
        'email' => [
            'subject' => 'Thank You for Your Donation',
            'intro'   => 'Thank you for donating :amount to :pet_name.',
        ],
        'sms' => [
            'body' => 'Donation :amount → :pet_name. ID: :donation_id.',
        ],
        'success' => 'Donation recorded successfully.',
        'failure' => 'Failed to record donation.',
    ],

];
