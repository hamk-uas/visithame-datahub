<?php
class Product
{
    public $product;

    public $images;
    public $cover_image_url;
    public $cover_image_copyright;
    public $cover_image_alt;

    public $official_name;
    public $business_name;
    public $contact_information;
    public $social_media;
    public $webshop_link;

    public $product_accessibility;
    public $product_address_city;
    public $product_address_latitude;
    public $product_address_longitude;
    public $product_address_postal_area;
    public $product_address_street_name;
    public $product_description;
    public $product_language;
    public $product_link;
    public $product_name;
    public $product_opening_hours; // Array, openFrom, openTo, weekday
    // public $product_pricings; // Array, fromPrice, toPrice
    public $product_type;
    public $product_pricing;
    public $tags;
    public $months;
    public $videos;

    public $website_url;
    public $logo;

    function __construct($product)
    {
        global $wpdb;
        $database = new DataHubDatabase();
        $this->product = $product;

        $cover_photo = null;
        $cover_photo_copyright = null;
        $cover_photo_alt = null;
        $image_deconstructed = array();
        $image_keys = array('large_url', 'cover_photo', 'copyright', 'alt_text', 'orientation', 'thumbnail_url');
        $image_data = explode(";:;", $product->ImageData);
        foreach ($image_data as $image_row) {
            $images_with_keys = array_combine($image_keys, explode(";", $image_row));
            array_push($image_deconstructed, $images_with_keys);
            if ($cover_photo == null && $images_with_keys['cover_photo'] == 1) {
                $cover_photo = $images_with_keys['large_url'];
                $cover_photo_copyright = $images_with_keys['copyright'];
                $cover_photo_alt = $images_with_keys['alt_text'];
            }
        }

        $this->cover_image_alt = $cover_photo_alt;
        $this->cover_image_copyright = $cover_photo_copyright;
        $this->images = $image_deconstructed;
        $this->cover_image_url = $cover_photo;
        $this->official_name = $product->official_name;
        $this->business_name = $product->business_name;
        $this->contact_information = $this->deconstruct_object(array('email', 'phone'), $product->ContactData);
        $this->social_media = $this->deconstruct_object(array('link_type', 'url'), $product->SocialData);
        $this->webshop_link = $product->webshop_url;
        $this->product_accessibility = $product->accessible_product;
        $this->product_address_city = $database->get_municipality($product->city_code);
        $this->product_address_latitude = $product->latitude;
        $this->product_address_longitude = $product->longitude;
        $this->product_address_postal_area = $product->postal_area;
        $this->product_address_street_name = $product->street_name;
        $this->product_description = $product->description;
        $this->product_language = $product->language;
        $this->product_link = $product->url;
        $this->product_name = $product->name;
        $this->product_opening_hours = $this->deconstruct_object(array('open_from', 'open_to', 'weekday'), $product->OpeningData);
        $this->product_pricing = $this->deconstruct_object(array('from_price', 'to_price'), $product->PriceData);
        $this->product_type = $product->product_type;
        $this->tags = $this->deconstruct_object(array('tag'), $product->TagData);
        $this->videos = $this->deconstruct_object(array('title', 'video_url'), $product->VideoData);
        $this->months = explode(",", $product->MonthData);
        $this->website_url = $product->website_url;
        $this->logo = json_decode(json_encode(array('logo' => $product->logo_url, 'logo_thumbnail' => $product->logo_thumbnail_url)));
    }

    function get_product()
    {
        return $this->product;
    }


