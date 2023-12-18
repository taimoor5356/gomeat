<?php

use App\Http\Controllers\JazzcashController;
use App\Models\Country;
use App\Models\CountryHasState;
use AmrShawky\LaravelCurrency\Facade\Currency as CurrencyConverter;
use Illuminate\Support\Facades\Route;
use OpenSpout\Common\Entity\Row;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('successful', function () {
    return 'Successfull';
});

Route::get('processing', function () {
    return 'In Process';
});
Route::get('currency-conversion', function () {
    $data = CurrencyConverter::convert()->from('USD')->to('PKR')->amount(1)->get();
    return 'Here'.$data;
});


Route::get('/insert-data', function () {
    // All countries
    // length 252
    $countries = array(
        array("name" => "Afghanistan","short_name" => "AF","phone_code" => 93,"currency_name" => "AFN","currency_symbol" => "؋"),
        array("name" => "Aland Islands","short_name" => "AX","phone_code" => 358,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Albania","short_name" => "AL","phone_code" => 355,"currency_name" => "ALL","currency_symbol" => "Lek"),
        array("name" => "Algeria","short_name" => "DZ","phone_code" => 213,"currency_name" => "DZD","currency_symbol" => "دج"),
        array("name" => "American Samoa","short_name" => "AS","phone_code" => 1684,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Andorra","short_name" => "AD","phone_code" => 376,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Angola","short_name" => "AO","phone_code" => 244,"currency_name" => "AOA","currency_symbol" => "Kz"),
        array("name" => "Anguilla","short_name" => "AI","phone_code" => 1264,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Antarctica","short_name" => "AQ","phone_code" => 672,"currency_name" => "AAD","currency_symbol" => "$"),
        array("name" => "Antigua and Barbuda","short_name" => "AG","phone_code" => 1268,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Argentina","short_name" => "AR","phone_code" => 54,"currency_name" => "ARS","currency_symbol" => "$"),
        array("name" => "Armenia","short_name" => "AM","phone_code" => 374,"currency_name" => "AMD","currency_symbol" => "֏"),
        array("name" => "Aruba","short_name" => "AW","phone_code" => 297,"currency_name" => "AWG","currency_symbol" => "ƒ"),
        array("name" => "Australia","short_name" => "AU","phone_code" => 61,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Austria","short_name" => "AT","phone_code" => 43,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Azerbaijan","short_name" => "AZ","phone_code" => 994,"currency_name" => "AZN","currency_symbol" => "m"),
        array("name" => "Bahamas","short_name" => "BS","phone_code" => 1242,"currency_name" => "BSD","currency_symbol" => "B$"),
        array("name" => "Bahrain","short_name" => "BH","phone_code" => 973,"currency_name" => "BHD","currency_symbol" => ".د.ب"),
        //
        array("name" => "Pakistan","short_name" => "PK","phone_code" => 92,"currency_name" => "PKR","currency_symbol" => "₨"),
        array("name" => "Bangladesh","short_name" => "BD","phone_code" => 880,"currency_name" => "BDT","currency_symbol" => "৳"),
        array("name" => "Barbados","short_name" => "BB","phone_code" => 1246,"currency_name" => "BBD","currency_symbol" => "Bds$"),
        array("name" => "Belarus","short_name" => "BY","phone_code" => 375,"currency_name" => "BYN","currency_symbol" => "Br"),
        array("name" => "Belgium","short_name" => "BE","phone_code" => 32,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Belize","short_name" => "BZ","phone_code" => 501,"currency_name" => "BZD","currency_symbol" => "$"),
        //
        array("name" => "United States","short_name" => "US","phone_code" => 1,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Benin","short_name" => "BJ","phone_code" => 229,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Bermuda","short_name" => "BM","phone_code" => 1441,"currency_name" => "BMD","currency_symbol" => "$"),
        array("name" => "Bhutan","short_name" => "BT","phone_code" => 975,"currency_name" => "BTN","currency_symbol" => "Nu."),
        array("name" => "Bolivia","short_name" => "BO","phone_code" => 591,"currency_name" => "BOB","currency_symbol" => "Bs."),
        array("name" => "Bonaire, Sint Eustatius and Saba","short_name" => "BQ","phone_code" => 599,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Bosnia and Herzegovina","short_name" => "BA","phone_code" => 387,"currency_name" => "BAM","currency_symbol" => "KM"),
        array("name" => "Botswana","short_name" => "BW","phone_code" => 267,"currency_name" => "BWP","currency_symbol" => "P"),
        array("name" => "Bouvet Island","short_name" => "BV","phone_code" => 55,"currency_name" => "NOK","currency_symbol" => "kr"),
        array("name" => "Brazil","short_name" => "BR","phone_code" => 55,"currency_name" => "BRL","currency_symbol" => "R$"),
        array("name" => "British Indian Ocean Territory","short_name" => "IO","phone_code" => 246,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Brunei Darussalam","short_name" => "BN","phone_code" => 673,"currency_name" => "BND","currency_symbol" => "B$"),
        array("name" => "Bulgaria","short_name" => "BG","phone_code" => 359,"currency_name" => "BGN","currency_symbol" => "Лв."),
        array("name" => "Burkina Faso","short_name" => "BF","phone_code" => 226,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Burundi","short_name" => "BI","phone_code" => 257,"currency_name" => "BIF","currency_symbol" => "FBu"),
        array("name" => "Cambodia","short_name" => "KH","phone_code" => 855,"currency_name" => "KHR","currency_symbol" => "KHR"),
        array("name" => "Cameroon","short_name" => "CM","phone_code" => 237,"currency_name" => "XAF","currency_symbol" => "FCFA"),
        array("name" => "Canada","short_name" => "CA","phone_code" => 1,"currency_name" => "CAD","currency_symbol" => "$"),
        array("name" => "Cape Verde","short_name" => "CV","phone_code" => 238,"currency_name" => "CVE","currency_symbol" => "$"),
        array("name" => "Cayman Islands","short_name" => "KY","phone_code" => 1345,"currency_name" => "KYD","currency_symbol" => "$"),
        array("name" => "Central African Republic","short_name" => "CF","phone_code" => 236,"currency_name" => "XAF","currency_symbol" => "FCFA"),
        array("name" => "Chad","short_name" => "TD","phone_code" => 235,"currency_name" => "XAF","currency_symbol" => "FCFA"),
        array("name" => "Chile","short_name" => "CL","phone_code" => 56,"currency_name" => "CLP","currency_symbol" => "$"),
        array("name" => "China","short_name" => "CN","phone_code" => 86,"currency_name" => "CNY","currency_symbol" => "¥"),
        array("name" => "Christmas Island","short_name" => "CX","phone_code" => 61,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Cocos (Keeling) Islands","short_name" => "CC","phone_code" => 672,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Colombia","short_name" => "CO","phone_code" => 57,"currency_name" => "COP","currency_symbol" => "$"),
        array("name" => "Comoros","short_name" => "KM","phone_code" => 269,"currency_name" => "KMF","currency_symbol" => "CF"),
        array("name" => "Congo","short_name" => "CG","phone_code" => 242,"currency_name" => "XAF","currency_symbol" => "FC"),
        array("name" => "Congo, Democratic Republic of the Congo","short_name" => "CD","phone_code" => 242,"currency_name" => "CDF","currency_symbol" => "FC"),
        array("name" => "Cook Islands","short_name" => "CK","phone_code" => 682,"currency_name" => "NZD","currency_symbol" => "$"),
        array("name" => "Costa Rica","short_name" => "CR","phone_code" => 506,"currency_name" => "CRC","currency_symbol" => "₡"),
        array("name" => "Cote D'Ivoire","short_name" => "CI","phone_code" => 225,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Croatia","short_name" => "HR","phone_code" => 385,"currency_name" => "HRK","currency_symbol" => "kn"),
        array("name" => "Cuba","short_name" => "CU","phone_code" => 53,"currency_name" => "CUP","currency_symbol" => "$"),
        array("name" => "Curacao","short_name" => "CW","phone_code" => 599,"currency_name" => "ANG","currency_symbol" => "ƒ"),
        array("name" => "Cyprus","short_name" => "CY","phone_code" => 357,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Czech Republic","short_name" => "CZ","phone_code" => 420,"currency_name" => "CZK","currency_symbol" => "Kč"),
        array("name" => "Denmark","short_name" => "DK","phone_code" => 45,"currency_name" => "DKK","currency_symbol" => "Kr."),
        array("name" => "Djibouti","short_name" => "DJ","phone_code" => 253,"currency_name" => "DJF","currency_symbol" => "Fdj"),
        array("name" => "Dominica","short_name" => "DM","phone_code" => 1767,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Dominican Republic","short_name" => "DO","phone_code" => 1809,"currency_name" => "DOP","currency_symbol" => "$"),
        array("name" => "Ecuador","short_name" => "EC","phone_code" => 593,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Egypt","short_name" => "EG","phone_code" => 20,"currency_name" => "EGP","currency_symbol" => "ج.م"),
        array("name" => "El Salvador","short_name" => "SV","phone_code" => 503,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Equatorial Guinea","short_name" => "GQ","phone_code" => 240,"currency_name" => "XAF","currency_symbol" => "FCFA"),
        array("name" => "Eritrea","short_name" => "ER","phone_code" => 291,"currency_name" => "ERN","currency_symbol" => "Nfk"),
        array("name" => "Estonia","short_name" => "EE","phone_code" => 372,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Ethiopia","short_name" => "ET","phone_code" => 251,"currency_name" => "ETB","currency_symbol" => "Nkf"),
        array("name" => "Falkland Islands (Malvinas)","short_name" => "FK","phone_code" => 500,"currency_name" => "FKP","currency_symbol" => "£"),
        array("name" => "Faroe Islands","short_name" => "FO","phone_code" => 298,"currency_name" => "DKK","currency_symbol" => "Kr."),
        array("name" => "Fiji","short_name" => "FJ","phone_code" => 679,"currency_name" => "FJD","currency_symbol" => "FJ$"),
        array("name" => "Finland","short_name" => "FI","phone_code" => 358,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "France","short_name" => "FR","phone_code" => 33,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "French Guiana","short_name" => "GF","phone_code" => 594,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "French Polynesia","short_name" => "PF","phone_code" => 689,"currency_name" => "XPF","currency_symbol" => "₣"),
        array("name" => "French Southern Territories","short_name" => "TF","phone_code" => 262,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Gabon","short_name" => "GA","phone_code" => 241,"currency_name" => "XAF","currency_symbol" => "FCFA"),
        array("name" => "Gambia","short_name" => "GM","phone_code" => 220,"currency_name" => "GMD","currency_symbol" => "D"),
        array("name" => "Georgia","short_name" => "GE","phone_code" => 995,"currency_name" => "GEL","currency_symbol" => "ლ"),
        array("name" => "Germany","short_name" => "DE","phone_code" => 49,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Ghana","short_name" => "GH","phone_code" => 233,"currency_name" => "GHS","currency_symbol" => "GH₵"),
        array("name" => "Gibraltar","short_name" => "GI","phone_code" => 350,"currency_name" => "GIP","currency_symbol" => "£"),
        array("name" => "Greece","short_name" => "GR","phone_code" => 30,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Greenland","short_name" => "GL","phone_code" => 299,"currency_name" => "DKK","currency_symbol" => "Kr."),
        array("name" => "Grenada","short_name" => "GD","phone_code" => 1473,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Guadeloupe","short_name" => "GP","phone_code" => 590,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Guam","short_name" => "GU","phone_code" => 1671,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Guatemala","short_name" => "GT","phone_code" => 502,"currency_name" => "GTQ","currency_symbol" => "Q"),
        array("name" => "Guernsey","short_name" => "GG","phone_code" => 44,"currency_name" => "GBP","currency_symbol" => "£"),
        array("name" => "Guinea","short_name" => "GN","phone_code" => 224,"currency_name" => "GNF","currency_symbol" => "FG"),
        array("name" => "Guinea-Bissau","short_name" => "GW","phone_code" => 245,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Guyana","short_name" => "GY","phone_code" => 592,"currency_name" => "GYD","currency_symbol" => "$"),
        array("name" => "Haiti","short_name" => "HT","phone_code" => 509,"currency_name" => "HTG","currency_symbol" => "G"),
        array("name" => "Heard Island and McDonald Islands","short_name" => "HM","phone_code" => 0,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Holy See (Vatican City State)","short_name" => "VA","phone_code" => 39,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Honduras","short_name" => "HN","phone_code" => 504,"currency_name" => "HNL","currency_symbol" => "L"),
        array("name" => "Hong Kong","short_name" => "HK","phone_code" => 852,"currency_name" => "HKD","currency_symbol" => "$"),
        array("name" => "Hungary","short_name" => "HU","phone_code" => 36,"currency_name" => "HUF","currency_symbol" => "Ft"),
        array("name" => "Iceland","short_name" => "IS","phone_code" => 354,"currency_name" => "ISK","currency_symbol" => "kr"),
        array("name" => "India","short_name" => "IN","phone_code" => 91,"currency_name" => "INR","currency_symbol" => "₹"),
        array("name" => "Indonesia","short_name" => "ID","phone_code" => 62,"currency_name" => "IDR","currency_symbol" => "Rp"),
        array("name" => "Iran, Islamic Republic of","short_name" => "IR","phone_code" => 98,"currency_name" => "IRR","currency_symbol" => "﷼"),
        array("name" => "Iraq","short_name" => "IQ","phone_code" => 964,"currency_name" => "IQD","currency_symbol" => "د.ع"),
        array("name" => "Ireland","short_name" => "IE","phone_code" => 353,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Isle of Man","short_name" => "IM","phone_code" => 44,"currency_name" => "GBP","currency_symbol" => "£"),
        array("name" => "Israel","short_name" => "IL","phone_code" => 972,"currency_name" => "ILS","currency_symbol" => "₪"),
        array("name" => "Italy","short_name" => "IT","phone_code" => 39,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Jamaica","short_name" => "JM","phone_code" => 1876,"currency_name" => "JMD","currency_symbol" => "J$"),
        array("name" => "Japan","short_name" => "JP","phone_code" => 81,"currency_name" => "JPY","currency_symbol" => "¥"),
        array("name" => "Jersey","short_name" => "JE","phone_code" => 44,"currency_name" => "GBP","currency_symbol" => "£"),
        array("name" => "Jordan","short_name" => "JO","phone_code" => 962,"currency_name" => "JOD","currency_symbol" => "ا.د"),
        array("name" => "Kazakhstan","short_name" => "KZ","phone_code" => 7,"currency_name" => "KZT","currency_symbol" => "лв"),
        array("name" => "Kenya","short_name" => "KE","phone_code" => 254,"currency_name" => "KES","currency_symbol" => "KSh"),
        array("name" => "Kiribati","short_name" => "KI","phone_code" => 686,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Korea, Democratic People's Republic of","short_name" => "KP","phone_code" => 850,"currency_name" => "KPW","currency_symbol" => "₩"),
        array("name" => "Korea, Republic of","short_name" => "KR","phone_code" => 82,"currency_name" => "KRW","currency_symbol" => "₩"),
        array("name" => "Kosovo","short_name" => "XK","phone_code" => 383,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Kuwait","short_name" => "KW","phone_code" => 965,"currency_name" => "KWD","currency_symbol" => "ك.د"),
        array("name" => "Kyrgyzstan","short_name" => "KG","phone_code" => 996,"currency_name" => "KGS","currency_symbol" => "лв"),
        array("name" => "Lao People's Democratic Republic","short_name" => "LA","phone_code" => 856,"currency_name" => "LAK","currency_symbol" => "₭"),
        array("name" => "Latvia","short_name" => "LV","phone_code" => 371,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Lebanon","short_name" => "LB","phone_code" => 961,"currency_name" => "LBP","currency_symbol" => "£"),
        array("name" => "Lesotho","short_name" => "LS","phone_code" => 266,"currency_name" => "LSL","currency_symbol" => "L"),
        array("name" => "Liberia","short_name" => "LR","phone_code" => 231,"currency_name" => "LRD","currency_symbol" => "$"),
        array("name" => "Libyan Arab Jamahiriya","short_name" => "LY","phone_code" => 218,"currency_name" => "LYD","currency_symbol" => "د.ل"),
        array("name" => "Liechtenstein","short_name" => "LI","phone_code" => 423,"currency_name" => "CHF","currency_symbol" => "CHf"),
        array("name" => "Lithuania","short_name" => "LT","phone_code" => 370,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Luxembourg","short_name" => "LU","phone_code" => 352,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Macao","short_name" => "MO","phone_code" => 853,"currency_name" => "MOP","currency_symbol" => "$"),
        array("name" => "Macedonia, the Former Yugoslav Republic of","short_name" => "MK","phone_code" => 389,"currency_name" => "MKD","currency_symbol" => "ден"),
        array("name" => "Madagascar","short_name" => "MG","phone_code" => 261,"currency_name" => "MGA","currency_symbol" => "Ar"),
        array("name" => "Malawi","short_name" => "MW","phone_code" => 265,"currency_name" => "MWK","currency_symbol" => "MK"),
        array("name" => "Malaysia","short_name" => "MY","phone_code" => 60,"currency_name" => "MYR","currency_symbol" => "RM"),
        array("name" => "Maldives","short_name" => "MV","phone_code" => 960,"currency_name" => "MVR","currency_symbol" => "Rf"),
        array("name" => "Mali","short_name" => "ML","phone_code" => 223,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Malta","short_name" => "MT","phone_code" => 356,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Marshall Islands","short_name" => "MH","phone_code" => 692,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Martinique","short_name" => "MQ","phone_code" => 596,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Mauritania","short_name" => "MR","phone_code" => 222,"currency_name" => "MRO","currency_symbol" => "MRU"),
        array("name" => "Mauritius","short_name" => "MU","phone_code" => 230,"currency_name" => "MUR","currency_symbol" => "₨"),
        array("name" => "Mayotte","short_name" => "YT","phone_code" => 262,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Mexico","short_name" => "MX","phone_code" => 52,"currency_name" => "MXN","currency_symbol" => "$"),
        array("name" => "Micronesia, Federated States of","short_name" => "FM","phone_code" => 691,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Moldova, Republic of","short_name" => "MD","phone_code" => 373,"currency_name" => "MDL","currency_symbol" => "L"),
        array("name" => "Monaco","short_name" => "MC","phone_code" => 377,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Mongolia","short_name" => "MN","phone_code" => 976,"currency_name" => "MNT","currency_symbol" => "₮"),
        array("name" => "Montenegro","short_name" => "ME","phone_code" => 382,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Montserrat","short_name" => "MS","phone_code" => 1664,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Morocco","short_name" => "MA","phone_code" => 212,"currency_name" => "MAD","currency_symbol" => "DH"),
        array("name" => "Mozambique","short_name" => "MZ","phone_code" => 258,"currency_name" => "MZN","currency_symbol" => "MT"),
        array("name" => "Myanmar","short_name" => "MM","phone_code" => 95,"currency_name" => "MMK","currency_symbol" => "K"),
        array("name" => "Namibia","short_name" => "NA","phone_code" => 264,"currency_name" => "NAD","currency_symbol" => "$"),
        array("name" => "Nauru","short_name" => "NR","phone_code" => 674,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Nepal","short_name" => "NP","phone_code" => 977,"currency_name" => "NPR","currency_symbol" => "₨"),
        array("name" => "Netherlands","short_name" => "NL","phone_code" => 31,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Netherlands Antilles","short_name" => "AN","phone_code" => 599,"currency_name" => "ANG","currency_symbol" => "NAf"),
        array("name" => "New Caledonia","short_name" => "NC","phone_code" => 687,"currency_name" => "XPF","currency_symbol" => "₣"),
        array("name" => "New Zealand","short_name" => "NZ","phone_code" => 64,"currency_name" => "NZD","currency_symbol" => "$"),
        array("name" => "Nicaragua","short_name" => "NI","phone_code" => 505,"currency_name" => "NIO","currency_symbol" => "C$"),
        array("name" => "Niger","short_name" => "NE","phone_code" => 227,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Nigeria","short_name" => "NG","phone_code" => 234,"currency_name" => "NGN","currency_symbol" => "₦"),
        array("name" => "Niue","short_name" => "NU","phone_code" => 683,"currency_name" => "NZD","currency_symbol" => "$"),
        array("name" => "Norfolk Island","short_name" => "NF","phone_code" => 672,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Northern Mariana Islands","short_name" => "MP","phone_code" => 1670,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Norway","short_name" => "NO","phone_code" => 47,"currency_name" => "NOK","currency_symbol" => "kr"),
        array("name" => "Oman","short_name" => "OM","phone_code" => 968,"currency_name" => "OMR","currency_symbol" => ".ع.ر"),
        array("name" => "Palau","short_name" => "PW","phone_code" => 680,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Palestinian Territory, Occupied","short_name" => "PS","phone_code" => 970,"currency_name" => "ILS","currency_symbol" => "₪"),
        array("name" => "Panama","short_name" => "PA","phone_code" => 507,"currency_name" => "PAB","currency_symbol" => "B/."),
        array("name" => "Papua New Guinea","short_name" => "PG","phone_code" => 675,"currency_name" => "PGK","currency_symbol" => "K"),
        array("name" => "Paraguay","short_name" => "PY","phone_code" => 595,"currency_name" => "PYG","currency_symbol" => "₲"),
        array("name" => "Peru","short_name" => "PE","phone_code" => 51,"currency_name" => "PEN","currency_symbol" => "S/."),
        array("name" => "Philippines","short_name" => "PH","phone_code" => 63,"currency_name" => "PHP","currency_symbol" => "₱"),
        array("name" => "Pitcairn","short_name" => "PN","phone_code" => 64,"currency_name" => "NZD","currency_symbol" => "$"),
        array("name" => "Poland","short_name" => "PL","phone_code" => 48,"currency_name" => "PLN","currency_symbol" => "zł"),
        array("name" => "Portugal","short_name" => "PT","phone_code" => 351,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Puerto Rico","short_name" => "PR","phone_code" => 1787,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Qatar","short_name" => "QA","phone_code" => 974,"currency_name" => "QAR","currency_symbol" => "ق.ر"),
        array("name" => "Reunion","short_name" => "RE","phone_code" => 262,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Romania","short_name" => "RO","phone_code" => 40,"currency_name" => "RON","currency_symbol" => "lei"),
        array("name" => "Russian Federation","short_name" => "RU","phone_code" => 7,"currency_name" => "RUB","currency_symbol" => "₽"),
        array("name" => "Rwanda","short_name" => "RW","phone_code" => 250,"currency_name" => "RWF","currency_symbol" => "FRw"),
        array("name" => "Saint Barthelemy","short_name" => "BL","phone_code" => 590,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Saint Helena","short_name" => "SH","phone_code" => 290,"currency_name" => "SHP","currency_symbol" => "£"),
        array("name" => "Saint Kitts and Nevis","short_name" => "KN","phone_code" => 1869,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Saint Lucia","short_name" => "LC","phone_code" => 1758,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Saint Martin","short_name" => "MF","phone_code" => 590,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Saint Pierre and Miquelon","short_name" => "PM","phone_code" => 508,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Saint Vincent and the Grenadines","short_name" => "VC","phone_code" => 1784,"currency_name" => "XCD","currency_symbol" => "$"),
        array("name" => "Samoa","short_name" => "WS","phone_code" => 684,"currency_name" => "WST","currency_symbol" => "SAT"),
        array("name" => "San Marino","short_name" => "SM","phone_code" => 378,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Sao Tome and Principe","short_name" => "ST","phone_code" => 239,"currency_name" => "STD","currency_symbol" => "Db"),
        array("name" => "Saudi Arabia","short_name" => "SA","phone_code" => 966,"currency_name" => "SAR","currency_symbol" => "﷼"),
        array("name" => "Senegal","short_name" => "SN","phone_code" => 221,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Serbia","short_name" => "RS","phone_code" => 381,"currency_name" => "RSD","currency_symbol" => "din"),
        array("name" => "Serbia and Montenegro","short_name" => "CS","phone_code" => 381,"currency_name" => "RSD","currency_symbol" => "din"),
        array("name" => "Seychelles","short_name" => "SC","phone_code" => 248,"currency_name" => "SCR","currency_symbol" => "SRe"),
        array("name" => "Sierra Leone","short_name" => "SL","phone_code" => 232,"currency_name" => "SLL","currency_symbol" => "Le"),
        array("name" => "Singapore","short_name" => "SG","phone_code" => 65,"currency_name" => "SGD","currency_symbol" => "$"),
        array("name" => "St Martin","short_name" => "SX","phone_code" => 721,"currency_name" => "ANG","currency_symbol" => "ƒ"),
        array("name" => "Slovakia","short_name" => "SK","phone_code" => 421,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Slovenia","short_name" => "SI","phone_code" => 386,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Solomon Islands","short_name" => "SB","phone_code" => 677,"currency_name" => "SBD","currency_symbol" => "Si$"),
        array("name" => "Somalia","short_name" => "SO","phone_code" => 252,"currency_name" => "SOS","currency_symbol" => "Sh.so."),
        array("name" => "South Africa","short_name" => "ZA","phone_code" => 27,"currency_name" => "ZAR","currency_symbol" => "R"),
        array("name" => "South Georgia and the South Sandwich Islands","short_name" => "GS","phone_code" => 500,"currency_name" => "GBP","currency_symbol" => "£"),
        array("name" => "South Sudan","short_name" => "SS","phone_code" => 211,"currency_name" => "SSP","currency_symbol" => "£"),
        array("name" => "Spain","short_name" => "ES","phone_code" => 34,"currency_name" => "EUR","currency_symbol" => "€"),
        array("name" => "Sri Lanka","short_name" => "LK","phone_code" => 94,"currency_name" => "LKR","currency_symbol" => "Rs"),
        array("name" => "Sudan","short_name" => "SD","phone_code" => 249,"currency_name" => "SDG","currency_symbol" => ".س.ج"),
        array("name" => "Suriname","short_name" => "SR","phone_code" => 597,"currency_name" => "SRD","currency_symbol" => "$"),
        array("name" => "Svalbard and Jan Mayen","short_name" => "SJ","phone_code" => 47,"currency_name" => "NOK","currency_symbol" => "kr"),
        array("name" => "Swaziland","short_name" => "SZ","phone_code" => 268,"currency_name" => "SZL","currency_symbol" => "E"),
        array("name" => "Sweden","short_name" => "SE","phone_code" => 46,"currency_name" => "SEK","currency_symbol" => "kr"),
        array("name" => "Switzerland","short_name" => "CH","phone_code" => 41,"currency_name" => "CHF","currency_symbol" => "CHf"),
        array("name" => "Syrian Arab Republic","short_name" => "SY","phone_code" => 963,"currency_name" => "SYP","currency_symbol" => "LS"),
        array("name" => "Taiwan, Province of China","short_name" => "TW","phone_code" => 886,"currency_name" => "TWD","currency_symbol" => "$"),
        array("name" => "Tajikistan","short_name" => "TJ","phone_code" => 992,"currency_name" => "TJS","currency_symbol" => "SM"),
        array("name" => "Tanzania, United Republic of","short_name" => "TZ","phone_code" => 255,"currency_name" => "TZS","currency_symbol" => "TSh"),
        array("name" => "Thailand","short_name" => "TH","phone_code" => 66,"currency_name" => "THB","currency_symbol" => "฿"),
        array("name" => "Timor-Leste","short_name" => "TL","phone_code" => 670,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Togo","short_name" => "TG","phone_code" => 228,"currency_name" => "XOF","currency_symbol" => "CFA"),
        array("name" => "Tokelau","short_name" => "TK","phone_code" => 690,"currency_name" => "NZD","currency_symbol" => "$"),
        array("name" => "Tonga","short_name" => "TO","phone_code" => 676,"currency_name" => "TOP","currency_symbol" => "$"),
        array("name" => "Trinidad and Tobago","short_name" => "TT","phone_code" => 1868,"currency_name" => "TTD","currency_symbol" => "$"),
        array("name" => "Tunisia","short_name" => "TN","phone_code" => 216,"currency_name" => "TND","currency_symbol" => "ت.د"),
        array("name" => "Turkey","short_name" => "TR","phone_code" => 90,"currency_name" => "TRY","currency_symbol" => "₺"),
        array("name" => "Turkmenistan","short_name" => "TM","phone_code" => 7370,"currency_name" => "TMT","currency_symbol" => "T"),
        array("name" => "Turks and Caicos Islands","short_name" => "TC","phone_code" => 1649,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Tuvalu","short_name" => "TV","phone_code" => 688,"currency_name" => "AUD","currency_symbol" => "$"),
        array("name" => "Uganda","short_name" => "UG","phone_code" => 256,"currency_name" => "UGX","currency_symbol" => "USh"),
        array("name" => "Ukraine","short_name" => "UA","phone_code" => 380,"currency_name" => "UAH","currency_symbol" => "₴"),
        array("name" => "United Arab Emirates","short_name" => "AE","phone_code" => 971,"currency_name" => "AED","currency_symbol" => "إ.د"),
        array("name" => "United Kingdom","short_name" => "GB","phone_code" => 44,"currency_name" => "GBP","currency_symbol" => "£"),
        array("name" => "United States Minor Outlying Islands","short_name" => "UM","phone_code" => 1,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Uruguay","short_name" => "UY","phone_code" => 598,"currency_name" => "UYU","currency_symbol" => "$"),
        array("name" => "Uzbekistan","short_name" => "UZ","phone_code" => 998,"currency_name" => "UZS","currency_symbol" => "лв"),
        array("name" => "Vanuatu","short_name" => "VU","phone_code" => 678,"currency_name" => "VUV","currency_symbol" => "VT"),
        array("name" => "Venezuela","short_name" => "VE","phone_code" => 58,"currency_name" => "VEF","currency_symbol" => "Bs"),
        array("name" => "Viet Nam","short_name" => "VN","phone_code" => 84,"currency_name" => "VND","currency_symbol" => "₫"),
        array("name" => "Virgin Islands, British","short_name" => "VG","phone_code" => 1284,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Virgin Islands, U.s.","short_name" => "VI","phone_code" => 1340,"currency_name" => "USD","currency_symbol" => "$"),
        array("name" => "Wallis and Futuna","short_name" => "WF","phone_code" => 681,"currency_name" => "XPF","currency_symbol" => "₣"),
        array("name" => "Western Sahara","short_name" => "EH","phone_code" => 212,"currency_name" => "MAD","currency_symbol" => "MAD"),
        array("name" => "Yemen","short_name" => "YE","phone_code" => 967,"currency_name" => "YER","currency_symbol" => "﷼"),
        array("name" => "Zambia","short_name" => "ZM","phone_code" => 260,"currency_name" => "ZMW","currency_symbol" => "ZK"),
        array("name" => "Zimbabwe","short_name" => "ZW","phone_code" => 263,"currency_name" => "ZWL","currency_symbol" => "$")
    );
    foreach ($countries as $key => $country) {
        Country::create([
            'name' => $country['name'],
            'short_name' => $country['short_name'],
            'currency_name' => $country['currency_name'],
            'currency_symbol' => $country['currency_symbol'],
            'gst' => '0',
            'phone_code' => $country['phone_code'],
        ]);
    }
    return 'done';
});
Route::get('insert-usa-states', function () {
    $states = [
        'AL' => 'Alabama',
        // 'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        'DC' => 'District of Columbia',
        'AS' => 'American Samoa',
        'GU' => 'Guam',
        'MP' => 'Northern Mariana Islands',
        'PR' => 'Puerto Rico',
        'UM' => 'United States Minor Outlying Islands',
        'VI' => 'Virgin Islands, U.S.',
    ];
    foreach ($states as $key => $state) {
        CountryHasState::create([
            'country_id' => 25,
            'name' => $state,
            'store_online_payment' => 0.00,
            'store_cash_payment' => 0.00,
            'restaurant_online_payment' => 0.00,
            'restaurant_cash_payment' => 0.00,
        ]);
    }
    return 'Done';
});
Route::get('/', 'HomeController@index')->name('home');
Route::get('custom-token-add', 'HomeController@add_token')->name('custom-token-add');
Route::get('gomt-transaction-status/{txn}', 'HomeController@gomt_transaction_status')->name('gomt-transaction-status');
// Route::get('get-base-url', 'HomeController@baseUrl')->name('get-base-url');
Route::get('terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
Route::get('about-us', 'HomeController@about_us')->name('about-us');
Route::get('contact-us', 'HomeController@contact_us')->name('contact-us');
Route::get('privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::post('newsletter/subscribe', 'NewsletterController@newsLetterSubscribe')->name('newsletter.subscribe');

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', 'PaymentController@payment')->name('payment-mobile');
    Route::get('set-payment-method/{name}', 'PaymentController@set_payment_method')->name('set-payment-method');
});

// SSLCOMMERZ Start
/*Route::get('/example1', 'SslCommerzPaymentController@exampleEasyCheckout');
Route::get('/example2', 'SslCommerzPaymentController@exampleHostedCheckout');*/
Route::post('pay-ssl', 'SslCommerzPaymentController@index');
Route::post('/success', 'SslCommerzPaymentController@success');
Route::post('/fail', 'SslCommerzPaymentController@fail');
Route::post('/cancel', 'SslCommerzPaymentController@cancel');
Route::post('/ipn', 'SslCommerzPaymentController@ipn');
//SSLCOMMERZ END

/*paypal*/
/*Route::get('/paypal', function (){return view('paypal-test');})->name('paypal');*/
Route::post('pay-paypal', 'PaypalPaymentController@payWithpaypal')->name('pay-paypal');
Route::get('paypal-status', 'PaypalPaymentController@getPaymentStatus')->name('paypal-status');
/*paypal*/


/*coinbase*/
Route::get('pay-coinbase', 'CoinbasePaymentController@payWithCoinbase')->name('pay-coinbase');
// Route::get('pay-coinbase/{customer_id}/{order_id}', 'CoinbasePaymentController@payWithCoinbase')->name('pay-coinbase');
Route::get('pay-coinbase/success/{order_id}/{transaction_ref}/{platform}/{customer_id}', 'CoinbasePaymentController@success')->name('pay-coinbase.success');
Route::get('pay-coinbase/fail/{order_id}/{platform}/{customer_id}', 'CoinbasePaymentController@fail')->name('pay-coinbase.fail');

/*Route::get('stripe', function (){
return view('stripe-test');
});*/

Route::get('pay-stripe', 'StripePaymentController@payment_process_3d')->name('pay-stripe');
Route::get('pay-stripe/success', 'StripePaymentController@success')->name('pay-stripe.success');
Route::get('pay-stripe/fail', 'StripePaymentController@fail')->name('pay-stripe.fail');

// Get Route For Show Payment Form
Route::get('paywithrazorpay', 'RazorPayController@payWithRazorpay')->name('paywithrazorpay');
Route::post('payment-razor/{order_id}', 'RazorPayController@payment')->name('payment-razor');

/*Route::fallback(function () {
return redirect('/admin/auth/login');
});*/

Route::get('payment-success', 'PaymentController@success')->name('payment-success');
Route::get('payment-fail', 'PaymentController@fail')->name('payment-fail');

///////////////////////////////////////////////////////////////////////////////////////////////////
// JazzCash payment
///////////////////////////////////////////////////////////////////////////////////////////////////

Route::get('/jazzcash-checkout/{product_id}', [JazzcashController::class, 'show'])->name('jazzcash.show');
Route::post('/checkout-jazzcash', [JazzcashController::class, 'checkOut'])->name('jazzcash.checkout');

///////////////////////////////////////////////////////////////////////////////////////////////////
// JazzCash payment
///////////////////////////////////////////////////////////////////////////////////////////////////

Route::get('coinbase-payment-success/{order_id}/{customer_id}', 'PaymentController@coinbase_success')->name('coinbase-payment-success');
Route::get('coinbase-payment-fail/{order_id}/{customer_id}', 'PaymentController@coinbase_fail')->name('coinbase-payment-fail');

//senang pay
Route::match(['get', 'post'], '/return-senang-pay', 'SenangPayController@return_senang_pay')->name('return-senang-pay');

// paymob
Route::post('/paymob-credit', 'PaymobController@credit')->name('paymob-credit');
Route::get('/paymob-callback', 'PaymobController@callback')->name('paymob-callback');

//paystack
Route::post('/paystack-pay', 'PaystackController@redirectToGateway')->name('paystack-pay');
Route::get('/paystack-callback', 'PaystackController@handleGatewayCallback')->name('paystack-callback');
Route::get('/paystack', function () {
    return view('paystack');
});


// The route that the button calls to initialize payment
Route::post('/flutterwave-pay', 'FlutterwaveController@initialize')->name('flutterwave_pay');
// The callback url after a payment
Route::get('/rave/callback', 'FlutterwaveController@callback')->name('flutterwave_callback');


// The callback url after a payment
Route::get('mercadopago/home', 'MercadoPagoController@index')->name('mercadopago.index');
Route::post('mercadopago/make-payment', 'MercadoPagoController@make_payment')->name('mercadopago.make_payment');
Route::get('mercadopago/get-user', 'MercadoPagoController@get_test_user')->name('mercadopago.get-user');

//paytabs
Route::any('/paytabs-payment', 'PaytabsController@payment')->name('paytabs-payment');
Route::any('/paytabs-response', 'PaytabsController@callback_response')->name('paytabs-response');

//bkash
Route::group(['prefix' => 'bkash'], function () {
    // Payment Routes for bKash
    Route::post('get-token', 'BkashPaymentController@getToken')->name('bkash-get-token');
    Route::post('create-payment', 'BkashPaymentController@createPayment')->name('bkash-create-payment');
    Route::post('execute-payment', 'BkashPaymentController@executePayment')->name('bkash-execute-payment');
    Route::get('query-payment', 'BkashPaymentController@queryPayment')->name('bkash-query-payment');
    Route::post('success', 'BkashPaymentController@bkashSuccess')->name('bkash-success');

    // Refund Routes for bKash
    // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
    // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
});

// The callback url after a payment PAYTM
Route::get('paytm-payment', 'PaytmController@payment')->name('paytm-payment');
Route::any('paytm-response', 'PaytmController@callback')->name('paytm-response');

// The callback url after a payment LIQPAY
Route::get('liqpay-payment', 'LiqPayController@payment')->name('liqpay-payment');
Route::any('liqpay-callback', 'LiqPayController@callback')->name('liqpay-callback');


Route::get('/test', function () {
    dd('Hello tester');
});

Route::get('module-test', function () {
});

//Restaurant Registration
Route::group(['prefix' => 'store', 'as' => 'restaurant.'], function () {
    Route::get('apply', 'VendorController@create')->name('create');
    Route::post('apply', 'VendorController@store')->name('store');
});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', 'DeliveryManController@create')->name('create');
    Route::post('apply', 'DeliveryManController@store')->name('store');
});
