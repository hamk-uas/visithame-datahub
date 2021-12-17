<?php

function datahub_render_card($product, $language)
{
    $content = '';

    $url = $product->thumbnail_url;
    // $url = $cover_photo;
    // $url = $product->large_url;
    $url = str_replace('(', '%28', $url);
    $url = str_replace(')', '%29', $url);

    $content .= '<div class="datahub-container-card" style="background-image: url(' . $url . ');" ' . 'data-copyright="&copy; ' . $product->copyright . '" alt="' . $product->alt_text . '">';
    $content .= sprintf("<a href='" . site_url('', 'relative') . "/%s/%s/' target='_blank'>", get_url($product, $language), $language);
    $content .= '<div class="datahub-container-card-overlay">';
    $content .= '<div class="datahub-container-card-title">' . mb_strtoupper($product->name) . '</div>';
    $content .= '<div class="datahub-container-card-description">' . mb_substr($product->description, 0, 165) . '...</div>';
    // $content .= '<div class="datahub-container-card-description">' . strtok($product->description, '.') . '...</div>';
    // $content .= '<div><small>' . $product->copyright . '</small></div>';
    $content .= '</div>'; // .datahub-container-card-overlay
    $content .= '</a>'; // a
    $content .= '</div>'; // .datahub-container-card

    return $content;
}

function datahub_render_cards($products, $areas, $language, $target_groups)
{
    $content = '';

    foreach ($areas as $area) {
        $content .= sprintf('<div id="%s" class="datahub-region-tabcontent">', $area['id']);
        $content .= '<div class="datahub-container-cards">';
        foreach ($products as $product) {
            if ($target_groups != 'b2b') {

                if (in_array($product->city_code, $area['cityCodes'])) {
                    if ($language == "fi") {
                        if (!strpos($product->description, 'englanniksi') !== false && $product->TargetData != 'b2b' && !strpos($product->description, 'suomeksi') !== false) {
                            $content .= datahub_render_card($product, $language);
                        }
                    } else if ($language == "en") {
                        if (!strpos($product->description, 'suomeksi') !== false && $product->TargetData != 'b2b') {
                            $content .= datahub_render_card($product, $language);
                        }
                    } else {
                        $content .= datahub_render_card($product, $language);
                    }
                }
            } else if ($target_groups == 'b2b') {
                if (in_array($product->city_code, $area['cityCodes'])) {
                    if ($language == "fi") {
                        if (!strpos($product->description, 'englanniksi') !== false && strpos($product->TargetData, 'b2b') !== false && !strpos($product->description, 'suomeksi') !== false) {
                            $content .= datahub_render_card($product, $language);
                        }
                    } else if ($language == "en") {
                        if (!strpos($product->description, 'suomeksi') !== false && strpos($product->TargetData, 'b2b') !== false) {
                            $content .= datahub_render_card($product, $language);
                        }
                    } else {
                        $content .= datahub_render_card($product, $language);
                    }
                }
            }
        }
        $content .= '</div>';
        $content .= '</div>';
    }

    $pos = strpos($content, 'datahub-region-tabcontent');
    if ($pos !== false) {
        $content = substr_replace($content, 'datahub-region-tabcontent" style="display: block;', $pos, strlen('datahub-region-tabcontent'));
    }
    return $content;
}

function get_url($product, $language)
{
    $url = null;
    switch ($language) {
        case "fi":
            $url = 'tuotteet/' . $product->product_url_fi;
            break;
        case "en":
            $url = 'products/' . $product->product_url_en;
            break;
        case "de":
            $url = 'produkte/' . $product->product_url_de;
            break;
        case "sv":
            $url = 'produkter/' . $product->product_url_sv;
            break;
        case "ru":
            $url = 'продукты/' . $product->product_url_ru;
            break;
        case "zh":
            $url = '产品/' . $product->product_url_zh;
            break;
        case "ja":
            $url = '製品/' . $product->product_url_ja;
            break;
        default:
            $url = 'datahub/' . $product->product_url_en;
    }
    return $url;
}
