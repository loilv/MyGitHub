<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use OneSignal;
use App\Constants\ResponseStatusCode;
use App\Models\UserRoles;

class Functions
{

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
            } else {
                if ($status == 'update' && $datas && $table) {
                    $table::where($table->getKeyName(), $id)->update($datas);
                }
            }
            return $table->id;
        }
    }

    /*
     * Upload image
     *
     * @param: file image, path image
     *
     * @return array
     */
    public static function uploadImage($file, $path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $extend = $file->getClientOriginalExtension();
        $data['image'] = str_replace('.' . $extend, '_' . time() . '.' . $extend, $file->getClientOriginalName());
        $data['path'] = $file->move($path, $data['image']);
        return $data;
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
//                if (\App::getLocale() == 'vi') {
                $timeleft[] = "{$left} " . ($left == 1 ? $time_sing : $time_plur) . ' ' . $ago;
//                }
            }
        }
        return $timeleft[0];
    }

    /**
     * Send mail
     *
     *
     */
    public static function sendMail($param)
    {
        if ($param['receiver']) {
            \Mail::send('backend.pages.email.content-email', $param, function ($msg) use ($param) {
                $msg->from($param['sender'], 'app-pccc.vn');
                $msg->to($param['receiver']);
                $msg->subject($param['title']);
            });
        } else {
            return true;
        }
    }

    /**
     * Function paginate array
     * @param $data
     * @param $perPage
     *
     * @return LengthAwarePaginator
     */
    public static function paginateArray($data, $perPage)
    {
        $current = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($data);
        $currentPageItems = $itemCollection->slice(($current * $perPage) - $perPage, $perPage)->all();

        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);

        return $paginatedItems->getCollection();
    }
}
