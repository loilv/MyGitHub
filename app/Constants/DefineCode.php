<?php

namespace App\Constants;

/**
 * Class HttpResponse
 *
 * @package App\Constants
 */
class DefineCode
{
    const STATUS_CODE_ACTIVE = 1;
    const STATUS_CODE_INACTIVE = 2;
    const GENDER_CODE_MALE = 0;
    const GENDER_CODE_FEMALE = 1;
    const ROLE_ADMIN = 0;
    const ROLE_MEMBER = 1;
    const ROLE_INVESTOR = 2;
    const ROLE_COMPANY = 3;
    const MALE = 0;
    const FEMALE = 1;
    const NORMAL = 0;
    const VIP = 1;
    const DEFAULT_IMAGE = 0;
    const VIP_DEFAULT = 0;
    const TYPE_USER_VIP = 1;
    const TYPE_USER_NEMBER = 0;

    const NEW = 0;
    const OLD = 1;

    const FACEBOOK = 1;
    const GOOGLE = 2;
    const APP = 0;
    const TYPE_IMAGE_ERROR = 0;

    const STATUS_SELL_NEW = 0;
    const STATUS_SELL_LIQUIDATION = 1;
    const STATUS_SELL_VIOLATE = 2;

    const TYPE_CATEGORY_PRODUCT = 0;
    const TYPE_CATEGORY_NEWS = 1;
    const NUMBER_ZERO = 0;

    const NAME_PACKAGE_INFO_PROJECT = 0;
    const NAME_PACKAGE_INFO_BIDDING = 1;
    const NAME_PACKAGE_PRODUCT = 2;
    const TYPE_PACKAGE_MONTH = 0;
    const TYPE_PACKAGE_PRODUCT = 1;

    const STATUS_ORDER_UNPAID = 0;
    const STATUS_ORDER_PAID = 1;
    const STATUS_ORDER_EXPIRED = 2;

    const FIELD_GOODS = 0;
    const FIELD_BUILD = 1;
    const FIELD_ADVISORY = 2;
    const FIELD_UN_ADVISORY = 3;
    const FIELD_MIXTURE = 4;

    const NEW_PROJECT_NOT_READ =0;
    const NEW_PROJECT_READ =1;

    const FOLLOW = 1;
    const NO_FOLLOW = 0;

    const NOT_VERIFICATION = 0;
    const VERIFICATION = 1;
}