    function render_title()
    {
        /**
         * Convert specific characters to url encoded form. 
         */
        $url = $this->cover_image_url;
        $url = str_replace('(', '%28', $url);
        $url = str_replace(')', '%29', $url);

        $content = '';
        $content .= '<div class="datahub__title_column" style="flex: 100%;">';
        $content .= '<div class="datahub-title-image" style="background-image: url(' . $url . ');" ' . 'data-copyright="&copy; ' . $this->cover_image_copyright   . '"' . 'alt="' . $this->cover_image_alt . '">';
        $content .= sprintf('<h2 class="datahub-title-text">%s</h2>', $this->product_name);
        $content .= '<div class="datahub-title-links">';

        if ($this->product_link != null && $this->product_link != '-' && $this->product_link != 'http://-') {
            $content .= '<a class="datahub-icon-link" href="' . $this->product_link . '">' . '<svg class="datahub-icon icon-link"><use xlink:href="#icon-link"></use></svg>' . '<div class="datahub-link-text">WWW</div></a>'; //'<span class="material-icons datahub-icon ">link</span>'
        }

        if ($this->webshop_link != null) {
            $content .= '<a class="datahub-icon-link" href="' . $this->webshop_link . '">' . '<svg class="datahub-icon icon-cart"><use xlink:href="#icon-cart"></use></svg>' . '<div class="datahub-link-text">' . $this->translations_for_product($this->product_language, 'store') . '</div></a>'; //'<span class="material-icons datahub-icon ">payments</span>'
        }

        if ($this->social_media != null) {
            foreach ($this->social_media as $social_link) {
                if ($social_link != null) {
                    if ($social_link->url != 'http://-' && $social_link->url != null) {
                        $content .= sprintf('<a class="datahub-icon-link" title="%s" href="%s">' . $this->check_link($social_link) . '<div class="datahub-link-text">%s</div></a>', $social_link->link_type, $social_link->url, $social_link->link_type);
                    }
                }
            }
        }

        $content .= '</div>'; // .datahub-title-links
        $content .= '</div>'; // .datahub-title-image
        $content .= '</div>'; // .datahub__title_column

        return $content;
    }

    function check_link($link)
    {
        $svg = null;
        switch ($link->link_type) {
            case "facebook":
                $svg = '<svg class="datahub-icon  icon-facebook2"><use xlink:href="#icon-facebook2"></use></svg>';
                break;
            case "instagram":
                $svg = '<svg class="datahub-icon  icon-instagram"><use xlink:href="#icon-instagram"></use></svg>';
                break;
            case "weibo":
                $svg = '<svg class="datahub-icon  icon-sina-weibo"><use xlink:href="#icon-sina-weibo"></use></svg>';
                break;
            case "youtube":
                $svg = '<svg class="datahub-icon  icon-youtube"><use xlink:href="#icon-youtube"></use></svg>';
                break;
            case "vkontakte":
                $svg = '<svg class="datahub-icon  icon-vkontakte"><use xlink:href="#icon-vkontakte"></use></svg>';
                break;
            case "twitter":
                $svg = '<svg class="datahub-icon  icon-twitter"><use xlink:href="#icon-twitter"></use></svg>';
                break;
            default:
                $svg = '<svg></svg>';
        }
        return $svg;
    }

    function render_main_content()
    {
        $content = '';

        $content .= '<div class="datahub-main-content-column" id="datahub-main-content-column">';
        $content .= '<h2 class="datahub__product_name">' . $this->product_name . '</h2>';

        $content .= $this->product_description;
        $content .= '<div class="datahub-show-more-container" id="datahub-show-more-container">';
        $content .= '<a id="datahub__read_more_link" onclick="open_description(); return false;" href="#">' . $this->translations_for_product($this->product_language, 'read_more') . '</a>';
        $content .= '</div>';
        $content .= '</div>'; // .datahub-main-content-column

        $content = str_replace(" www.", " https://www.", $content);

        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        $content = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $content);

        $search  = array('/<p>__<\/p>/', '/([a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})/');
        $replace = array('<hr />', '<a href="mailto:$1">$1</a>');
        $content = preg_replace($search, $replace, $content);

