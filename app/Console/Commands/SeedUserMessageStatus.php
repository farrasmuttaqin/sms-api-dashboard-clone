<?php

namespace Firstwap\SmsApiDashboard\Console\Commands;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Message;
use Illuminate\Console\Command;

class SeedUserMessageStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed new message content for sms api dashboard';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->signature = $this->getSignature();

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (env('APP_ENV') === 'production') {
            $this->error('Hey, You are running in production mode. You are not allowing to seed the message');

            return;
        }

        $data = [];

        $count = $this->option('count');
        $messageCount = $this->option('message_count') ?? 1;
        $date = Carbon::parse($this->option('date'));

        if ($messageCount > 1) {
            $prefix = "This is message content with length $messageCount SMS :";
            $data['message_content'] = $prefix.str_random(Message::GSM_7BIT_MULTIPLE_SMS * $messageCount - mb_strlen($prefix));
        }

        if($user_id = $this->option('user_id')){
            if($api = ApiUser::where('user_name', $user_id)->first()){
                $data['user_id'] = $api->user_name;
                $data['user_id_number'] = $api->user_id;

                $senders = \DB::connection('mysql_sms_api')
                            ->table('SENDER')
                            ->select('user_id','sender_name','sender_id')
                            ->get()
                            ->groupBy('user_id')
                            ->all();

                if(isset($senders[$api->user_id])){
                    $data['sender_id'] = $senders[$api->user_id]->random()->sender_id;
                    $data['sender'] = $senders[$api->user_id]->random()->sender_name;
                }

            }else{
                $this->info('User Id '.$user_id.' Not Found.');
            }
        }

        $bar = $this->output->createProgressBar($count);

        for ($b=0; $b < ceil($count/1000); $b++) {
            $batch = [];
            $sisa = $count - 1000 * $b;
            for ($i=0; $i < 1000 && $i < $sisa; $i++) {
                $data['send_datetime'] = $date->addSeconds(1)->toDateTimeString();
                $data['message_id'] = '0GPI' . $data['send_datetime'] . '.000.' . str_random(5);
                $batch[] = factory(Message::class)->make($data)->getAttributes();
                $bar->advance();
            }

            Message::insert($batch);
        }

        $bar->finish();

        $this->info(PHP_EOL.PHP_EOL.'Success Generate Messages.'.PHP_EOL);
    }


    protected function getSignature()
    {
        $date = Carbon::parse('-90 days midnight')->toDateTimeString();

        return "seed:message
                {--count=1000 : Total messages will be generated}
                {--date=$date : Start message send datetime}
                {--user_id=? : Input API Username, example: PEPTrial}
                {--message_count=? : Message length, example: 1}";
    }
}
