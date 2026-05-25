protected function schedule(Schedule $schedule)
{
    $schedule->command('telegram:daily')->dailyAt('09:00');
    $schedule->command('telegram:daily')->dailyAt('18:00');
}