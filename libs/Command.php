<?php

class Command
{
    /**
     * @param string $script
     * @param string[] $arguments
     * @return void
     * @throws Exception
     */
    public static function execute(string $script, array $arguments)
    {
        if(__RUN_SHELL_SCRIPTS__) {
            if(!defined(__SHELL_USER__) || !__SHELL_USER__){
                throw new Exception("Shell User undefined. Please define __SHELL_USER__ in settings.inc.php.");
            }
            $command = "sudo -u ".__SHELL_USER__." ../bin/{$script}";
            foreach ($arguments as $argument) {
                $command .= " ".escapeshellarg($argument);
            }
            $command .= " 2>&1";

            Log::info($command);
            exec($command);
        }
    }
}