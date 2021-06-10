<?php

namespace PostHog;

use Closure;

class HttpClient
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var bool
     */
    private $useSsl;

    /**
     * @var int
     */
    private $maximumBackoffDuration;

    /**
     * @var bool
     */
    private $compressRequests;

    /**
     * @var Closure|null
     */
    private $errorHandler;
    /**
     * @var bool
     */
    private $debug;

    public function __construct(
        string $host,
        bool $useSsl = true,
        int $maximumBackoffDuration = 10000,
        bool $compressRequests = false,
        bool $debug = false,
        ?Closure $errorHandler = null
    ) {
        $this->host = $host;
        $this->useSsl = $useSsl;
        $this->maximumBackoffDuration = $maximumBackoffDuration;
        $this->compressRequests = $compressRequests;
        $this->debug = $debug;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param string $path
     * @param string|null $payload
     * @param array $extraHeaders
     * @return HttpResponse
     */
    public function sendRequest(string $path, ?string $payload, array $extraHeaders = [])
    {
        $protocol = $this->useSsl ? "https://" : "http://";

        $backoff = 100;     // Set initial waiting time to 100ms

        while ($backoff < $this->maximumBackoffDuration) {
            // open connection
            $ch = curl_init();

            if (null !== $payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }

            $headers = [];
            $headers[] = 'Content-Type: application/json';
            if ($this->compressRequests) {
                $headers[] = 'Content-Encoding: gzip';
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, $extraHeaders));
            curl_setopt($ch, CURLOPT_URL, $protocol . $this->host . $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // retry failed requests just once to diminish impact on performance
            $httpResponse = $this->executePost($ch);
            $responseCode = $httpResponse->getResponseCode();

            //close connection
            curl_close($ch);

            if (200 != $responseCode) {
                // log error
                $this->handleError($ch, $responseCode);

                if (($responseCode >= 500 && $responseCode <= 600) || 429 == $responseCode) {
                    // If status code is greater than 500 and less than 600, it indicates server error
                    // Error code 429 indicates rate limited.
                    // Retry uploading in these cases.
                    usleep($backoff * 1000);
                    $backoff *= 2;
                } elseif ($responseCode >= 400) {
                    break;
                } elseif ($responseCode == 0) {
                    break;
                }
            } else {
                break;  // no error
            }
        }

        return $httpResponse;
    }

    private function executePost($ch): HttpResponse
    {
        return new HttpResponse(
            curl_exec($ch),
            curl_getinfo($ch, CURLINFO_RESPONSE_CODE)
        );
    }

    private function handleError($code, $message)
    {
        if (null !== $this->errorHandler) {
            $this->errorHandler($code, $message);
        }

        if ($this->debug) {
            error_log("[PostHog][HttpClient] " . $message);
        }
    }
}
