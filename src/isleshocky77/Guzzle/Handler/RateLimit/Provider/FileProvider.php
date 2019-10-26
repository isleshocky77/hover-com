<?php


namespace isleshocky77\Guzzle\Handler\RateLimit\Provider;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Concat\Http\Middleware\RateLimitProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * An object which manages rate data for a rate limiter, which uses the data to
 * determine wait duration. Keeps track of:
 *
 *  - Time at which the last request was made
 *  - The allowed interval between the last and next request
 */
class FileProvider implements RateLimitProvider
{

    private $cache;

    public function __construct()
    {
        $this->cache =  new FilesystemAdapter();

    }

    /**
     * Returns when the last request was made.
     *
     * @return float|null When the last request was made.
     */
    public function getLastRequestTime(RequestInterface $request)
    {
        return $this->cache->getItem('last_request_time')->get();
    }

    /**
     * Used to set the current time as the last request time to be queried when
     * the next request is attempted.
     */
    public function setLastRequestTime(RequestInterface $request)
    {
        $lastRequestTimeItem = $this->cache->getItem('last_request_time');
        $lastRequestTimeItem->set(microtime(true));
        $this->cache->save($lastRequestTimeItem);
        return true;
    }

    /**
     * Returns what is considered the time when a given request is being made.
     *
     * @param RequestInterface $request The request being made.
     *
     * @return float Time when the given request is being made.
     */
    public function getRequestTime(RequestInterface $request)
    {
        return microtime(true);
    }

    /**
     * Returns the minimum amount of time that is required to have passed since
     * the last request was made. This value is used to determine if the current
     * request should be delayed, based on when the last request was made.
     *
     * Returns the allowed time between the last request and the next, which
     * is used to determine if a request should be delayed and by how much.
     *
     * @param RequestInterface $request The pending request.
     *
     * @return float The minimum amount of time that is required to have passed
     *               since the last request was made (in microseconds).
     */
    public function getRequestAllowance(RequestInterface $request)
    {
        // This is just an example, it's up to you to store the request
        // allowance, whether it's in a database or cache driver.
        return $this->cache->getItem('request_allowance')->get();
    }

    /**
     * Used to set the minimum amount of time that is required to pass between
     * this request and the next request.
     *
     * @param ResponseInterface $response The resolved response.
     */
    public function setRequestAllowance(ResponseInterface $response)
    {
        // Let's also assume that the response contains two headers:
        //     - ratelimit-remaining
        //     - ratelimit-window
        //
        // The first header tells us how many requests we have left in the
        // current window, the second tells us how many seconds are left in the
        // window before it expires.
//        $requests = $response->getHeader('ratelimit-remaining');
//        $seconds = $response->getHeader('ratelimit-window');

        // The allowance is therefore how much time is remaining in our window
        // divided by the number of requests we can still make. This is the
        // value we need to store to determine if a future request should be
        // delayed or not.
//        $allowance = (float)$seconds / $requests;
        $allowance = 0.5;

        // This is just an example, it's up to you to store the request
        // allowance, whether it's in a database or cache driver.
        $lastRequestTimeItem = $this->cache->getItem('request_allowance');
        $lastRequestTimeItem->set($allowance);
        $this->cache->save($lastRequestTimeItem);
    }
}
