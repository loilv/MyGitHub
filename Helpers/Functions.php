<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use OneSignal;
use App\Models\Setting;
use App\Models\Image;

class Functions
{
    /**
     * Define type for user
     * type: 0 is client; 1 is therapist
     *
     * @param
     *
     * @return Array
     */
    public static function Type()
    {
        $data = [
            'client'    => 0,
            'therapist' => 1,
        ];
        return $data;
    }


    /**
     * insert and update data
     *
     * @param status: insert or update, table name, id record if status is update , data is array(column,value)
     *
     * @return id
     */
    public static function insertUpdate($status = '', $table = '', $id = '', $datas = [])
    {
        if ($status) {
            if ($status == 'insert' && $datas && $table) {
                foreach ($datas as $name => $value) {
                    $table->$name = $value;
                }
                $table->save();
            } elseif ($status == 'update' && $datas && $table) {
                $table::where($table->getKeyName(), $id)->update($datas);
            }
            return $table->id;
        }
    }


    /**
     * Send notification
     *
     * @param $message
     * @param $users
     * @param $additionData
     */
    public static function notification($message, $users, $additionData)
    {
        OneSignal::sendNotificationUsingTags(
            $message,
            [
                ["field" => "tag", "key" => "userId", "relation" => "=", "value" => $users],
            ],
            $url = null,
            $data = $additionData,
            $buttons = null,
            $schedule = null
        );
    }


    /**
     * Send notification to group
     *
     * @param $message
     * @param $users
     * @param $additionData
     */
    public static function notificationOfGroupUsers($message, $users, $additionData)
    {
        OneSignal::sendNotificationUsingTags(
            $message,
            [
                ["field" => "tag", "key" => "typeId", "relation" => "=", "value" => $users],
            ],
            $url = null,
            $data = $additionData,
            $buttons = null,
            $schedule = null
        );
    }


    /**
     * Get date with format has 'ago' string
     *
     * @param $date
     * @param $check
     *
     * @return string
     */
    public static function getDateFormatAgo($date, $check = false)
    {
        if (!$check) {
            $date = date('Y-m-d H:i:s', strtotime($date));
        }
        $ago = 'trước';
        $date = strtotime($date);
        $diff = time() - ($date - 1);
        $times = [];
        $timeleft = [];
        $times[] = ['năm', 'năm', 31557600];
        $times[] = ['tháng', 'tháng', 2592000];
        $times[] = ['ngày', 'ngày', 86400];
        $times[] = ['giờ', 'giờ', 3600];
        $times[] = ['phút', 'phút', 60];
        $times[] = ['giây', 'giây', 1];
        foreach ($times as $timedata) {
            list($time_sing, $time_plur, $offset) = $timedata;
            if ($diff >= $offset) {
                $left = floor($diff / $offset);
                $diff -= ($left * $offset);
                if (\App::getLocale() == 'vi') {
                    $timeleft[] = "{$left} " . ($left == 1 ? $time_sing : $time_plur) . ' ' . $ago;
                }
            }
        }
        return $timeleft[0];
    }


