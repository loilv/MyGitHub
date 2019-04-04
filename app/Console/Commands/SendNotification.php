<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\OrderPackage;
use App\Models\SellProduct;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Constants\OrderPackageCode;
use App\Constants\NotificationCode;
use App\Models\EmailTemplate;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();
        $send_notifi_order = OrderPackage::orderBy('id', 'desc')->get();
        if (count($send_notifi_order)) {
            $mail_package = EmailTemplate::where('slug', 'thong-bao-goi-dich-vu-sap-het-han')->first();
            foreach ($send_notifi_order as $key => $value) {
                $type = $value->type;
                $params = [
                    'sender'   => env('MAIL_USERNAME'),
                    'receiver' => $value->orderToUser->name ? 'ngadt@vinsofts.net' : 'tupa@vinsofts.net',
                    'content'  => $value->name . $mail_package->body . $value->limit,
                    'title'    => $mail_package->title,
                    'subject'  => $mail_package->subject,
                ];
                if ($type == OrderPackageCode::TYPE_NEWS) {
                    $date_end = Carbon::parse($value->limit);
                    $diff = $date_end->diffInDays($now);
                    if ($diff == NotificationCode::DATE_END && $diff > NotificationCode::NUMBER_ZERO) {
                        $data = [
                            'message'   => $value->name . ' của bạn sắp hết hạn vào ngày ' . $value->limit,
                            'user_id'   => $value->user_id,
                            'common_id' => $value->id,
                            'type'      => 'package',
                            'status'    => NotificationCode::NUMBER_ZERO,
                        ];
                        Notification::create($data);
                        \Func::sendMail($params);
                    } else {
                        if ($diff == NotificationCode::NUMBER_ZERO) {
                            $value->status = OrderPackageCode::STATUS_ORDER_EXPIRED;
                            $value->save();
                        }
                    }
                }
            }
        }

        $send_notifi_product = SellProduct::orderBy('id', 'desc')->get();
        if (count($send_notifi_product)) {
            $mail_product = EmailTemplate::where('slug', 'thong-bao-san-pham-sap-het-han')->first();
            foreach ($send_notifi_product as $key => $value) {
                $check = $value->send_mail;
                $params = [
                    'sender'   => env('MAIL_USERNAME'),
                    'receiver' => $value->sellToUser->name ? 'ngadt@vinsofts.net' : 'tupa@vinsofts.net',
                    'content'  => 'Sản phẩm ' . $value->name . $mail_product->body . $value->time_end,
                    'title'    => $mail_product->title,
                    'subject'  => $mail_product->subject,
                ];
                $date_end = Carbon::parse($value->time_end);
                $diff = $date_end->diffInDays($now);
                if ($diff <= NotificationCode::DATE_END && $diff > NotificationCode::NUMBER_ZERO
                    && $check == NotificationCode::NUMBER_ZERO) {
                    $data = [
                        'message'   => 'Sản phẩm ' . $value->name . ' của bạn sắp hết hạn vào ngày ' . $value->time_end,
                        'user_id'   => $value->user_id,
                        'common_id' => $value->id,
                        'type'      => 'sellproduct',
                        'status'    => NotificationCode::NUMBER_ZERO,
                    ];
                    Notification::create($data);
                    \Func::sendMail($params);
                    $value->send_mail = NotificationCode::NUMBER_ONE;
                    $value->save();
                }
            }
        }
    }
}
