<?php

namespace Stn\RateLimitingBundle\Redis\Command;

use Predis\Command\ScriptCommand;

/**
 * Custom Redis command for counting request
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
class CountRequestCommand extends ScriptCommand
{
    /**
     * {@inheritdoc}
     */
    public function getKeysCount()
    {
        return 1;
    }

    /**
     * Gets the body of a Lua script.
     *
     * This custom command has 3 arguments: key, limit and duration.
     * 1. key: The request identifier which contains information of IP and route
     * 2. ttl: Time to live
     *
     * This custom command will return times of request which has been accessed as an integer
     *
     * @todo Check the return value type
     * @return string
     */
    public function getScript()
    {
        return <<<LUA
local key = KEYS[1]
local ttl = ARGV[1]
local times = redis.call('incr', key)

if times == 1 then
    redis.call('expire', key, ttl)
end

return times
LUA;
    }
}
