<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAiRiskApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new AI Risk API Key and sync it between Laravel and FastAPI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newKey = Str::random(64);
        $this->info("Generated Key: $newKey");

        // 1. Update Laravel .env
        $laravelEnvPath = base_path('.env');
        if (!$this->updateEnvFile($laravelEnvPath, 'AI_RISK_API_KEY', $newKey)) {
            $this->error("Failed to update Laravel .env");
            return 1;
        }
        $this->comment("Updated Laravel .env: AI_RISK_API_KEY");

        // 2. Update FastAPI .env
        // FastAPI project is at the same level as Laravel project in the workspace
        $fastApiEnvPath = realpath(base_path('../ai-security/.env'));
        
        if ($fastApiEnvPath && file_exists($fastApiEnvPath)) {
            if (!$this->updateEnvFile($fastApiEnvPath, 'API_KEY', $newKey)) {
                $this->error("Failed to update FastAPI .env");
                return 1;
            }
            $this->comment("Updated FastAPI .env: API_KEY");
        } else {
            $this->warn("FastAPI .env not found at $fastApiEnvPath. Skipping...");
        }

        $this->success("AI Risk API Key successfully generated and synchronized!");
        $this->info("");
        $this->info("NOTE: You might need to restart your Docker containers for the changes to take effect.");
        $this->info("Run: docker-compose restart fastapi-risk");

        return 0;
    }

    /**
     * Helper to update .env file.
     */
    private function updateEnvFile(string $path, string $key, string $value): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        $pattern = "/^" . preg_quote($key, '/') . "=.*/m";

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, "$key=\"$value\"", $content);
        } else {
            // If the key doesn't exist, append it (optional, but safer to assume it exists based on project setup)
            $newContent = $content . "\n$key=\"$value\"\n";
        }

        return file_put_contents($path, $newContent) !== false;
    }

    /**
     * Helper for success message style.
     */
    private function success(string $message): void
    {
        $this->line("<info>SUCCESS:</info> $message");
    }
}
