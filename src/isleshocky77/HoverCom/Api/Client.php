<?php


namespace isleshocky77\HoverCom\Api;


use GuzzleHttp\Cookie\CookieJar as CookieJarAlias;
use GuzzleHttp\Cookie\FileCookieJar;

class Client
{
    private $isLoggedIn = false;

    private $client;

    public function __construct()
    {
        $cookieJar = new FileCookieJar(__DIR__ . '/../../../../.cookiejar.json', true);
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://www.hover.com',
            'timeout'  => 2.0,
            'cookies' => $cookieJar,
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
    }

    public function getDns($domain)
    {
        $url = sprintf('/api/domains/%s/dns', $domain);
        $response = $this->client->get($url);

        $result = json_decode((string) $response->getBody(), true);

        if ($result['succeeded'] === true) {
            return $result['domains'][0];
        }
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
