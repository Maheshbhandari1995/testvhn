<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
class LowCreditAlertEmail extends Mailable
{
    public $clientData;
    public $companyLogoUrl;
    public $companyName;

    public function __construct($clientData)
    {
        $this->clientData = $clientData;
        $this->companyLogoUrl = asset('assets/img/edas-logo-light.png');
        $this->companyName = 'EDAS';
    }

    public function build()
    {
        $low_credit_limit = Config::get('custom.low_credit_limit');

        return $this->view('emails.low_credit_alert')
            ->subject('Low Credit Alert')
            ->with([
                'low_credit_limit' => $low_credit_limit
            ]);
    }
}

class CommonController extends Controller
{

    public function lowCreditAlert()
    {
        $data = Company::whereIn('del_status', [1,0])->whereIn('status', [1,0])->latest()->get();

        if($data)
        {
            $mail_from_address  = env('MAIL_FROM_ADDRESS', 0); // Set a default value of 0 if the parameter is not found
            $low_credit_limit   = Config::get('custom.low_credit_limit');
            foreach($data as $k => $clientData)
            {
                $available_credit   = $clientData->max_count;
                
                if($available_credit <= $low_credit_limit)
                {
                    $to = $clientData->email;
                    $recipientName = $clientData->email;
                    Mail::to(array($to=>$recipientName))->send(new LowCreditAlertEmail($clientData));
                    echo "Email sent successfully!";
                    //die;
                }
            }
        }
    }
}