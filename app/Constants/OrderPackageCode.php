<?php

namespace App\Constants;

/**
 * Class HttpResponse
 *
 * @package App\Constants
 */
class OrderPackageCode
{
    const STATUS_ORDER_UNPAID = 0;
    const STATUS_ORDER_PAID = 1;
    const STATUS_ORDER_EXPIRED = 2;

    const TYPE_PRODUCT = 1;
    const TYPE_NEWS = 0;

    const PACKAGE_PROJECT = 0;
    const PACKAGE_BIDDING = 1;
    const PACKAGE_PRODUCT = 2;
}
