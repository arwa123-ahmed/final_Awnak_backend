<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceMatch;
use App\Models\Service;
use Carbon\Carbon;

class FinishServiceMatches extends Command
{
    protected $signature = 'service-matches:finish';
    protected $description = 'Check ServiceMatches and finish the ones whose end_time passed';

 public function handle()
{
    // جلب كل الـ matches المقبولة
    $matches = ServiceMatch::where('status', 'accepted')
        ->with('service') // نحضر الـ Service المرتبطة
        ->get();

    foreach ($matches as $match) {
        // إذا وقت الخدمة انتهى
        if ($match->service->end_time <= now()) {
            $match->status = 'timeFinished';
            $match->save();

        }
    }

    $this->info('Checked ' . $matches->count() . ' matches.');


     $matches = ServiceMatch::where('status', 'delayed')
        ->with('service') // نحضر الـ Service المرتبطة
        ->get();

    foreach ($matches as $match) {
        // إذا وقت التأخير انتهى
        if ($match->service->end_time <= now()) {
            $match->status = 'completed';
            $match->save();

            $service = Service::where('id', $match->service_id)->first();
            $service->status = "Finished";
            $service->update();
            
        }
    }

    $this->info('Checked ' . $matches->count() . ' matches.');
}


}