<?php

return [
    'created' => [
        'email' => [
            'subject' => 'Thank You for Purchasing Credits!',
            'intro' => 'You have successfully purchased :credits credits for :amount.',
        ],
        'sms' => [
            'body' => 'Purchased :credits credits for :amount. ID: :purchase_id.',
        ],
        'success' => 'Credit purchase completed successfully.',
        'failure' => 'Failed to complete credit purchase.',
        'errors' => [
            'invalid_bundle' => 'The selected credit bundle is not available.',
            'invalid_return_url' => 'The return URL provided is not valid.',
        ],
    ]
];
