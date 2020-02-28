<?php
declare(ticks = 1);

/**
 * Blockchain CLI
 *
 * @author emi
 */

namespace Minds\Controllers\Cli;

use Minds\Cli;
use Minds\Core\Blockchain\EthPrice;
use Minds\Core\Blockchain\Services\Ethereum;
use Minds\Core\Blockchain\Purchase\Delegates\EthRate;
use Minds\Core\DeferredOps\Manager;
use Minds\Core\Di\Di;
use Minds\Core\Email\Confirmation\Manager as EmailConfirmation;
use Minds\Core\Entities\Resolver;
use Minds\Core\Events\Dispatcher;
use Minds\Entities\User;
use Minds\Interfaces;
use Minds\Core\Util\BigNumber;

class ConfirmationEmailResender extends Cli\Controller implements Interfaces\CliControllerInterface
{
    protected $ethActiveFilter;

    /**
     * Echoes $commands (or overall) help text to standard output.
     * @param  string|null $command - the command to be executed. If null, it corresponds to exec()
     * @return null
     */
    public function help($command = null)
    {
        $this->out('Usage: cli confirmationemailresender');
    }

    /**
     * Executes the default command for the controller.
     * @return mixed
     */
    public function exec()
    {
        \Minds\Core\Events\Defaults::_();

        /** @var Manager $deferredOpsManager */
        $opsManager = Di::_()->get('DeferredOps\Manager');

        /** @var EmailConfirmation $emailConfirmation */
        $emailConfirmationManager = Di::_()->get('Email\Confirmation');

        $resolver = new Resolver();

        while (true) {
            // get jobs
            $ops = $opsManager->getList([
                'job' => 'ConfirmationEmailResender',
                'next_attempt' => ['lte' => time()],
            ]);

            if (!$ops) {
                sleep(1);
                continue;
            }

            foreach ($ops as $op) {
                $resolver->setUrns($op->getEntityUrn());

                $result = $resolver->fetch();

                /** @var User $user */
                $user = null;
                if (isset($result[0])) {
                    $user = $result[0];
                }

                if (!$user) {
                    $this->out("User {$op->getEntityUrn()->getUrn()} could not be found");
                }

                // if the email is confirmed, delete the entry and continue with the next iteration
                if ($user->isEmailConfirmed()) {
                    echo "[ConfirmationEmailResender]: User email already confirmed ({$user->guid}";
                    $opsManager->delete($op);
                    continue;
                } else {
                    // try to resend the email
                    $emailConfirmationManager
                        ->setUser($user)
                        ->sendEmail();
                }

                $op->setTries($op->getTries() + 1);

                // if we've reached the max number of tries, delete the entry
                if ($op->getTries() < $op->getMaxTries()) {
                    // update with 1 more try
                    $opsManager->add($op);
                } else {
                    $opsManager->delete($op);
                }
            }

            sleep(3600); // 1 hour
        }

        $this->filterCleanup();
    }
}
