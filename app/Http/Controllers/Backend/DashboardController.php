<?php

namespace App\Http\Controllers\Backend;

use App\Constants\DefineCode;
use App\Constants\OrderPackageCode;
use App\Http\Controllers\Controller;
use App\Models\NewsBidding;
use App\Models\NewsProject;
use App\Models\OrderPackage;
use App\Models\User;
use DB;

class DashboardController extends Controller
{
    /**
     * function return view
     *
     * @return view
     * */
    public function index()
    {
        $title = 'DashBoard';
        $user['user'] = User::where('role', DefineCode::ROLE_MEMBER)->count();
        $user['investor'] = User::where('role', DefineCode::ROLE_INVESTOR)->count();
        $news['project'] = NewsProject::get()->count();
        $news['bidding'] = NewsBidding::get()->count();

        //
        $numberMonth = 12;
        $i = 0;
        $arrayMonth = [];
        while ($i < $numberMonth) {
            $month = strtotime(date("d-m-Y") . "-" . $i . "month");
            $month = date("m-Y", $month);
            array_push($arrayMonth, $month);
            $i += 1;
        }
        $arrayMonth = array_reverse($arrayMonth);
        $arrayProject = $this->sumSellPackageProject($arrayMonth);
        $arrayBidding = $this->sumSellPackageBidding($arrayMonth);
        $arrayProduct = $this->sumSellPackageProduct($arrayMonth);
//        dd($arrayMonth, $arrayProject, $arrayBidding, $arrayProduct);
        //
        $len = count($arrayProject);
        $data = [];
        for ($i = 0; $i < $len; $i++) {
            $cc = new \stdClass();
            $cc->y = $arrayMonth[$i];
            $cc->a = (int)$arrayProject[$i];
            $cc->b = (int)$arrayBidding[$i];
            $cc->c = (int)$arrayProduct[$i];
            $data[] = $cc;
        }
//        dd($data);
        return view('backend.pages.dashboard.index', compact('title', 'user', 'news', 'data'));
    }

    /**
     *
     * Get total user buy package project by month
     *
     * @param $listMonth
     *
     * @return array
     */
    public function sumSellPackageProject($listMonth)
    {
        $data = [];
        $monthStop = strtotime(date("d-m-Y") . "+1 month");
        $monthStop = date("m-Y", $monthStop);
        array_push($listMonth, $monthStop);
        $lenListMonth = count($listMonth);
        foreach ($listMonth as $key => $month) {
            if ($key == $lenListMonth - 1) {
                break;
            }

            $date_from = date("Y-m-d 00:00:00", strtotime("1-" . $month));
            $date_to = date("Y-m-d 00:00:00", strtotime('1-' . $listMonth[$key + 1]));
            $total = OrderPackage::where('package_id', OrderPackageCode::PACKAGE_PROJECT)
                         ->select(DB::raw('COUNT(user_id) AS total_user'))
                         ->whereBetween('created_at', [$date_from, $date_to])
                         ->first()
                         ->toArray()['total_user'];
            if ($total == null) {
                $total = 0;
            }
            array_push($data, $total);
        }
        return $data;
    }

    /**
     * Get total user buy package bidding by month
     *
     * @param $listMonth
     *
     * @return \Illuminate\Http\Response
     */
    public function sumSellPackageBidding($listMonth)
    {
        $data = [];
        $monthStop = strtotime(date("d-m-Y") . "+1 month");
        $monthStop = date("m-Y", $monthStop);
        array_push($listMonth, $monthStop);
        $lenListMonth = count($listMonth);
        foreach ($listMonth as $key => $month) {
            if ($key == $lenListMonth - 1) {
                break;
            }

            $date_from = date("Y-m-d 00:00:00", strtotime("1-" . $month));
            $date_to = date("Y-m-d 00:00:00", strtotime('1-' . $listMonth[$key + 1]));
            $total = OrderPackage::where('package_id', OrderPackageCode::PACKAGE_BIDDING)
                         ->select(DB::raw('COUNT(user_id) AS total_user'))
                         ->whereBetween('created_at', [$date_from, $date_to])
                         ->first()
                         ->toArray()['total_user'];
            if ($total == null) {
                $total = 0;
            }
            array_push($data, $total);
        }
        return $data;
    }

    /**
     *
     * Get total user buy package product by month
     *
     * @param $listMonth
     *
     * @return array
     */
    public function sumSellPackageProduct($listMonth)
    {
        $data = [];
        $monthStop = strtotime(date("d-m-Y") . "+1 month");
        $monthStop = date("m-Y", $monthStop);
        array_push($listMonth, $monthStop);
        $lenListMonth = count($listMonth);
        foreach ($listMonth as $key => $month) {
            if ($key == $lenListMonth - 1) {
                break;
            }

            $date_from = date("Y-m-d 00:00:00", strtotime("1-" . $month));
            $date_to = date("Y-m-d 00:00:00", strtotime('1-' . $listMonth[$key + 1]));
            $total = OrderPackage::where('package_id', OrderPackageCode::PACKAGE_PRODUCT)
                         ->select(DB::raw('COUNT(user_id) AS total_user'))
                         ->whereBetween('created_at', [$date_from, $date_to])
                         ->first()
                         ->toArray()['total_user'];
            if ($total == null) {
                $total = 0;
            }
            array_push($data, $total);
        }
        return $data;
    }

    public function updateAdmin()
    {
        return view('backend.pages.dashboard.admin');
    }
}
