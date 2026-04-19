<?php

namespace App\Console\Commands\Ai;

use App\Modules\Security\Models\LoginLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportTrainingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:export-training-data {--output=login_data.csv : The output filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export normal login data (success) to CSV for AI model training';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data export for AI training...');

        $filename = $this->option('output');
        $filePath = storage_path('app/' . $filename);

        // Header CSV sesuai dengan DatasetSchema di FastAPI
        $headers = [
            'ip_risk_score',
            'is_vpn',
            'is_new_device',
            'is_new_country',
            'login_hour',
            'failed_attempts',
            'request_speed',
            'device_trust_score'
        ];

        $handle = fopen($filePath, 'w');
        fputcsv($handle, $headers);

        // Ambil hanya login sukses (asumsi data normal) yang memiliki payload input
        $count = 0;
        LoginLog::where('status', LoginLog::STATUS_SUCCESS)
            ->whereNotNull('ai_response_raw')
            ->chunk(100, function ($logs) use ($handle, &$count) {
                foreach ($logs as $log) {
                    $inputs = $log->ai_response_raw['_inputs'] ?? null;
                    
                    if ($inputs) {
                        fputcsv($handle, [
                            $inputs['ip_risk_score'] ?? 0,
                            $inputs['is_vpn'] ?? 0,
                            $inputs['is_new_device'] ?? 0,
                            $inputs['is_new_country'] ?? 0,
                            $inputs['login_hour'] ?? 0,
                            $inputs['failed_attempts'] ?? 0,
                            $inputs['request_speed'] ?? 0,
                            $inputs['device_trust_score'] ?? 0,
                        ]);
                        $count++;
                    }
                }
            });

        fclose($handle);

        $this->info("Export completed! {$count} rows exported to: {$filePath}");
        $this->comment("You can now move this file to the ai-security/data/ directory for training.");
    }
}
