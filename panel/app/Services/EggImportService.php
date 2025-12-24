<?php

namespace App\Services;

use App\Models\Egg;
use App\Models\Nest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EggImportService
{
    /**
     * Import an egg from a JSON file.
     */
    public function import(string $filePath): Egg
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (!$data || !isset($data['name'])) {
            throw new \Exception('Invalid egg file format.');
        }

        // Find or create a default nest
        $nest = Nest::firstOrCreate(
            ['name' => 'Imported'],
            ['description' => 'Auto-imported game eggs']
        );

        // Create or update the egg
        $egg = Egg::updateOrCreate(
            ['name' => $data['name']],
            [
                'nest_id' => $nest->id,
                'uuid' => Str::uuid(),
                'author' => $data['author'] ?? 'unknown@schnuffelll.com',
                'description' => $data['description'] ?? '',
                'features' => json_encode($data['features'] ?? []),
                'docker_images' => json_encode($data['docker_images'] ?? []),
                'startup' => $data['startup'] ?? '',
                'config_files' => json_encode($data['config']['files'] ?? []),
                'config_startup' => json_encode($data['config']['startup'] ?? []),
                'config_logs' => json_encode($data['config']['logs'] ?? []),
                'config_stop' => $data['config']['stop'] ?? 'stop',
                'script_install' => $data['scripts']['installation']['script'] ?? '',
                'script_container' => $data['scripts']['installation']['container'] ?? 'alpine:3.4',
                'script_entry' => $data['scripts']['installation']['entrypoint'] ?? 'ash',
            ]
        );

        // Handle variables
        if (isset($data['variables']) && is_array($data['variables'])) {
            foreach ($data['variables'] as $var) {
                $egg->variables()->updateOrCreate(
                    ['env_variable' => $var['env_variable']],
                    [
                        'name' => $var['name'],
                        'description' => $var['description'] ?? '',
                        'default_value' => $var['default_value'] ?? '',
                        'user_viewable' => $var['user_viewable'] ?? true,
                        'user_editable' => $var['user_editable'] ?? false,
                        'rules' => $var['rules'] ?? 'required|string',
                        'sort' => 0,
                    ]
                );
            }
        }

        return $egg;
    }

    /**
     * Import all eggs from the eggs directory.
     */
    public function importAll(string $directory): array
    {
        $imported = [];
        $files = glob($directory . '/egg-*.json');

        foreach ($files as $file) {
            try {
                $egg = $this->import($file);
                $imported[] = $egg->name;
            } catch (\Exception $e) {
                logger()->error("Failed to import egg from {$file}: " . $e->getMessage());
            }
        }

        return $imported;
    }
}
