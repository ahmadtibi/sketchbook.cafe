<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\SBCTimezone;

class SBCTimezone
{
    // Construct
    public function __construct()
    {
        $method = 'SBCTimezone->__construct()';
    }

    // Function
    final public static function timezone($value,$type)
    {
        $method = 'SBCTimezone->timezone()';

        switch ($value) {
            case 1:     $var    = 'Kwajalein';
                        $text   = '(GMT-12:00) International Date Line West';
                        break;

            case 2:     $var    = 'Pacific/Midway';
                        $text   = '(GMT-11:00) Midway Island';
                        break;

            case 3:     $var    = 'Pacific/Samoa';
                        $text	= '(GMT-11:00) Samoa';
                        break;

            case 4:     $var    = 'Pacific/Honolulu';
                        $text   = '(GMT-10:00) Hawaii';
                        break;

            case 5:     $var    = 'America/Anchorage';
                        $text   = '(GMT-09:00) Alaska';
                        break;

            case 6:     $var    = 'America/Los_Angeles';
                        $text	= '(GMT-08:00) Pacific Time (US &amp; Canada)';
                        break;

            case 7:     $var    = 'America/Tijuana';
                        $text   = '(GMT-08:00) Tijuana, Baja California';
                        break;

            case 8:     $var    = 'America/Denver';
                        $text   = '(GMT-07:00) Mountain Time (US &amp; Canada)';
                        break;

            case 9:     $var    = 'America/Chihuahua';
                        $text   = '(GMT-07:00) Chihuahua';
                        break;

            case 10:	$var    = 'America/Mazatlan';
                        $text   = '(GMT-07:00) Mazatlan';
                        break;

            case 11:    $var    = 'America/Phoenix';
                        $text   = '(GMT-07:00) Arizona';
                        break;

            case 12:    $var    = 'America/Regina';
                        $text   = '(GMT-06:00) Saskatchewan';
                        break;

            case 13:    $var    = 'America/Tegucigalpa';
                        $text   = '(GMT-06:00) Central America';
                        break;

            case 14:    $var    = 'America/Chicago';
                        $text   = '(GMT-06:00) Central Time (US &amp; Canada)';
                        break;

            case 15:    $var    = 'America/Mexico_City';
                        $text   = '(GMT-06:00) Mexico City';
                        break;

            case 16:    $var    = 'America/Monterrey';
                        $text   = '(GMT-06:00) Monterrey';
                        break;

            case 17:    $var    = 'America/New_York';
                        $text   = '(GMT-05:00) Eastern Time (US &amp; Canada)';
                        break;

            case 18:    $var    = 'America/Bogota';
                        $text   = '(GMT-05:00) Bogota';
                        break;

            case 19:    $var    = 'America/Lima';
                        $text   = '(GMT-05:00) Lima';
                        break;

            case 20:    $var    = 'America/Rio_Branco';
                        $text   = '(GMT-05:00) Rio Branco';
                        break;

            case 21:    $var    = 'America/Indiana/Indianapolis';
                        $text   = '(GMT-05:00) Indiana (East)';
                        break;

            case 22:    $var    = 'America/Caracas';
                        $text   = '(GMT-04:30) Caracas';
                        break;

            case 23:    $var    = 'America/Halifax';
                        $text   = '(GMT-04:00) Atlantic Time (Canada)';
                        break;

            case 24:    $var    = 'America/Manaus';
                        $text   = '(GMT-04:00) Manaus';
                        break;

            case 25:    $var    = 'America/Santiago';
                        $text   = '(GMT-04:00) Santiago';
                        break;

            case 26:    $var    = 'America/La_Paz';
                        $text   = '(GMT-04:00) La Paz';
                        break;

            case 27:    $var    = 'America/St_Johns';
                        $text   = '(GMT-03:30) Newfoundland';
                        break;

            case 28:    $var    = 'America/Argentina/Buenos_Aires';
                        $text   = '(GMT-03:00) Georgetown';
                        break;

            case 29:    $var    = 'America/Sao_Paulo';
                        $text   = '(GMT-03:00) Brasilia';
                        break;

            case 30:    $var    = 'America/Godthab';
                        $text   = '(GMT-03:00) Greenland';
                        break;

            case 31:    $var    = 'America/Montevideo';
                        $text   = '(GMT-03:00) Montevideo';
                        break;

            case 32:    $var    = 'Atlantic/South_Georgia';
                        $text   = '(GMT-02:00) Mid-Atlantic';
                        break;

            case 33:    $var    = 'Atlantic/Azores';
                        $text   = '(GMT-01:00) Azores';
                        break;

            case 34:    $var    = 'Atlantic/Cape_Verde';
                        $text   = '(GMT-01:00) Cape Verde Is.';
                        break;

            case 35:    $var    = 'Europe/Dublin';
                        $text   = '(GMT) Dublin';
                        break;

            case 36:    $var    = 'Europe/Lisbon';
                        $text   = '(GMT) Lisbon';
                        break;

            case 37:    $var    = 'Europe/London';
                        $text   = '(GMT) London';
                        break;

            case 38:    $var    = 'Africa/Monrovia';
                        $text   = '(GMT) Monrovia';
                        break;

            case 39:    $var    = 'Atlantic/Reykjavik';
                        $text   = '(GMT) Reykjavik';
                        break;

            case 40:    $var    = 'Africa/Casablanca';
                        $text   = '(GMT) Casablanca';
                        break;

            case 41:    $var    = 'Europe/Belgrade';
                        $text   = '(GMT+01:00) Belgrade';
                        break;

            case 42:    $var    = 'Europe/Bratislava';
                        $text   = '(GMT+01:00) Bratislava';
                        break;

            case 43:    $var    = 'Europe/Budapest';
                        $text   = '(GMT+01:00) Budapest';
                        break;

            case 44:    $var    = 'Europe/Ljubljana';
                        $text   = '(GMT+01:00) Ljubljana';
                        break;

            case 45:    $var    = 'Europe/Prague';
                        $text   = '(GMT+01:00) Prague';
                        break;

            case 46:    $var    = 'Europe/Sarajevo';
                        $text   = '(GMT+01:00) Sarajevo';
                        break;

            case 47:    $var    = 'Europe/Skopje';
                        $text   = '(GMT+01:00) Skopje';
                        break;

            case 48:    $var    = 'Europe/Warsaw';
                        $text   = '(GMT+01:00) Warsaw';
                        break;

            case 49:    $var    = 'Europe/Zagreb';
                        $text   = '(GMT+01:00) Zagreb';
                        break;

            case 50:    $var    = 'Europe/Brussels';
                        $text   = '(GMT+01:00) Brussels';
                        break;

            case 51:    $var    = 'Europe/Copenhagen';
                        $text   = '(GMT+01:00) Copenhagen';
                        break;

            case 52:    $var    = 'Europe/Madrid';
                        $text   = '(GMT+01:00) Madrid';
                        break;

            case 53:    $var    = 'Europe/Paris';
                        $text   = '(GMT+01:00) Paris';
                        break;

            case 54:    $var    = 'Africa/Algiers';
                        $text   = '(GMT+01:00) West Central Africa';
                        break;

            case 55:    $var    = 'Europe/Amsterdam';
                        $text   = '(GMT+01:00) Amsterdam';
                        break;

            case 56:    $var    = 'Europe/Berlin';
                        $text   = '(GMT+01:00) Berlin';
                        break;

            case 57:    $var    = 'Europe/Rome';
                        $text   = '(GMT+01:00) Rome';
                        break;

            case 58:    $var    = 'Europe/Stockholm';
                        $text   = '(GMT+01:00) Stockholm';
                        break;

            case 59:    $var    = 'Europe/Vienna';
                        $text   = '(GMT+01:00) Vienna';
                        break;

            case 60:    $var    = 'Europe/Minsk';
                        $text   = '(GMT+02:00) Minsk';
                        break;

            case 61:    $var    = 'Africa/Cairo';
                        $text   = '(GMT+02:00) Cairo';
                        break;

            case 62:    $var    = 'Europe/Helsinki';
                        $text   = '(GMT+02:00) Helsinki';
                        break;

            case 63:    $var    = 'Europe/Riga';
                        $text   = '(GMT+02:00) Riga';
                        break;

            case 64:    $var    = 'Europe/Sofia';
                        $text   = '(GMT+02:00) Sofia';
                        break;

            case 65:    $var    = 'Europe/Tallinn';
                        $text   = '(GMT+02:00) Tallinn';
                        break;

            case 66:    $var    = 'Europe/Vilnius';
                        $text   = '(GMT+02:00) Vilnius';
                        break;

            case 67:    $var    = 'Europe/Athens';
                        $text   = '(GMT+02:00) Athens';
                        break;

            case 68:    $var    = 'Europe/Bucharest';
                        $text   = '(GMT+02:00) Bucharest';
                        break;

            case 69:    $var    = 'Europe/Istanbul';
                        $text   = '(GMT+02:00) Istanbul';
                        break;



            case 70:    $var    = 'Asia/Jerusalem';
                        $text   = '(GMT+02:00) Jerusalem';
                        break;

            case 71:    $var    = 'Asia/Amman';
                        $text   = '(GMT+02:00) Amman';
                        break;

            case 72:    $var    = 'Asia/Beirut';
                        $text   = '(GMT+02:00) Beirut';
                        break;

            case 73:    $var    = 'Africa/Windhoek';
                        $text   = '(GMT+02:00) Beirut';
                        break;

            case 74:    $var    = 'Africa/Harare';
                        $text   = '(GMT+02:00) Harare';
                        break;

            case 75:    $var    = 'Asia/Kuwait';
                        $text   = '(GMT+03:00) Kuwait';
                        break;

            case 76:    $var    = 'Asia/Riyadh';
                        $text   = '(GMT+03:00) Riyadh';
                        break;

            case 77:    $var    = 'Asia/Baghdad';
                        $text   = '(GMT+03:00) Baghdad';
                        break;

            case 78:    $var    = 'Africa/Nairobi';
                        $text   = '(GMT+03:00) Nairobi';
                        break;

            case 79:    $var    = 'Asia/Tbilisi';
                        $text   = '(GMT+03:00) Tbilisi';
                        break;

            case 80:    $var    = 'Europe/Moscow';
                        $text   = '(GMT+03:00) Moscow';
                        break;

            case 81:    $var    = 'Europe/Volgograd';
                        $text   = '(GMT+03:00) Volgograd';
                        break;

            case 82:    $var    = 'Asia/Tehran';
                        $text   = '(GMT+03:30) Tehran';
                        break;

            case 83:    $var    = 'Asia/Muscat';
                        $text   = '(GMT+04:00) Muscat';
                        break;

            case 84:    $var    = 'Asia/Baku';
                        $text   = '(GMT+04:00) Baku';
                        break;

            case 85:    $var    = 'Asia/Yerevan';
                        $text   = '(GMT+04:00) Yerevan';
                        break;

            case 86:    $var    = 'Asia/Yekaterinburg';
                        $text   = '(GMT+05:00) Ekaterinburg';
                        break;

            case 87:    $var    = 'Asia/Karachi';
                        $text   = '(GMT+05:00) Karachi';
                        break;

            case 88:    $var    = 'Asia/Tashkent';
                        $text   = '(GMT+05:00) Tashkent';
                        break;

            case 89:    $var    = 'Asia/Kolkata';
                        $text   = '(GMT+05:30) Calcutta';
                        break;

            case 90:    $var    = 'Asia/Colombo';
                        $text   = '(GMT+05:30) Sri Jayawardenepura';
                        break;

            case 91:    $var    = 'Asia/Katmandu';
                        $text   = '(GMT+05:45) Kathmandu';
                        break;

            case 92:    $var    = 'Asia/Dhaka';
                        $text   = '(GMT+06:00) Dhaka';
                        break;

            case 93:    $var    = 'Asia/Almaty';
                        $text   = '(GMT+06:00) Almaty';
                        break;

            case 94:    $var    = 'Asia/Novosibirsk';
                        $text   = '(GMT+06:00) Novosibirsk';
                        break;

            case 95:    $var    = 'Asia/Rangoon';
                        $text   = '(GMT+06:30) Yangon (Rangoon)';
                        break;

            case 96:    $var    = 'Asia/Krasnoyarsk';
                        $text   = '(GMT+07:00) Krasnoyarsk';
                        break;

            case 97:    $var    = 'Asia/Bangkok';
                        $text   = '(GMT+07:00) Bangkok';
                        break;

            case 98:    $var    = 'Asia/Jakarta';
                        $text   = '(GMT+07:00) Jakarta';
                        break;

            case 99:    $var    = 'Asia/Brunei';
                        $text   = '(GMT+08:00) Beijing';
                        break;

            case 100:   $var    = 'Asia/Chongqing';
                        $text   = '(GMT+08:00) Chongqing';
                        break;

            case 101:   $var    = 'Asia/Hong_Kong';
                        $text   = '(GMT+08:00) Hong Kong';
                        break;

            case 102:   $var    = 'Asia/Urumqi';
                        $text   = '(GMT+08:00) Urumqi';
                        break;

            case 103:   $var    = 'Asia/Irkutsk';
                        $text   = '(GMT+08:00) Irkutsk';
                        break;

            case 104:   $var    = 'Asia/Ulaanbaatar';
                        $text   = '(GMT+08:00) Ulaan Bataar';
                        break;

            case 105:   $var    = 'Asia/Kuala_Lumpur';
                        $text   = '(GMT+08:00) Kuala Lumpur';
                        break;

            case 106:   $var    = 'Asia/Singapore';
                        $text   = '(GMT+08:00) Singapore';
                        break;

            case 107:   $var    = 'Asia/Taipei';
                        $text   = '(GMT+08:00) Taipei';
                        break;

            case 108:   $var    = 'Australia/Perth';
                        $text   = '(GMT+08:00) Perth';
                        break;

            case 109:   $var    = 'Asia/Seoul';
                        $text   = '(GMT+09:00) Seoul';
                        break;

            case 110:   $var    = 'Asia/Tokyo';
                        $text   = '(GMT+09:00) Tokyo';
                        break;

            case 111:   $var    = 'Asia/Yakutsk';
                        $text   = '(GMT+09:00) Yakutsk';
                        break;

            case 112:   $var    = 'Australia/Darwin';
                        $text   = '(GMT+09:30) Darwin';
                        break;

            case 113:   $var    = 'Australia/Adelaide';
                        $text   = '(GMT+09:30) Adelaide';
                        break;

            case 114:   $var    = 'Australia/Canberra';
                        $text   = '(GMT+10:00) Canberra';
                        break;

            case 115:   $var    = 'Australia/Melbourne';
                        $text   = '(GMT+10:00) Melbourne';
                        break;

            case 116:   $var    = 'Australia/Sydney';
                        $text   = '(GMT+10:00) Sydney';
                        break;

            case 117:   $var    = 'Australia/Brisbane';
                        $text   = '(GMT+10:00) Brisbane';
                        break;

            case 118:   $var    = 'Australia/Hobart';
                        $text   = '(GMT+10:00) Hobart';
                        break;

            case 119:   $var    = 'Asia/Vladivostok';
                        $text   = '(GMT+10:00) Vladivostok';
                        break;

            case 120:   $var    = 'Pacific/Guam';
                        $text   = '(GMT+10:00) Guam';
                        break;

            case 121:   $var    = 'Pacific/Port_Moresby';
                        $text   = '(GMT+10:00) Port Moresby';
                        break;

            case 122:   $var    = 'Asia/Magadan';
                        $text   = '(GMT+11:00) Magadan';
                        break;

            case 123:   $var    = 'Pacific/Fiji';
                        $text   = '(GMT+12:00) Fiji';
                        break;

            case 124:   $var    = 'Asia/Kamchatka';
                        $text   = '(GMT+12:00) Kamchatka';
                        break;

            case 125:   $var    = 'Pacific/Auckland';
                        $text   = '(GMT+12:00) Auckland';
                        break;

            case 126:   $var    = 'Pacific/Tongatapu';
                        $text   = '(GMT+13:00) Nukualofa';
                        break;

            default:    $var    = 'America/Los_Angeles'; 
                        $text   = 'Unknown Default';
                        break;
        }

        // Return value
        if ($type == 0)
        {
            return $var;
        } else {
            return $text;
        }
    }
}