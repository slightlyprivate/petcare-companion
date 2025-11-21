<?php

return [
    'upload' => [
        'success' => 'File uploaded successfully.',
    ],
    'validation' => [
        'file' => [
            'required' => 'Please provide a file to upload.',
            'file' => 'Upload must be a valid file.',
            'mimes' => 'Supported file types: jpg, jpeg, png, gif, webp, mp4, webm.',
            'max' => 'Uploads must be 10MB or smaller.',
        ],
        'context' => [
            'in' => 'Context must be one of activities, pet_avatars, or general.',
        ],
    ],
];
