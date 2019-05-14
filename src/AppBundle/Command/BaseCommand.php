<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    /** Configure command */
    const DATE_FORMAT = "d-m-Y H:i:s";

    const INFO = "info";
    const SUCCESS = "success";
    const NOTICE = "notice";
    const WARNING = "warning";
    const ERROR = "error";

    protected $output;

    /**
     * @param string $level
     * @param string $message
     *
     * Log command data in console output and logfile
     *
     * Level list
     * *****************
     * success : successful event
     * info : interesting event
     * notice : interesting but noticeable event
     * warning : unexpected event, with no impact for the user
     * error : runtime error
     *
     */
    protected function log($level, $message, $noNewline = false)
    {
        $colorTemplates = [
            self::INFO => '%s', // white text
            self::SUCCESS => '<info>%s</info>', // green text
            self::NOTICE => '<question>%s</question>', // black text on a cyan background
            self::WARNING => '<comment>%s</comment>', // yellow text
            self::ERROR => '<error>%s</error>', // white text on a red background
        ];
        $method = $noNewline ? 'write' : 'writeln';
        $this->output->$method(sprintf($colorTemplates[$level], $message));
    }

    /**
     * Create a new line lin logs
     */
    protected function logCRLF()
    {
        $this->output->writeln('');
    }
}
