<?php
/**
 * ConfirmationUrlDelegate
 *
 * @author edgebal
 */

namespace Minds\Core\Email\Confirmation\Delegates;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Entities\User;

class ConfirmationUrlDelegate
{
    /** @var Config */
    protected $config;

    /**
     * ConfirmationUrlDelegate constructor.
     * @param Config $config
     */
    public function __construct(
        $config = null
    ) {
        $this->config = $config ?: Di::_()->get('Config');
    }

    /**
     * @param User $user
     * @param array $params
     * @return string
     */
    public function generate(User $user, array $params = []): string
    {
        return sprintf(
            '%s?%s',
            $this->config->get('site_url'),
            http_build_query(array_merge($params, [
                'token' => $user->getEmailConfirmationToken(),
            ])),
        );
    }
}
