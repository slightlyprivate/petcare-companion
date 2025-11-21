<?php

return [
    'created' => [
        'success' => 'Activity logged successfully.',
    ],
    'deleted' => [
        'success' => 'Activity removed successfully.',
    ],
    'validation' => [
        'type' => [
            'required' => 'Activity type is required.',
            'max' => 'Activity type must be 50 characters or fewer.',
        ],
        'description' => [
            'required' => 'Description is required.',
        ],
        'media_url' => [
            'max' => 'Media reference must be 255 characters or fewer.',
        ],
    ],
];
