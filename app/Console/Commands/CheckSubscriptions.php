<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $users = User::where('active', 1)->get();

    foreach ($users as $user) {
        $expiryDate = $user->created_at->copy()->addYear();

        if (now()->greaterThan($expiryDate)) {
            $user->active = 0;
            $user->save();
        }
    }

    $this->info('Subscription check completed.');


    }
}
