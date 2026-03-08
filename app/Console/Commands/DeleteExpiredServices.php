<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use Carbon\Carbon;

class DeleteExpiredServices extends Command
{
    protected $signature = 'services:delete-expired';
    protected $description = 'Delete services whose expires_at has passed';

   public function handle()
{
    Service::where('status', 'pending')
           ->where('expires_at', '<', Carbon::now())
           ->delete();
}
}
