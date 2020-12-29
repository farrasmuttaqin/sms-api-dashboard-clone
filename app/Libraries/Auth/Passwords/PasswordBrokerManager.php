<?php
namespace Firstwap\SmsApiDashboard\Libraries\Auth\Passwords;

use Firstwap\SmsApiDashboard\Libraries\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Auth\Passwords\PasswordBrokerManager as PasswordBrokerManagerDefault;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PasswordBrokerManager extends PasswordBrokerManagerDefault
{
	
    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $connection = $config['connection'] ?? null;

        return new DatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $this->app['hash'],
            $config['table'],
            $key,
            $config['expire']
        );
    }
}