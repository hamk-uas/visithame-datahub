<?php
class DataHubDatabase
{
    function get_datahub_options()
    {
        $options = null;

        $options = get_option('datahub_option_name');
        if (empty($options['datahub_client_secret']) || empty($options['datahub_username']) || empty($options['datahub_password'])) {
            return "Connection details missing from plugin settings.";
        }

        return $options;
    }

    function insert_or_update_editor_tags($tags)
    {
        $table_editor_tags = array();

        foreach ($tags as $tag) {
            if ($tag != "" && $tag != null && !empty($tag)) {
                array_push($table_editor_tags, array(
                    'tag' => trim($tag->tag)
                ));
            }
        }
        $tags_done = $this->insert_or_update('visithame_tags', '(%s)', $table_editor_tags);

        $date = date('Y-m-d h:i:s', time());
        $done_up = $this->insert_or_update_time_updated('editor', $date);
        return true;
    }

    function insert_or_update_editor_municipality($city, $city_code)
    {
        global $wpdb;
        global $jal_db_version;
        $table_name = $wpdb->prefix . 'visithame_municipalities';

        $sql = "INSERT INTO $table_name (city, city_code) VALUES (%s, %s) ON DUPLICATE KEY UPDATE city = city";
        $sql = $wpdb->prepare($sql, $city, $city_code);
        if ($wpdb->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    function insert_or_update_time_updated($id = 'editor', $updated_at)
    {
        global $wpdb;
        global $jal_db_version;
        $table_name = $wpdb->prefix . 'visithame_updates';

        $sql = "INSERT INTO $table_name (target_field, updated_at) VALUES (%s, %s) ON DUPLICATE KEY UPDATE updated_at = %s";
        $sql = $wpdb->prepare($sql, $id, $updated_at, $updated_at);
        if ($wpdb->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    function get_tags()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visithame_tags';
        $tags = $wpdb->get_results("
    SELECT *
    FROM $table_name
    ");

        foreach ($tags as $tag) {
            $tag->checked = false;
        }

        return $tags;
    }

    function get_municipalities()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visithame_municipalities';
        $municipalities = $wpdb->get_results("
    SELECT city, city_code
    FROM $table_name
    ");

        foreach ($municipalities as $municipality) {
            $municipality->checked = false;
        }

        return $municipalities;
    }

    function get_municipality($city_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visithame_municipalities';
        $municipality = $wpdb->get_results("
        SELECT city
        FROM $table_name
        WHERE city_code = $city_code
        ");

        if (count($municipality) > 0) {
            return $municipality[0]->city;
        } else {
            return "Kanta-Häme";
        }
    }

    function insert_or_update_products($products)
    {
        $table_product = array();
        $table_company = array();
        $table_contact = array();
        $table_image = array();
        $table_info = array();
        $table_social = array();
        $table_postal_address = array();
        $table_postal_area = array();
        $table_video = array();
        $table_pricing = array();
        $table_tag = array();
        $table_available = array();
        $table_opening = array();
        $table_targets = array();

        $companies_added = array();
        $postal_area_added = array();

        foreach ($products as $product) {

            if (in_array($product->company->id, $companies_added) == false) {
                array_push($table_company, array(
                    'company_id' => $product->company->id,
                    'official_name' => $product->company->officialName,
                    'business_name' => $product->company->businessName,
                    'webshop_url' => $product->company->webshopUrl,
                    'website_url' => $product->company->websiteUrl,
                    'logo_url' => $product->company->logoUrl,
                    'logo_thumbnail_url' => $product->company->logoThumbnailUrl,
                    'updated_at' => $product->company->updatedAt
                ));

                foreach ($product->company->socialMediaLinks as $social_links) {

                    array_push($table_social, array(
                        'company_id' => $product->company->id,
                        'link_type' => $social_links->linkType,
                        'url' => $social_links->url
                    ));
                }
            }
            foreach ($product->contactDetails as $contact) {
                array_push($table_contact, array(
                    'company_id' => $product->companyId,
                    'product_id' => $product->id,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'contact_id' => $contact->id
                ));
            }
            foreach ($product->productImages as $image) {
                array_push($table_image, array(
                    'product_id' => $product->id,
                    'large_url' => $image->largeUrl,
                    'thumbnail_url' => $image->thumbnailUrl,
                    'copyright' => $image->copyright,
                    'alt_text' => $image->altText,
                    'orientation' => $image->orientation,
                    'cover_photo' => $image->coverPhoto
                ));
            }

            $url_fi = null;
            $url_en = null;
            $url_de = null;
            $url_ru = null;
            $url_sv = null;
            $url_ja = null;
            $url_zh = null;

            foreach ($product->productInformations as $info) {
                array_push($table_info, array(
                    'product_id' => $product->id,
                    'name' => trim($info->name),
                    'description' => $info->description,
                    'language' => $info->language,
                    'updated_at' => $info->updatedAt,
                    'url' => $info->url,
                    'webshop_url' => $info->webshopUrl

                ));
                if ($info->language == 'fi') {
                    $url_fi = $this->clean($info->name);
                }
                if ($info->language == 'en') {
                    $url_en = $this->clean($info->name);
                }
                if ($info->language == 'de') {
                    $url_de = $this->clean($info->name);
                }
                if ($info->language == 'sv') {
                    $url_sv = $this->clean($info->name);
                }
                if ($info->language == 'ja') {
                    $url_ja = $this->clean_cyr_ja_zh($info->name);
                }
                if ($info->language == 'zh') {
                    $url_zh = $this->clean_cyr_ja_zh($info->name);
                }
                if ($info->language == 'ru') {
                    // $url_ru = $this->clean($info->name);
                    $url_ru = $this->clean($this->transliterate($info->name));
                }
            }
            array_push($table_product, array(
                'product_id' => $product->id,
                'company_id' => $product->companyId,
                'accessible_product' => $product->accessible,
                'product_type' => $product->type,
                'url_primary' => $product->urlPrimary,
                'webshop_url_primary' => $product->webshopUrlPrimary,
                'product_url_fi' =>  $url_fi,
                'product_url_en' =>  $url_en,
                'product_url_de' =>  $url_de,
                'product_url_sv' =>  $url_sv,
                'product_url_ja' =>  $url_ja,
                'product_url_zh' =>  $url_zh,
                'product_url_ru' =>  $url_ru,
                'updated_at' => $product->updatedAt
            ));
            foreach ($product->postalAddresses as $postal_address) {
                $loc = str_replace(",", " ", substr($postal_address->location, 1, -1));
                if (strlen($loc) <= 6 || $loc == null) {
                    $loc = "0.00000 0.00000";
                }
                $loc = sprintf("POINT(%s)", $loc);
                array_push($table_postal_address, array(
                    'product_id' => $product->id,
                    'company_id' => $product->companyId,
                    'postal_code' => $postal_address->postalCode,
                    'street_name' => $postal_address->streetName,
                    'city' => $postal_address->city,
                    'location' => $loc,
                    'postal_area' => $postal_address->postalArea->postalArea,
                    'updated_at' => $postal_address->updatedAt
                ));
                if (in_array($postal_address->postalArea->postalCode, $postal_area_added) == false) {
                    if (substr($postal_address->postalArea->postalCode, 0, 2) == '11') {
                        array_push($table_postal_area, array(
                            'city_code' => 694,
                            'postal_area' => $postal_address->postalArea->postalArea,
                            'postal_code' => $postal_address->postalArea->postalCode
                        ));
                    } else {
                        array_push($table_postal_area, array(
                            'city_code' => $postal_address->postalArea->cityCode,
                            'postal_area' => $postal_address->postalArea->postalArea,
                            'postal_code' => $postal_address->postalArea->postalCode
                        ));
                    }
                    array_push($postal_area_added, $postal_address->postalArea->postalCode);
                }
            }
            foreach ($product->productVideos as $video) {
                array_push(
                    $table_video,
                    array(
                        'title' => $video->title,
                        'video_url' => $video->url,
                        'product_id' => $product->id
                    )
                );
            }

            foreach ($product->productPricings as $pricing) {
                array_push($table_pricing, array(
                    'product_id' => $product->id,
                    'from_price' => $pricing->fromPrice,
                    'to_price' => $pricing->toPrice,
                    'updated_at' => $pricing->updatedAt
                ));
            }
            foreach ($product->productTags as $tag) {
                array_push($table_tag, array(
                    'product_id' => $product->id,
                    'tag' => $tag->tag,
                    'updated_at' => $tag->updatedAt
                ));
            }
            foreach ($product->productAvailableMonths as $available) {
                array_push($table_available, array(
                    'product_id' => $product->id,
                    'month' => $available->month,
                    'updated_at' => $available->updatedAt
                ));
            }
            foreach ($product->openingHours as $hour) {
                array_push($table_opening, array(
                    'product_id' => $product->id,
                    'open_from' => $hour->openFrom,
                    'open_to' => $hour->openTo,
                    'weekday' => $hour->weekday,
                    'updated_at' => $hour->updatedAt
                ));
            }
            foreach ($product->productTargetGroups as $target_group) {
                array_push($table_targets, array(
                    'product_id' => $product->id,
                    'target_group' => $target_group->targetGroupId,
                    'updated_at' => $target_group->updatedAt
                ));
            }

            array_push($companies_added, $product->company->id);
        }

        global $wpdb;

        $table_prefix = $wpdb->prefix;
        $tables = array(
            $table_prefix . 'visithame_social_media_link',
            $table_prefix . 'visithame_opening_hours',
            $table_prefix . 'visithame_postal_area',
            $table_prefix . 'visithame_postal_address',
            $table_prefix . 'visithame_product_availability',
            $table_prefix . 'visithame_product_available_months',
            $table_prefix . 'visithame_product_image',
            $table_prefix . 'visithame_product_information',
            $table_prefix . 'visithame_product_pricing',
            $table_prefix . 'visithame_product_tag',
            $table_prefix . 'visithame_product_video',
            $table_prefix . 'visithame_product_pricing',
            $table_prefix . 'visithame_contact_details',
            $table_prefix . 'visithame_company',
            $table_prefix . 'visithame_product',
            $table_prefix . 'visithame_target_group'
        );

        foreach ($tables as $table) {
            $sql = "TRUNCATE TABLE $table";
            $wpdb->query($sql);
        }

        $done_p = $this->insert_or_update('visithame_product', '(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $table_product);
        $done_c = $this->insert_or_update('visithame_company', '(%s, %s, %s, %s, %s, %s, %s, %s)', $table_company);
        $done_co = $this->insert_or_update('visithame_contact_details', '(%s, %s, %s, %s, %s)', $table_contact);
        $done_im = $this->insert_or_update('visithame_product_image', '(%s, %s, %s, %s, %s, %s, %s)', $table_image);
        $done_in = $this->insert_or_update('visithame_product_information', '(%s, %s, %s, %s, %s, %s, %s)', $table_info);
        $done_so = $this->insert_or_update('visithame_social_media_link', '(%s, %s, %s)', $table_social);
        $done_pa = $this->insert_or_update('visithame_postal_address', '(%s, %s, %s, %s, %s, ST_PointFromText(%s), %s, %s)', $table_postal_address);
        $done_paa = $this->insert_or_update('visithame_postal_area', '(%s, %s, %s)', $table_postal_area);
        $done_v = $this->insert_or_update('visithame_product_video', '(%s, %s, %s)', $table_video);
        $done_p = $this->insert_or_update('visithame_product_pricing', '(%s, %s, %s, %s)', $table_pricing);
        $done_t = $this->insert_or_update('visithame_product_tag', '(%s, %s, %s)', $table_tag);
        $done_a = $this->insert_or_update('visithame_product_available_months', '(%s, %s, %s)', $table_available);
        $done_o = $this->insert_or_update('visithame_opening_hours', '(%s, %s, %s, %s, %s)', $table_opening);
        $done_ta = $this->insert_or_update('visithame_target_group', '(%s, %s, %s)', $table_targets);

        $date = date('Y-m-d h:i:s', time());
        $done_up = $this->insert_or_update_time_updated('products', $date);

        return true;
    }

    function clean($string)
    {
        $string = mb_strtolower($string);
        $string = trim($string);
        $string = str_replace(' ', '-', $string);
        $string = str_replace('ä', 'a', $string);
        $string = str_replace('å', 'a', $string);
        $string = str_replace('ö', 'o', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

    function clean_cyr_ja_zh($string)
    {
        $string = mb_strtolower($string);
        $string = str_replace(' ', '-', $string);
        $string = str_replace(',', '', $string);
        $string = str_replace('.', '', $string);

        return preg_replace('/-+/', '-', $string);
    }

    function transliterate($textcyr = null, $textlat = null)
    {
        $cyr = array(
            'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
            'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я'
        );
        $lat = array(
            'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
            'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q'
        );
        if ($textcyr) return str_replace($cyr, $lat, $textcyr);
        else if ($textlat) return str_replace($lat, $cyr, $textlat);
        else return null;
    }

    function insert_page($page_id)
    {
        global $wpdb;
        global $jal_db_version;
        $table_name = $wpdb->prefix . 'visithame_translations';

        $sql = "INSERT INTO $table_name (page_id) VALUES (%s) ON DUPLICATE KEY UPDATE page_id = VALUES(page_id)";
        // var_dump($sql); // debug
        $sql = $wpdb->prepare($sql, $page_id);
        // var_dump($sql); // debug
        if ($wpdb->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    function insert_or_update(string $table, string $placeholders, array $values)
    {
        global $wpdb;
        $table = $wpdb->prefix . $table;
        $values_to_insert = array();
        $unpacked_keys = array();

        if (count($values) > 0) {
            foreach ($values as $key => $value) {
                foreach ($value as $row_key => $row_value) {
                    if (count($unpacked_keys) < count($value)) {
                        array_push($unpacked_keys, $row_key);
                    }
                    array_push($values_to_insert, $row_value);
                }
                $place[] = $placeholders;
            }

            $query = "INSERT INTO $table (" . implode(',', $unpacked_keys) . ") VALUES ";
            $query .= implode(', ', $place);
            $query .= " ON DUPLICATE KEY UPDATE ";

            foreach ($unpacked_keys as $key) {
                $query .= $key . " = VALUES(" . $key . "), ";
            }

            $sql = $wpdb->prepare("$query ", $values_to_insert);
            $sql = substr($sql, 0, -3);

            if ($wpdb->query($sql)) {
                return true;
            } else {
                return false;
            }
        }
    }

    function get_products_list($categories, $municipalities, $language)
    {
        global $wpdb;
        $table_product = $wpdb->prefix . 'visithame_product';
        $table_postal_address = $wpdb->prefix . 'visithame_postal_address';
        $table_postal_area = $wpdb->prefix . 'visithame_postal_area';
        $table_company = $wpdb->prefix . 'visithame_company';
        $table_information = $wpdb->prefix . 'visithame_product_information';
        $table_image = $wpdb->prefix . 'visithame_product_image';
        $table_tag = $wpdb->prefix . 'visithame_product_tag';
        $table_target = $wpdb->prefix . 'visithame_target_group';

        $tags = '';

        $sql = "";
        $sql .= "SELECT Product.product_id, Information.description, Area.city_code, Information.name, Product.product_url_fi, Product.product_url_en, Product.product_url_de, Product.product_url_ru, Product.product_url_sv, Product.product_url_zh, Product.product_url_ja, ";
        // $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Images.large_url, Images.cover_photo, Images.copyright, Images.alt_text, Images.orientation, Images.thumbnail_url)) SEPARATOR ';:;') AS ImageData, ";
        $sql .= "Images.large_url, Images.copyright, Images.alt_text, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Target.target_group))) AS TargetData ";
        $sql .= "FROM $table_product AS Product ";
        $sql .= "LEFT JOIN $table_image Images ON Product.product_id = Images.product_id ";
        $sql .= "LEFT JOIN $table_information Information ON Product.product_id = Information.product_id ";
        $sql .= "LEFT JOIN $table_company Company ON Product.company_id = Company.company_id ";
        $sql .= "LEFT JOIN $table_postal_address Address ON Product.product_id = Address.product_id ";
        $sql .= "LEFT JOIN $table_postal_area Area ON Address.postal_area = Area.postal_area ";
        $sql .= "LEFT JOIN $table_tag Tag ON Product.product_id = Tag.product_id ";
        $sql .= "LEFT JOIN $table_target Target ON Product.product_id = Target.product_id ";
        $sql .= $wpdb->prepare("WHERE Information.language = %s ", $language);
        $sql .= "AND Images.cover_photo = 1 ";
        #$sql .= "AND Images.orientation = 'landscape' AND (";
        $sql .= "AND (";
        $categories_array = explode(",", $categories);
        foreach ($categories_array as $category) {
            $tags .= $wpdb->prepare("Tag.tag = %s OR ", $category);
        }
        $sql .= substr($tags, 0, -4) . ") ";
        $sql .= "AND Information.description NOT LIKE '%englanniksi%' AND (";
        $m = '';
        $municipalities_array = explode(",", $municipalities);
        foreach ($municipalities_array as $municipality) {
            $m .= $wpdb->prepare("Area.city_code = %s OR ", $municipality);
        }
        $sql .= substr($m, 0, -4) . ") ";
        $sql .= "GROUP BY Product.product_id ";
        $sql .= "ORDER BY Information.name COLLATE utf8mb4_swedish_ci ";

        $products = $wpdb->get_results($sql);

        return $products;
    }

    function datahub_get_product_details_from_database($id, $language)
    {
        global $wpdb;
        $table_product = $wpdb->prefix . 'visithame_product';
        $table_postal_address = $wpdb->prefix . 'visithame_postal_address';
        $table_postal_area = $wpdb->prefix . 'visithame_postal_area';
        $table_company = $wpdb->prefix . 'visithame_company';
        $table_information = $wpdb->prefix . 'visithame_product_information';
        $table_image = $wpdb->prefix . 'visithame_product_image';
        $table_tag = $wpdb->prefix . 'visithame_product_tag';
        $table_pricing = $wpdb->prefix . 'visithame_product_pricing';
        $table_opening = $wpdb->prefix . 'visithame_opening_hours';
        $table_social = $wpdb->prefix . 'visithame_social_media_link';
        $table_video = $wpdb->prefix . 'visithame_product_video';
        $table_contact = $wpdb->prefix . 'visithame_contact_details';
        $table_available = $wpdb->prefix . 'visithame_product_available_months';
        $table_target = $wpdb->prefix . 'visithame_target_group';

        $sql = "";
        $sql .= "SELECT DISTINCT $table_product.company_id, $table_product.product_id, $table_product.accessible_product, $table_product.product_type,  $table_product.product_url_fi,  $table_product.product_url_en,  $table_product.product_url_de,  $table_product.product_url_ru,  $table_product.product_url_sv,  $table_product.product_url_zh,  $table_product.product_url_ja, ";
        #$sql .= "Company.official_name, Company.business_name, Company.website_url, Company.logo_url, Company.logo_thumbnail_url, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Images.large_url, Images.cover_photo, Images.copyright, Images.alt_text, Images.orientation, Images.thumbnail_url)) SEPARATOR ';:;') AS ImageData, ";
        $sql .= "Information.name, Information.description, Information.language, Information.webshop_url, Information.url, ";
        $sql .= "Address.postal_code, Address.postal_area, ST_X(Address.location) AS latitude, ST_Y(Address.location) AS longitude, Address.street_name, Address.city, ";
        $sql .= "Area.city_code, Area.postal_code, ";
        $sql .= "GROUP_CONCAT(DISTINCT(Tags.tag)) AS TagData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Prices.from_price, Prices.to_price)) SEPARATOR '::') AS PriceData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Hours.open_from, Hours.open_to, Hours.weekday)) SEPARATOR '::') AS OpeningData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Contacts.email, Contacts.phone)) SEPARATOR '::') AS ContactData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Videos.title, Videos.video_url)) SEPARATOR '::') AS VideoData, ";
        // $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Social.link_type, Social.url))) AS SocialData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Months.month))) AS MonthData, ";
        $sql .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Target.target_group))) AS TargetData ";
        $sql .= "FROM $table_product ";
        $sql .= "LEFT JOIN $table_image Images ON $table_product.product_id = Images.product_id ";
        $sql .= "LEFT JOIN $table_information Information ON $table_product.product_id = Information.product_id ";
        $sql .= "LEFT JOIN $table_company Company ON $table_product.company_id = Company.company_id ";
        $sql .= "LEFT JOIN $table_postal_address Address ON $table_product.product_id = Address.product_id ";
        $sql .= "LEFT JOIN $table_postal_area Area ON Address.postal_area = Area.postal_area ";
        $sql .= "LEFT JOIN $table_tag Tags ON $table_product.product_id = Tags.product_id ";
        $sql .= "LEFT JOIN $table_pricing Prices ON $table_product.product_id = Prices.product_id ";
        $sql .= "LEFT JOIN $table_opening Hours ON $table_product.product_id = Hours.product_id ";
        $sql .= "LEFT JOIN $table_contact Contacts ON $table_product.product_id = Contacts.product_id ";
        $sql .= "LEFT JOIN $table_available Months ON $table_product.product_id = Months.product_id ";
        $sql .= "LEFT JOIN $table_target Target ON $table_product.product_id = Target.product_id ";
        $sql .= "LEFT JOIN $table_video Videos ON $table_product.product_id = Videos.product_id ";
        // $sql .= "LEFT JOIN $table_social Social ON Company.company_id = Social.company_id ";
        $sql .= $wpdb->prepare("WHERE Information.language = %s ", $language);
        // $sql .= $wpdb->prepare("AND $table_product.product_id = %s ", $id);

        switch ($language) {
            case "fi":
                // $url = $product->product_url_fi;
                $sql .= $wpdb->prepare("AND $table_product.product_url_fi = %s ", $id);
                break;
            case "en":
                $sql .= $wpdb->prepare("AND $table_product.product_url_en = %s ", $id);
                break;
            case "de":
                $sql .= $wpdb->prepare("AND $table_product.product_url_de = %s ", $id);
                break;
            case "sv":
                $sql .= $wpdb->prepare("AND $table_product.product_url_sv = %s ", $id);
                break;
            case "ru":
                $sql .= $wpdb->prepare("AND $table_product.product_url_ru = %s ", $id);
                break;
            case "zh":
                $sql .= $wpdb->prepare("AND $table_product.product_url_zh = %s ", $id);
                break;
            case "ja":
                $sql .= $wpdb->prepare("AND $table_product.product_url_ja = %s ", $id);
                break;
            default:
                $sql .= $wpdb->prepare("AND $table_product.product_url_en = %s ", $id);
        }

        $product = $wpdb->get_results($sql);

        $company_id = $product[0]->company_id;

        $sql_2 = "";
        $sql_2 .= "SELECT $table_company.company_id, $table_company.official_name, $table_company.business_name, $table_company.website_url, $table_company.logo_url, $table_company.logo_thumbnail_url, ";
        $sql_2 .= "GROUP_CONCAT(DISTINCT(CONCAT_WS(';', Social.link_type, Social.url)) SEPARATOR '::') AS SocialData ";
        $sql_2 .= "FROM $table_company ";
        $sql_2 .= "LEFT JOIN $table_social Social ON $table_company.company_id = Social.company_id ";
        $sql_2 .= "WHERE $table_company.company_id = '$company_id' ";
        $sql_2 .= "AND Social.company_id = '$company_id' ";

        $company = $wpdb->get_results($sql_2);

        foreach ($company as $object) {
            foreach ($object as $key => $value) {
                $product[0]->$key = $value;
            }
        }

        return $product;
    }

    function get_pages()
    {
        global $wpdb;
        global $jal_db_version;
        $table_name = $wpdb->prefix . 'visithame_translations';

        $sql = "SELECT page_id FROM $table_name ";

        $pages = $wpdb->get_results($sql);
        return $pages;
    }

    function create_database_tables()
    {
        global $wpdb;
        global $jal_db_version;
        $jal_db_version = '1.0';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table_name = $wpdb->prefix . 'visithame_translations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            page_id mediumint(9),
            PRIMARY KEY  (id)
        ) $charset_collate;
        ";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_tags';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        tag VARCHAR(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY tag (tag)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_municipalities';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        city VARCHAR(128) DEFAULT '' NOT NULL,
        city_code VARCHAR(3) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY city (city)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_updates';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        target_field VARCHAR(10) NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (target_field)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) UNIQUE NOT NULL,
        company_id VARCHAR(64) NOT NULL,
        accessible_product BOOLEAN NOT NULL,
        product_type VARCHAR(255) NOT NULL,
        url_primary VARCHAR(320) NOT NULL,
        webshop_url_primary VARCHAR(320) NOT NULL,
        product_url_fi VARCHAR(100),
        product_url_en VARCHAR(100),
        product_url_de VARCHAR(100),
        product_url_sv VARCHAR(100),
        product_url_ja VARCHAR(100),
        product_url_zh VARCHAR(100),
        product_url_ru VARCHAR(100),
        updated_at DATETIME,
        PRIMARY KEY  (id),
        INDEX product_url_fi (product_url_fi),
        INDEX product_url_en (product_url_en),
        INDEX product_url_de (product_url_de),
        INDEX product_url_sv (product_url_sv),
        INDEX product_url_ja (product_url_ja),
        INDEX product_url_zh (product_url_zh),
        INDEX product_url_ru (product_url_ru)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_company';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        company_id VARCHAR(64) NOT NULL,
        official_name VARCHAR(255) NOT NULL,
        business_name VARCHAR(255) NOT NULL,
        webshop_url VARCHAR(320) NOT NULL,
        website_url VARCHAR(320) NOT NULL,
        logo_url varchar(320) NOT NULL,
        logo_thumbnail_url varchar(320) NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY company_id (company_id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_contact_details';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        company_id VARCHAR(64) NOT NULL,
        product_id VARCHAR(64) NOT NULL,
        email VARCHAR(128) NOT NULL,
        phone VARCHAR(64) NOT NULL,
        contact_id VARCHAR(64) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY contact_id (contact_id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_video';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        title VARCHAR(128) NOT NULL,
        video_url VARCHAR(320) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id, video_url)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_tag';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        tag VARCHAR(255) NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id)
        ) $charset_collate;";
        // -- UNIQUE KEY tag (tag)

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_pricing';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        from_price DOUBLE (10, 2) NOT NULL,
        to_price DOUBLE (10, 2) NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_information';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        language enum('de', 'en', 'fi', 'ja', 'ru', 'sv', 'zh') DEFAULT 'en' NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        updated_at DATETIME,
        url VARCHAR(320),
        webshop_url VARCHAR(320),
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id, language)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_image';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        large_url VARCHAR(320) NOT NULL,
        thumbnail_url VARCHAR(320) NOT NULL,
        copyright VARCHAR(255) NOT NULL,
        alt_text VARCHAR(255) NOT NULL,
        cover_photo BOOLEAN NOT NULL,
        orientation enum('landscape', 'portrait') NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id, large_url)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_available_months';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        month enum('april', 'august', 'december', 'february', 'january', 'july', 'june', 'march', 'may', 'november', 'october', 'september') NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_months (product_id, month)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_product_availability';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        doors_open_at datetime DEFAULT '0000-00-00 00:00:00',
        end_date date,
        end_time time,
        nro_of_tickets int,
        start_date date,
        start_time time,
        updated_at DATETIME,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_postal_address';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        company_id VARCHAR(64) NOT NULL,
        postal_code VARCHAR(5) NOT NULL,
        postal_area VARCHAR(64) NOT NULL,
        location POINT,
        street_name VARCHAR(255) NOT NULL,
        city VARCHAR(255) NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id, company_id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_postal_area';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        city_code VARCHAR(4) NOT NULL,
        postal_area VARCHAR(255) NOT NULL,
        postal_code VARCHAR(5) NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_opening_hours';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) NOT NULL,
        open_from time NOT NULL,
        open_to time NOT NULL,
        weekday enum('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_days (weekday, product_id)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_social_media_link';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        company_id VARCHAR(64) NOT NULL,
        link_type enum('facebook', 'instagram', 'twitter', 'vkontakte', 'weibo', 'youtube') NOT NULL,
        url VARCHAR(320) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (company_id, url)
        ) $charset_collate;";

        dbDelta($sql);

        $table_name = $wpdb->prefix . 'visithame_target_group';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id VARCHAR(64) DEFAULT '' NOT NULL,
        target_group VARCHAR(20) NOT NULL,
        updated_at DATETIME,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_id (product_id, target_group)
        ) $charset_collate;";

        dbDelta($sql);

        add_option('jal_db_version', $jal_db_version);
    }

    function add_initial_values()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'visithame_tags';
        $wpdb->query("INSERT INTO $table
            (tag)
            VALUES
            ('amusement_park'),('animal_parks_farms'),('architecture'),('artisan'),('autumn_colours_ruska'),
            ('bars_nightlife'),('bed_breakfast_apartments_guest_house'),('berry_mushroom_picking'),('boating_sailing'),('boutique'),('breweries_distilleries'),('business'),
            ('cafe'),('camp_school'),('camping'),('childrens_attraction'),('christmas_santa'),('climbing'),('coast_archipelago'),('cottages_villas'),('creative_arts'),('cross_country_skiing'),('cruises_ferries'),('culinary_experience'),('cultural_heritage'),('cycling_mountain_biking'),
            ('day_trip'),
            ('education'),('events_festivals'),
            ('family_activity'),('fast_food'),('fine_dining'),('finnish_design_fashion'),('fishing_hunting'),('food_experience'),('forests'),
            ('golf'),('guidance'),('guided_service'),
            ('handicraft'),('hiking_walking_trekking'),('historical_sites'),('history'),('horses'),('hotels_hostels'),('huskies'),
            ('ice_climbing'),('ice_fishing'),('ice_skating'),('ice_swimming'),
            ('lakes'),('LGBTQ'),('local_food'),('local_lifestyle'),('local_products'),('luxury'),('luxury_accommodation'),
            ('market'),('medical_service'),('midnight_sun'),('motorsports'),('museums_galleries'),('music'),('mystay'),
            ('national_park'),('natural_site'),('nature_excursion'),('northern_lights'),
            ('orienteering'),('other_activity'),('other_attraction'),('other_shop'),('other_winter_activity'),('outlet'),
            ('paddling_rafting'),('parks_gardens'),('pet_friendly'),('photography'),('private_experience'),
            ('rehabilitation'),('reindeer'),('restaurant'),('running'),('running_trailrunning'),
            ('safari'),('sauna_experience'),('scenic_point'),('school_visit'),('shopping_center'),('sightseeing_tours'),('silence_program'),('ski_resort'),('ski_school'),('skiing_snowboarding'),('snowmobiling'),('snowshoeing'),('souvenirs'),('spa_recreational_spa'),('specialty_accommodation'),('sports'),('supermarket'),('swimming'),
            ('tourist_information'),('transportation'),
            ('vegetarian_vegan'),('virtual_reality'),
            ('water_activities'),('wellbeing_from_nature'),('wellness_treatments'),('wildlife_bird_watching'),('winter_biking'),
            ('yoga_meditation')
            ");

        $table = $wpdb->prefix . 'visithame_municipalities';
        $wpdb->query("INSERT INTO $table
        (city, city_code)
        VALUES
        ('Forssa', '061'),
        ('Hattula', '082'),
        ('Hausjärvi', '086'),
        ('Humppila', '103'),
        ('Hämeenlinna', '109'),
        ('Janakkala', '165'),
        ('Jokioinen', '169'),
        ('Loppi', '433'),
        ('Riihimäki', '694'),
        ('Tammela', '834'),
        ('Ypäjä', '981')
        ");

        $date = date('Y-m-d h:i:s', time());
        $this->insert_or_update_time_updated('editor', $date);
    }
}
