<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Mail;
use Hash;
use App\User;

class UserUpdateEmailSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user; 

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user; 
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $useremail = $this->user->email;
        $adminEmail = config("constants.support_mail"); 
        $site_name = config("constants.site_name"); 
        if(!empty($this->user)) {

            Mail::send('email.user-details', ['user'=>$this->user, 'sitename'=>$site_name], function ($m) use ($adminEmail, $site_name, $useremail) {

                $m->from($adminEmail, $site_name.' - '.' Profile Details Updated');

                $m->to($useremail)->subject($site_name.' - '.' Profile Details Updated');
            });

        }  
        
    }
}