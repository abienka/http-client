<?php declare(strict_types=1);

namespace Abienka\HttpClient\Helper;

class XmlPathHelper
{
    const ERROR_CODE = '/result/error/code/text()';
    const ERROR_DESCRIPTION = '/result/error/description/text()';
    // company data
    const UID = '/result/firm/uid/text()';
    const NIP = '/result/firm/nip/text()';
    const NAME = '/result/firm/name/text()';
    const FIRSTNAME = '/result/firm/firstname/text()';
    const LASTNAME = '/result/firm/lastname/text()';
    const STREET = '/result/firm/street/text()';
    const STREET_NUMBER = '/result/firm/streetNumber/text()';
    const HOUSE_NUMBER = '/result/firm/houseNumber/text()';
    const CITY = '/result/firm/city/text()';
    const POST_CODE = '/result/firm/postCode/text()';
    const POST_CITY = '/result/firm/postCity/text()';
    const PHONE = '/result/firm/phone/text()';
    const EMAIL = '/result/firm/email/text()';
    const WWW = '/result/firm/www/text()';
}
