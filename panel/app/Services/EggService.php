<?php

namespace App\Services;

use App\Models\Egg;

class EggService
{
    /**
     * Parse the startup command by replacing placeholders with variable values.
     *
     * @param Egg $egg
     * @param array $variables Key-value pair of variable env names and their values
     * @return string
     */
    public function parseStartupCommand(Egg $egg, array $variables): string
    {
        $command = $egg->startup_command; 
        // Example: java -Xms128M -jar {{SERVER_JARFILE}}

        foreach ($variables as $key => $value) {
            // Security: In a real app, validate regex rules from EggVariables table here.
            
            // Replace {{VARIABLE}} with value
            $command = str_replace("{{" . $key . "}}", $value, $command);
        }
        
        // Cleanup: Replace any remaining {{VAR}} with empty string or default?
        // For now, leave strictly.
        
        return $command;
    }
}
