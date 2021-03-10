<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Notifications\SendOrderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class ChargeSucceededJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        $charge = $this->webhookCall->payload['data']['object'];
        $user = User::where('stripe_id', $charge['customer'])->first();

        if ($user) {
            $order = Order::where('user_id', $user->id)->whereNull('paid_at')->latest()->first();
            if ($order) {
                $order->update(['paid_at' => now()]);
                $user->notify(new SendOrderNotification());
                $order->update(['delivered_at' => now()]);
            }
        }
    }
}
