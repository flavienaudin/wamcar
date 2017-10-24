<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
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
            'info' => '%s', // white text
            'success' => '<info>%s</info>', // green text
            'notice' => '<question>%s</question>', // black text on a cyan background
            'warning' => '<comment>%s</comment>', // yellow text
            'error' => '<error>%s</error>', // white text on a red background
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
