<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private $twilioClient;

    public function __construct(private string $twilioSid, private string $twilioAuthToken, private string $twilioPhoneNumber,
                                private LoggerInterface $logger)
    {
        $this->twilioClient = new Client($twilioSid, $twilioAuthToken);
    }

    public function notify(string $recipientPhoneNumber, string $message): bool
    {
        try {
            $this->logger->info('Sending message', [
                'twilioPhoneNumber' => $this->twilioPhoneNumber,
                'recipientPhoneNumber' => $recipientPhoneNumber,
                'message' => $message,
            ]);

            $message = $this->twilioClient->messages->create(
                $recipientPhoneNumber,
                [
                    'from' => $this->twilioPhoneNumber,
                    'body' => $message,
                ]
            );
            $this->logger->info("Message {$message->sid} sent successfully");
            return true;
        } catch (\Exception $e) {

            $this->logger->error('Failed to send notification', [
                'exception' => $e->getMessage(),
                'twilioPhoneNumber' => $this->twilioPhoneNumber,
                'recipientPhoneNumber' => $recipientPhoneNumber,
                'message' => $message,
            ]);
            return false;
        }
    }
}
