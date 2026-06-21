<?php

namespace App\Console\Commands;

use App\Services\CampaignProcessorService;
use Illuminate\Console\Command;

class ProcessCampaignsCommand extends Command
{
    protected $signature = 'campaigns:process';

    protected $description = 'Process due campaigns and send next batch of emails';

    public function handle(CampaignProcessorService $processor): int
    {
        $count = $processor->processDueCampaigns();

        $this->info("Processed {$count} campaign batch(es).");

        return self::SUCCESS;
    }
}
