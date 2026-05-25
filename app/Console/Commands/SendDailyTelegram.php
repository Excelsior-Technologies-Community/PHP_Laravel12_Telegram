<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\AdvancedTelegramNotification;

class SendDailyTelegram extends Command
{
    protected $signature = 'telegram:daily';
    protected $description = 'Send daily Telegram notification';

    public function handle()
    {
        $users = User::all(); // Or get specific users
        
        $message = "🌟 Good Morning!\n\nThis is your daily update from Laravel.\nHave a great day!";
        
        foreach ($users as $user) {
            try {
                $user->notify(new AdvancedTelegramNotification(
                    $message,
                    'Daily Update',
                    'info'
                ));
                $this->info("Sent to user: {$user->id}");
            } catch (\Exception $e) {
                $this->error("Failed for user: {$user->id} - {$e->getMessage()}");
            }
        }
        
        $this->info('Daily notifications sent successfully!');
    }
}