<?php


namespace isleshocky77\HoverCom\Api;


use Concat\Http\Middleware\RateLimiter;
use GuzzleHttp\Cookie\CookieJar as CookieJarAlias;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use isleshocky77\Guzzle\Handler\RateLimit\Provider\FileProvider;

class Client
{
    private $isLoggedIn = false;

    private $client;

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

    private static function retryDecider() {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // Limit the number of retries to 5
            if ( $retries >= 5 ) {
                return false;
            }

            // Retry connection exceptions
            if( $exception instanceof ConnectException ) {
                return true;
            }

            if( $response ) {
                // Retry on server errors
                if( $response->getStatusCode() >= 500 ) {
                    return true;
                }
            }

            return false;
        };
    }

    private static function retryDelay() {
        return function( $numberOfRetries ) {
            return 1000 * $numberOfRetries;
        };
    }

    public function login($username, $password)
    {
        $this->client->request('POST', '/api/login', [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        $this->isLoggedIn = true;
    }

    public function getDomains()
    {
        $jar = new FileCookieJar(__DIR__ . '/../../../..//.cookiejar.json');
        $response = $this->client->get('/api/domains');

        $result = json_decode((string) $response->getBody(), true);

        if ($result['succeeded'] === true) {
            return $result['domains'];
        }

        throw new \RuntimeException($result['error']);
    }

    public function getDns($domain)
    {
        $url = sprintf('/api/domains/%s/dns', $domain);
        $response = $this->client->get($url);

        $result = json_decode((string) $response->getBody(), true);

        if ($result['succeeded'] === true) {
            return $result['domains'][0];
        }

        throw new \RuntimeException($result['error']);
    }

    public function updateDnsEntry($dnsRecordId, $content = null, $ttl = null)
    {
        $record = [];

        if (is_string($content) && strlen($content) > 0) {
            $record['content'] = $content;
        }

        $url = sprintf('/api/dns/%s', $dnsRecordId);
        $response = $this->client->put($url, ['form_params' => $record]);

        $result = json_decode((string) $response->getBody(), true);

        return $result['succeeded'];
    }
}
