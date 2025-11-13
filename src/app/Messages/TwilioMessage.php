<?php

namespace App\Messages;

/**
 * Represents a Twilio SMS message to be sent.
 */
class TwilioMessage
{
    /**
     * The phone number to send the message to.
     */
    protected string $phoneNumber;

    /**
     * The message content.
     */
    protected string $content;

    /**
     * Create a new TwilioMessage instance.
     */
    public function __construct(string $phoneNumber, string $content)
    {
        $this->phoneNumber = $phoneNumber;
        $this->content = $content;
    }

    /**
     * Get the phone number.
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * Get the message content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the phone number.
     */
    public function phone(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Set the message content.
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
