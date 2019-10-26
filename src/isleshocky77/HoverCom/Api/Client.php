<?php


namespace isleshocky77\HoverCom\Api;


use Concat\Http\Middleware\RateLimiter;
use GuzzleHttp\Cookie\CookieJar as CookieJarAlias;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use isleshocky77\Guzzle\Handler\RateLimit\Provider\FileProvider;

class Client
{
    private $isLoggedIn = false;

    private $client;

    public function __construct()
    {
        $cookieJar = new FileCookieJar(__DIR__ . '/../../../../.cookiejar.json', true);

        $handler = new CurlHandler();
        $handlerStack = HandlerStack::create($handler);
        $rateLimitProvider = new FileProvider();
        $handlerStack->push(new RateLimiter($rateLimitProvider));

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://www.hover.com',
            'timeout'  => 2.0,
            'cookies' => $cookieJar,
            'handler' => $handlerStack,
        ]);
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