        return $content;
    }

    function render_sidebar()
    {
        $content = '';
        $content .= '<div class="datahub-sidebar-column" id="datahub__sidebar">';

        $logo = $this->logo->logo;
        if (!$logo) {
            $logo = plugin_dir_url(dirname(__FILE__)) . 'images/Visit_Hame_logo_header_medium.png';
        }

        $content .= '<div class="datahub-logo"><img class="datahub-logo-image" src="' . $logo . '"></div>';
        $content .= '<ul>';
        $content .= '<h3 class="datahub__company_name">' . $this->business_name . '</h3>';
        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">email</span>';
        $content .= '<span title="Sähköposti"><a href="mailto:' . $this->contact_information[0]->email . '">' .  $this->contact_information[0]->email . '</a></span></li>';

        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">phone</span>';
        $content .= '<span title="Puhelin"><a href="tel:' . $this->contact_information[0]->phone . '">' . $this->contact_information[0]->phone . '</a></span></li>';

        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">web</span>';
        $url_data = parse_url($this->product_link);
        $content .= '<span><a href=' . $this->product_link . ' target="_blank">' .   $url_data['host']    . '</a></span></li>';

        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">place</span>';
        $address =  $this->product_address_street_name . ' ' . $this->product_address_city;
        $content .= '<span>' . $this->product_address_street_name . ' ' . $this->product_address_city . '</span></li>'; //.' '.$this->product_address_city;

        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">map</span>';
        $content .= '<span><a href="https://google.com/maps/?q=' . $address . '" target="blank">Google Maps</a></span></li>';

        # Price and open information hidden
        # [0] => Array ( [open_from] => 00:00:00 [open_to] => 00:00:00 [weekday] => friday
        // $ar_time =  $this->product_opening_hours;
        // $order = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        // usort($ar_time, function ($a, $b) use ($order) {
        //     $pos_a = array_search($a->weekday, $order);
        //     $pos_b = array_search($b->weekday, $order);
        //     return $pos_a - $pos_b;
        // });

        //    $ar_time [3]->open_to = '14.00';


        // $days_content = '';

        // foreach ($ar_time as $day) {
        //     if ($day->open_from != "00:00:00" or $day->open_to != "00:00:00") {
        //         $days_content .= '<tr><td>' . $this->translations_for_product($this->product_language, $day->weekday) . '</td><td>' . substr($day->open_from, 0, -3) . '</td><td>' . substr($day->open_to, 0, -3) . '</td> </tr>';
        //     }
        // }

        // if ($days_content != '') {
        //     $content .= '<li id="companyCalendar">';
        //     $content .= '<span class="material-icons">event</span>';
        //     $content .= '<span><table>  <tr><td>' . $this->translations_for_product($this->product_language, 'day') . '</td><td>' . $this->translations_for_product($this->product_language, 'open_from') . '</td><td>' . $this->translations_for_product($this->product_language, 'open_to') . '</td></tr>' . $days_content . ' </table></span></li>';
        // }
        // $price = '';
        // $price_from = $this->product_pricing[0]->from_price;
        // $price_to = $this->product_pricing[0]->to_price;
        // if ($price_from != 0 or $price_to != 0) {

        //     $content .= '<li id="companyPrice">';
        //     $content .= '<span class="material-icons">euro</span>';


        //     if ($price_to == 0) {
        //         $price = $this->translations_for_product($this->product_language, 'starting_from') . ' ' . $price_from . '€';
        //     } else {

        //         $price = $price_from . ' - ' . $price_to;
        //     }
        //     $content .= '<span>' . $price . '</span></li>';
        // }


        $content .= '<li class="datahub-sidebar-li">';
        $content .= '<span class="material-icons">event</span>';
        if (count($this->months) < 12) {
            $content .= '<span><table>';

            $ar_months =  $this->months;
            $monthsArr = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

            usort($ar_months, function ($a, $b) use ($monthsArr) {
                $pos_a = array_search($a, $monthsArr);
                $pos_b = array_search($b, $monthsArr);
                return $pos_a - $pos_b;
            });

            $trip_months = array();
            $last_month = -1;
            $i = -1;
            foreach ($ar_months as $month) {
                $this_month = DateTime::createFromFormat('M', $month)->format('n');
                if ($this_month != ($last_month + 1)) { // ($last_month + 1) % 12
                    $trip_months[++$i] = array($month);
                } else {
                    $trip_months[$i][] = $month;
                }
                $last_month = $this_month;
            }

            foreach ($trip_months as $month_list) {
                if (count($month_list) == 1) {
                    $content .= '<tr><td>' . $this->translations_for_product($this->product_language, $month_list[0]) . '</td></tr>';
                } else {
                    $content .= '<tr><td>' . $this->translations_for_product($this->product_language, $month_list[0]) . ' - ' . $this->translations_for_product($this->product_language, $month_list[count($month_list) - 1]) . '</td></tr>';
                }
            }
            $content .= '</table></span>';
        } else {
            $content .= '<span><table><tr><td>' . $this->translations_for_product($this->product_language, 'open_all_year') . '</td></tr></table></span>';
        }
        $content .= '</li>';
        $content .= '</ul>';
        $content .= '</div>'; // .datahub-sidebar-column

        return $content;
    }

    function render_images()
    {
        if (count($this->images) == 0) {
            return null;
        }
        $content = '';
        $content .= '<div class="datahub-divider-content"><div class="datahub-divider-title">' . $this->translations_for_product($this->product_language, 'images') . '</div><div class="datahub-divider"></div></div>';
        $content .= '<div class="datahub-media-column">';
        $content .= '<div class="datahub-lightbox-row-content" id="datahub-lightbox-row-content">';
        foreach ($this->images as $index => $image) {
            $content .= sprintf('<img src="%s" onclick="datahubOpenModal();datahubCurrentSlide(%s)" class="datahub-carousel-images datahub-lightbox-thumbnail" data-copyright="&copy; ' . $image['copyright'] . '" alt="' . $image['alt_text'] . '">', $image['thumbnail_url'], $index + 1);
        }
        if (count($this->images) > 1) {
            $content .= '<div id="datahub-images-carousel-prev">&#10094;</div>';
            $content .= '<div id="datahub-images-carousel-next">&#10095;</div>';
        }
        $content .= '</div>'; // datahub-lightbox-row-content

        $content .= '<div id="datahub-lightbox-modal" class="datahub-lightbox-modal">';
        $content .= '<div class="datahub-modal-content">';
        $content .= '<span class="datahub-close cursor" onclick="datahubCloseModal()">&times;</span>';
        foreach ($this->images as $index => $image) {
            $content .= '<div class="datahub-lightbox-slides">';
            $content .= sprintf('<div class="datahub-numbertext">%s/%s</div>', $index + 1, count($this->images));
            $content .= '<img id="full_image_' . ($index + 1) . '" data-image-url="' . $image['large_url'] . '" src="" title="' . $image['alt_text'] . '" style="width: 100%" ' . 'data-copyright="&copy; ' . $image['copyright']   . '" alt="' . $image['alt_text'] . '">';
            $content .= '</div>';  // datahub-lightbox-slides
        }

        $content .= '<a class="datahub-prev" onclick="datahubPlusSlides(-1)">&#10094;</a>';
        $content .= '<a class="datahub-next" onclick="datahubPlusSlides(1)">&#10095;</a>';

        $content .= '</div>'; // datahub-modal-content
        $content .= '</div>'; // datahub-lightbox-modal
        $content .= '</div>'; // datahub-media-column

        return $content;
    }

    function render_videos()
    {

        $content = '';
        if ($this->videos == null) {
            return null;
        }
        $content .= '<div class="datahub-divider-content"><div class="datahub-divider-title">' . $this->translations_for_product($this->product_language, 'videos') . '</div><div class="datahub-divider"></div></div>';
        $content .= '<div class="datahub-media-column">';

        if ($this->videos != null) {
            $content .= '<div class="datahub__video_row">';
            $content .= '<div class="datahub-video-links">';
            foreach ($this->videos as $video) {
                if ($video != null) {
                    $url_data = parse_url($video->video_url);
                    $content .= sprintf('<div class="datahub-video-link"><span class="datahub-icon material-icons">open_in_new</span><a href="%s" title="%s">%s - %s</a></div><br>', $video->video_url, $video->title, $video->title, $url_data['host']);
                }
            }
            $content .= '</div>'; // .datahub-video-links
            $content .= '</div>'; // .datahub__video_row
        }

        $content .= '</div>'; // datahub-media-column

        return $content;
    }

    private function deconstruct_object($keys, $data)
    {
        if ($data == null) {
            return null;
        }
        $deconstructed = array();
        $data_arr = explode("::", $data);
        foreach ($data_arr as $row) {
            if ($row != null) {
                $data_with_keys = array_combine($keys, explode(";", $row));
                array_push($deconstructed, json_decode(json_encode($data_with_keys)));
            }
        }

        return $deconstructed;
    }

    private function translations_for_product($language, $key)
    {
        $translations = array(
            'fi' => array(
                'day' => 'Päivä',
                'open_from' => 'Avataan',
                'open_to' => 'Suljetaan',
                'monday' => 'Ma',
                'tuesday' => 'Ti',
                'wednesday' => 'Ke',
                'thursday' => 'To',
                'friday' => 'Pe',
                'saturday' => 'La',
                'sunday' => 'Su',
                'starting_from' => 'Alkaen',
                'january' => 'Tammikuu',
                'february' => 'Helmikuu',
                'march' => 'Maaliskuu',
                'april' => 'Huhtikuu',
                'may' => 'Toukokuu',
                'june' => 'Kesäkuu',
                'july' => 'Heinäkuu',
                'august' => 'Elokuu',
                'september' => 'Syyskuu',
                'october' => 'Lokakuu',
                'november' => 'Marraskuu',
                'december' => 'Joulukuu',
                'open_all_year' => 'Avoinna ympäri vuoden',
                'read_more' => 'Lue lisää',
                'videos' => 'Videot',
                'images' => 'Kuvat',
                'store' => 'Kauppa'
            ),
            'en' => array(
                'day' => 'Day',
                'open_from' => 'Open from',
                'open_to' => 'Closed at',
                'monday' => 'Mon.',
                'tuesday' => 'Tue.',
                'wednesday' => 'Wed.',
                'thursday' => 'Thu.',
                'friday' => 'Fri.',
                'saturday' => 'Sat.',
                'sunday' => 'Sun.',
                'starting_from' => 'Starting from',
                'january' => 'January',
                'february' => 'February',
                'march' => 'March',
                'april' => 'April',
                'may' => 'May',
                'june' => 'June',
                'july' => 'July',
                'august' => 'August',
                'september' => 'September',
                'october' => 'October',
                'november' => 'November',
                'december' => 'December',
                'open_all_year' => 'Open all year round',
                'read_more' => 'Read more',
                'videos' => 'Videos',
                'images' => 'Images',
                'store' => 'Store'
            ),
            'de' => array(
                'day' => 'Tag',
                'open_from' => 'Offen von',
                'open_to' => 'Geöffnet für',
                'monday' => 'Mo.',
                'tuesday' => 'Di.',
                'wednesday' => 'Mi.',
                'thursday' => 'Do.',
                'friday' => 'Fr.',
                'saturday' => 'Sa.',
                'sunday' => 'So.',
                'starting_from' => 'Ab',
                'january' => 'Januar',
                'february' => 'Februar',
                'march' => 'März',
                'april' => 'April',
                'may' => 'Mai',
                'june' => 'Juni',
                'july' => 'Juli',
                'august' => 'August',
                'september' => 'September',
                'october' => 'Oktober',
                'november' => 'November',
                'december' => 'Dezember',
                'open_all_year' => 'Ganzjährig geöffnet',
                'read_more' => 'Weiterlesen',
                'videos' => 'Videos',
                'images' => 'Bilder',
                'store' => 'Handel'
            ),
            'ru' => array(
                'day' => 'день',
                'open_from' => 'Открыто с',
                'open_to' => 'Открыто до',
                'monday' => 'пн',
                'tuesday' => 'вт',
                'wednesday' => 'ср',
                'thursday' => 'чт',
                'friday' => 'пт',
                'saturday' => 'сб',
                'sunday' => 'вс',
                'starting_from' => 'От',
                'january' => 'Январь',
                'february' => 'Февраль',
                'march' => 'Март',
                'april' => 'Апрель',
                'may' => 'Май',
                'june' => 'Июнь',
                'july' => 'Июль',
                'august' => 'Август',
                'september' => 'Сентябрь',
                'october' => 'Октябрь',
                'november' => 'Ноябрь',
                'december' => 'Декабрь',
                'open_all_year' => 'Мы открыты круглый год',
                'read_more' => 'Подробнее',
                'videos' => 'Видео',
                'images' => 'Изображения',
                'store' => 'Магазин'
            ),
            'ja' => array(
                'day' => '日',
                'open_from' => 'から開く',
                'open_to' => '閉店',
                'monday' => '月曜日',
                'tuesday' => '火曜日',
                'wednesday' => '水曜日',
                'thursday' => '木曜日',
                'friday' => '金曜日',
                'saturday' => '土曜日',
                'sunday' => '日曜日',
                'starting_from' => 'から始まる',
                'january' => '一月',
                'february' => '二月',
                'march' => '三月',
                'april' => '四月',
                'may' => '五月',
                'june' => '六月',
                'july' => '七月',
                'august' => '八月',
                'september' => '九月',
                'october' => '十月',
                'november' => '十一月',
                'december' => '十二月',
                'open_all_year' => '年中無休',
                'read_more' => '続きを読む',
                'videos' => 'ビデオ',
                'images' => '画像',
                'store' => 'トレード'
            ),
            'zh' => array(
                'day' => '日',
                'open_from' => '打开自',
                'open_to' => '关门时间',
                'monday' => '星期一',
                'tuesday' => '星期二',
                'wednesday' => '星期三',
                'thursday' => '星期四',
                'friday' => '星期五',
                'saturday' => '星期六',
                'sunday' => '星期日',
                'starting_from' => '从...开始',
                'january' => '一 月',
                'february' => '二 月',
                'march' => '三 月',
                'april' => '四 月',
                'may' => '五 月',
                'june' => '六 月',
                'july' => '七 月',
                'august' => '八 月',
                'september' => '九 月',
                'october' => '十 月',
                'november' => '十一 月',
                'december' => '十二 月',
                'open_all_year' => '全年开放',
                'read_more' => '阅读更多',
                'videos' => '视频',
                'images' => '图片',
                'store' => '贸易'
            ),
            'sv' => array(
                'day' => 'Day',
                'open_from' => 'Öppet från',
                'open_to' => 'Stängt',
                'monday' => 'Mån',
                'tuesday' => 'Tis',
                'wednesday' => 'Ons',
                'thursday' => 'Tors',
                'friday' => 'Fre',
                'saturday' => 'Lör',
                'sunday' => 'Sön',
                'starting_from' => 'Med början från',
                'january' => 'Januari',
                'february' => 'Februari',
                'march' => 'Mars',
                'april' => 'April',
                'may' => 'Maj',
                'june' => 'Juni',
                'july' => 'Juli',
                'august' => 'Augusti',
                'september' => 'September',
                'october' => 'Oktober',
                'november' => 'November',
                'december' => 'December',
                'open_all_year' => 'Öppet året runt',
                'read_more' => 'Läs mer',
                'videos' => 'Videoklipp',
                'images' => 'Bilder',
                'store' => 'Handel'
            )
        );
        return $translations[$language][$key];
    }
}
