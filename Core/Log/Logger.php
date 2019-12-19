<?php
/**
 * Logger
 *
 * @author edgebal
 */

namespace Minds\Core\Log;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * Logger constructor.
     * @param string $channel
     * @param array $options
     */
    public function __construct(string $channel = 'Minds', array $options = [])
    {
        $options = array_merge([
            'isProduction' => true,
            'devToolsLogger' => '',
        ], $options);

        $handlers = [];

        $errorLogHandler = new ErrorLogHandler(
            ErrorLogHandler::OPERATING_SYSTEM,
            $options['isProduction'] ? MonologLogger::INFO : MonologLogger::DEBUG
        );

        $errorLogHandler
            ->setFormatter(new LineFormatter(
                "%channel%.%level_name%: %message% %context% %extra%\n",
                'c',
                false,
                true
            ));

        $handlers[] = $errorLogHandler;

        if (!$options['isProduction']) {
            // Extra handlers for Development Mode

            switch ($options['devToolsLogger']) {
                case 'firephp':
                    $handlers[] = new FirePHPHandler();
                    break;

                case 'chromelogger':
                    $handlers[] = new ChromePHPHandler();
                    break;

                case 'phpconsole':
                    try {
                        $handlers[] = new PHPConsoleHandler();
                    } catch (Exception $exception) {
                        // If the server-side vendor package is not installed, ignore any warnings.
                    }
                    break;
            }
        }

        // Create Monolog instance

        parent::__construct($channel, $handlers);
    }
}
