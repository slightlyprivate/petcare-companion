<?php
// appointments.php
return [
    'created' => [
        'email' => [
            'subject' => 'New Appointment Scheduled: :appointment_date',
            'intro'   => 'Your appointment for :pet_name has been scheduled on :appointment_date.',
        ],
        'sms' => [
            'body' => 'Appointment for :pet_name on :appointment_date scheduled.',
        ],
        'success' => 'The appointment has been created successfully.',
        'failure' => 'Unable to create the appointment.',
    ],

    'updated' => [
        'email' => [
            'subject' => 'Appointment Updated: :appointment_date',
            'intro'   => 'Your appointment for :pet_name has been updated to :appointment_date.',
        ],
        'sms' => [
            'body' => 'Appointment for :pet_name updated to :appointment_date.',
        ],
        'success' => 'The appointment has been updated successfully.',
        'failure' => 'Unable to update the appointment.',
    ],

    'deleted' => [
        'email' => [
            'subject' => 'Appointment Canceled: :appointment_date',
            'intro'   => 'Your appointment for :pet_name on :appointment_date has been canceled.',
        ],
        'sms' => [
            'body' => 'Appointment for :pet_name on :appointment_date canceled.',
        ],
        'success' => 'The appointment has been deleted successfully.',
        'failure' => 'Unable to delete the appointment.',
    ],
];
