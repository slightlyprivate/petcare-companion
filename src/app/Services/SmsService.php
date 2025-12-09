<?php

namespace App\Services;

use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Service responsible for delivering SMS messages through AWS SNS.
 */
class SmsService
{
    protected ?SnsClient $client = null;

    /**
     * Send an SMS message via AWS SNS.
     *
     * @throws Throwable
     */
    public function send(string $message, ?string $phoneNumber = null): void
    {
        $config = $this->getConfig();

        if (! $this->hasCredentials($config)) {
            throw new RuntimeException('SNS credentials are not configured.');
        }

        $payload = [
            'Message' => $message,
        ];

        if ($phoneNumber) {
            $payload['PhoneNumber'] = $phoneNumber;

            if (! empty($config['origination'])) {
                $payload['MessageAttributes'] = [
                    'AWS.SNS.SMS.OriginationNumber' => [
                        'DataType' => 'String',
                        'StringValue' => $config['origination'],
                    ],
                ];
            }
        } elseif (! empty($config['topic'])) {
            $payload['TopicArn'] = $config['topic'];
        } else {
            throw new RuntimeException('SNS topic ARN or destination phone number is required.');
        }

        try {
            $this->getClient($config)->publish($payload);
        } catch (Throwable $exception) {
            Log::error('SNS SMS publish failed', [
                'error' => $exception->getMessage(),
                'phone_number' => $phoneNumber,
                'topic' => $config['topic'] ?? null,
            ]);

            throw $exception;
        }
    }

    /**
     * Determine if all required SNS credentials are present.
     *
     * @param  array<string, mixed>  $config
     */
    protected function hasCredentials(array $config): bool
    {
        return ! empty($config['key']) && ! empty($config['secret']) && ! empty($config['region']);
    }

    /**
     * Lazily instantiate an SNS client with the provided configuration.
     *
     * @param  array<string, mixed>  $config
     */
    protected function getClient(array $config): SnsClient
    {
        if ($this->client === null) {
            $this->client = new SnsClient([
                'version' => '2010-03-31',
                'region' => $config['region'],
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Retrieve the SNS service configuration from Laravel.
     *
     * @return array<string, mixed>
     */
    protected function getConfig(): array
    {
        return config('services.sns') ?? [];
    }
}
