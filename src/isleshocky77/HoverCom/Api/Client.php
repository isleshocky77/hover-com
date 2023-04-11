<?php

declare(strict_types=1);


namespace isleshocky77\HoverCom\Api;

use GuzzleHttp;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class Client
{
    private \GuzzleHttp\Client $client;

    public function __construct()
    {
        $cookieJar = new FileCookieJar(__DIR__ . '/../../../../.cookiejar.json', true);

        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push( Middleware::retry(self::retryDecider(), self::retryDelay()));

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://www.hover.com',
            'timeout'  => 2.0,
            'cookies' => $cookieJar,
            'handler' => $handlerStack,
        ]);
    }

    private static function retryDecider(): \Closure
    {
        return static function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) : bool {
            // Limit the number of retries to 5
            if ( $retries >= 5 ) {
                return false;
            }

            // Retry connection exceptions
            if( $exception instanceof ConnectException ) {
                return true;
            }

            // Retry on server errors
            if($response && $response->getStatusCode() >= 500) {
                return true;
            }

            return false;
        };
    }

    private static function retryDelay(): \Closure
    {
        return static function($numberOfRetries ) {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login($username, $password, $totpCode): void
    {
        $this->client->request('GET', 'signin');

        $request = $this->client->request('POST', 'signin/auth.json', [
            GuzzleHttp\RequestOptions::JSON => [
                'username' => $username,
                'password' => $password,
                'token' => null,
            ],
        ]);

        $json = json_decode((string)$request->getBody(), false, 512, JSON_THROW_ON_ERROR);
        if ($json->status === 'need_2fa' && $json->type === 'app') {
            $this->client->request('POST', 'signin/auth2.json', [
                GuzzleHttp\RequestOptions::JSON => [
                    'code' => $totpCode,
                ],
            ]);
        }
    }

    public function getDomains() : array
    {
        $response = $this->client->get('/api/domains');

        $result = json_decode((string) $response->getBody(), true);

        if ($result['succeeded'] === true) {
            return $result['domains'];
        }

        throw new \RuntimeException($result['error']);
    }

    public function getDns($domain) : array
    {
        $url = sprintf('/api/domains/%s/dns', $domain);
        $response = $this->client->get($url);

        $result = json_decode((string) $response->getBody(), true);

        if ($result['succeeded'] === true) {
            return $result['domains'][0];
        }

        throw new \RuntimeException($result['error']);
    }

    public function updateDnsEntry(string $dnsRecordId, ?string $content = null, ?int $ttl = null): bool
    {
        $record = [];

        if (is_string($content) && $content !== '') {
            $record['content'] = $content;
        }

        $url = sprintf('/api/dns/%s', $dnsRecordId);
        $response = $this->client->put($url, ['form_params' => $record]);

        $result = json_decode((string) $response->getBody(), true);

        return $result['succeeded'];
    }

    public function deleteDnsEntry($dnsRecordId) : bool
    {
        $url = sprintf('/api/dns/%s', $dnsRecordId);
        $response = $this->client->delete($url);

        $result = json_decode((string) $response->getBody(), true);

        return $result['succeeded'];
    }
}