    /**
     * Get browser
     *
     * @return array
     */
    public static function getBrowser()
    {
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //  if the reuqest is sent from terminal(eg: facebook share reques) return dummy data
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return [
                'userAgent' => $bname,
                'name'      => $bname,
                'version'   => $version,
                'platform'  => $platform,
                'pattern'   => 'test',
                'device'    => 'Computer',
            ];
        }
        $u_agent = $_SERVER['HTTP_USER_AGENT'];

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/YaBrowser/', $u_agent)) {
            $bname = 'Ya Browser';
            $ub = "YaBrowser";
        } elseif (preg_match('/OPR/', $u_agent)) {
            $bname = 'Opera Browser';
            $ub = "OPR";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }

        // finally get the correct version number
        $known = ['Version', $ub, 'other'];
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = ($matches && $matches['version']) ? $matches['version'][0] : '';
            } else {
                $version = ($matches && $matches['version']) ? $matches['version'][1] : '';
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        $windowPhone = strripos($u_agent, "Windows Phone");
        $microsoft = strripos($u_agent, "Microsoft");
        $iPod = strripos($u_agent, "iPod");
        $iPhone = strripos($u_agent, "iPhone");
        $iPad = strripos($u_agent, "iPad");
        $Android = strripos($u_agent, "Android");
        $webOS = strripos($u_agent, "webOS");
        $device = "";
        //do something with this information
        if (($iPod || $iPhone) && !$microsoft) {
            $device = "iPhone/iPod";
        } else {
            if ($iPad) {
                $device = "iPad";
            } else {
                if ($Android && !$microsoft) {
                    $device = "Android device";
                } else {
                    if ($webOS) {
                        $device = "webOS";
                    } else {
                        if ($windowPhone && $microsoft) {
                            $device = "Window Phone";
                        } else {
                            $device = "Computer";
                        }
                    }
                }
            }
        }

        return [
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern,
            'device'    => $device,
        ];
    }

    public static function getAccountKitFB($code)
    {
        $auth = file_get_contents('https://graph.accountkit.com/v1.1/access_token?grant_type=authorization_code&code=' . $code . '&access_token=AA|' . \Config::get('app.fb_app_id') . '|' . \Config::get('app.fb_kit_app_secret'));
        $access = json_decode($auth, true);
        if (empty($access) || !isset($access['access_token'])) {
            return ["status" => 2, "message" => "Unable to verify the phone number."];
        }
        //App scret proof key Ref : https://developers.facebook.com/docs/graph-api/securing-requests
        $appsecret_proof = hash_hmac('sha256', $access['access_token'], \Config::get('app.fb_kit_app_secret'));

        //echo 'https://graph.accountkit.com/v1.1/me/?access_token='. $access['access_token'];
        $url = 'https://graph.accountkit.com/v1.1/me/?access_token=' . $access['access_token'] . '&appsecret_proof=' . $appsecret_proof;
        $info = self::getCurl($url);
        if (empty($info) || !isset($info['phone']) || isset($info['error'])) {
            return ["status" => 2, "message" => "Unable to verify the phone number."];
        }

        return $info;
    }

    public static function getCurl($url)
    {
        $ch = curl_init();
        // Set query data here with the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, '4');
        $resp = trim(curl_exec($ch));
        curl_close($ch);
        $info = json_decode($resp, true);
        return $info;
    }

    /**
     *
     * My account
     *
     */
    public static function myAccount()
    {
        $user = \Auth::user();
        if ($user->getImage && $user->getImage->path && $user->getImage->name) {
            if (file_exists(public_path('assets/uploads/' . $user->getImage->path . '/' . $user->getImage->name))) {
                $user->image = asset('assets/uploads/' . $user->getImage->path . '/' . $user->getImage->name);
            } else {
                $user->image = asset('frontend/images/image-default.png');
            }
        } else {
            $user->image = asset('frontend/images/image-default.png');
        }
        return $user;
    }

    public static function getSharefacebook()
    {
        $title = 'CityZEN';
        $description = 'Bạn nhận được mã giảm giá 20% đơn hàng để sử dụng dịch vụ spa và mátsa tại CityZen! Hăy tải ứng dụng ngay tại [*******], đăng ký, và nhập mã [******] vào ô “Nhập mã khuyến mãi” để bắt đầu sử dụng dịch vụ.';
        $link = \Config::get('app.link_app_ios');
        $tagSocial = Functions::generaterTagSocial('', $title, $description, $link);
        return $tagSocial;
    }

    /**
     *
     * generater Tag Social
     *
     */
    public static function generaterTagSocial($image = '', $meta_title, $meta_description, $url)
    {
        $title = preg_replace('/\s+/', ' ', ucwords(html_entity_decode($meta_title)));
        $description = preg_replace('/\s+/', ' ', ucwords(html_entity_decode($meta_description)));
        $tags = "<meta property=\"og:title\" content=\"" . $title . "\" />\n<meta property=\"og:description\" content=\"" . $description . "\" />\n<meta property=\"og:type\" content=\"article\" />\n<meta property=\"og:url\" content=\"" . $url . "\" />\n<meta property=\"og:image\" content=\"$image?" . time() . "\" />\n";
        return $tags;
    }

    public static function getSetting()
    {
        $setting['title'] = 'Settings';
        $setting = Setting::select('*')->first();
        if (!empty($setting)) {

            $banner_home = Image::find($setting->banner_home);
            $home_name = ($banner_home) ? $banner_home->name : '';
            $home_path = ($banner_home) ? $banner_home->path : '';
            $setting->banner_home = request()->root() . '/assets/uploads/' . $home_path . '/' . $home_name;

            $banner_home_phone = Image::find($setting->banner_home_phone);
            $home_phone_name = ($banner_home_phone) ? $banner_home_phone->name : '';
            $home_phone_path = ($banner_home_phone) ? $banner_home_phone->path : '';
            $setting->banner_home_phone = request()->root() . '/assets/uploads/' . $home_phone_path . '/' . $home_phone_name;

            $banner_page = Image::find($setting->banner_page);
            $page_name = ($banner_page) ? $banner_page->name : '';
            $page_path = ($banner_page) ? $banner_page->path : '';
            $setting->banner_page = request()->root() . '/assets/uploads/' . $page_path . '/' . $page_name;

            $logo_header = Image::find($setting->logo_header);
            $header_name = ($logo_header) ? $logo_header->name : '';
            $header_path = ($logo_header) ? $logo_header->path : '';
            $setting->logo_header = request()->root() . '/assets/uploads/' . $header_path . '/' . $header_name;

            $logo_footer = Image::find($setting->logo_footer);
            $footer_name = ($logo_footer) ? $logo_footer->name : '';
            $footer_path = ($logo_footer) ? $logo_footer->path : '';
            $setting->logo_footer = request()->root() . '/assets/uploads/' . $footer_path . '/' . $footer_name;

            $favicon = Image::find($setting->favicon);
            $favicon_name = ($favicon) ? $favicon->name : '';
            $favicon_path = ($favicon) ? $favicon->path : '';
            $setting->favicon = request()->root() . '/assets/uploads/' . $favicon_path . '/' . $favicon_name;


        }

        return $setting;
    }

    public static function convertTranslate($slug)
    {
        $arr_folder = [
            'home'           => 'Home',
            'user'           => 'User',
            'gift'           => 'Gift',
            'booking'        => 'Booking',
            'therapist'      => 'Therapist',
            'help'           => 'Help',
            'company'        => 'Company',
            'servicePackage' => 'Service Package',
        ];
        return $arr_folder[$slug];
    }

    public static function allPageTranslate()
    {
        $array = [
            'home'           => 'Home',
            'user'           => 'User',
            'gift'           => 'Gift',
            'booking'        => 'Booking',
            'therapist'      => 'Therapist',
            'help'           => 'Help',
            'company'        => 'Company',
            'servicePackage' => 'Service Package',
        ];

        return $array;
    }

    public static function linkLanguage($locale = '')
    {

        $url = \URL::full();
        if (substr_count($url, '?') > 0 && substr_count($url, '/?') <= 0) {
            $url = str_replace("?", "/?", $url);
        }

        $domain = request()->root();
        $lang = \Request::segment(1);
        $segment_2 = \Request::segment(2);
        if ($locale == '') {
            if ($lang != 'en') {
                $convert = '';
                $url = str_replace($domain . '/' . $lang, $domain . '/' . $convert . $lang, $url);
            } elseif ($lang == null || $lang == 'en') {
                $convert = '';
                $url = str_replace($domain . '/' . $lang, $domain . $convert, $url);
            }
        } else {
            if ($locale == 'en') {
                if ($lang == null) {
                    $url = $domain . '/' . $locale;
                } elseif ($lang != 'en') {
                    $convert = '';
                    $lang = 'en';
                    $url = str_replace($domain, $domain . '/' . $lang . $convert, $url);
                } elseif ($lang == 'en') {
                    $convert = '';
                    if ($segment_2) {
                        $lang .= 'en';
                    }
                    $url = str_replace($domain . '/' . $lang, $domain . '/' . $lang . '/' . $convert, $url);
                }
            }
        }
        return $url;
    }

    /**
    * Get data translate
    * @param data , table name
    * @return data translate
    *
    */
    public static function getDataLangEn($datas = '', $table = '') {
        $count_data = count($datas);
        $lang = \App::getLocale();
        if ($lang == 'en') {
            $results = DB::select(DB::raw("select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='" . \DB::getTablePrefix() . "$table' AND COLUMN_NAME LIKE '%en'"));
            foreach ($results as $name) {
                if ($datas) {
                    foreach ($datas as $key => $item) {
                        $name_en = $name->COLUMN_NAME;
                        $name_vi = str_replace('_en', '', $name_en);
                        if ($count_data == 1) {
                            if ($datas[0]) {
                                if (isset($datas[0]->$name_en))
                                    $datas[0]->$name_vi = $datas[0]->$name_en;
                            }else {
                                if (isset($datas->$name_en))
                                    $datas->$name_vi = $datas->$name_en;
                            }
                        }else {
                            if (isset($datas[$key]->$name_en))
                                $datas[$key]->$name_vi = $datas[$key]->$name_en;
                        }
                    }
                }
            }
            return $datas;
        }else {
            return $datas;
        }
    }
}
