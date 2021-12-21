<?php

/**
 * Plugin Name:       DataHub
 * Description:       Query products from DataHub
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.4.0
 * Author:            VisitHäme
 * License:           MIT License
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       visithame-datahub
 * Domain Path:       /languages
 *
 * @package           datahub
 */

include plugin_dir_path(__FILE__) . 'options.php';
include plugin_dir_path(__FILE__) . 'render.php';
include plugin_dir_path(__FILE__) . 'classes/class-graphql.php';
include plugin_dir_path(__FILE__) . 'classes/class-database.php';

$graphQL = new GraphQL();

class VisitHameDataHub
{
    function __construct()
    {

        $this->templates = array();


        // Add a filter to the attributes metabox to inject template into the cache.
        if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {

            // 4.6 and older
            add_filter(
                'page_attributes_dropdown_pages_args',
                array($this, 'register_project_templates')
            );
        } else {

            // Add a filter to the wp 4.7 version attributes metabox
            add_filter(
                'theme_page_templates',
                array($this, 'add_new_template')
            );
        }

        // Add a filter to the save post to inject out template into the page cache
        add_filter(
            'wp_insert_post_data',
            array($this, 'register_project_templates')
        );


        // Add a filter to the template include to determine if the page has our 
        // template assigned and return it's path
        add_filter(
            'template_include',
            array($this, 'view_project_template')
        );


        // Add your templates to this array.
        $this->templates = array(
            'product-template.php' => 'Tuotekorttipohja',
        );


        register_activation_hook(__FILE__, array($this, 'initialize_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));
        add_action('wp_enqueue_scripts', array($this, 'content_enqueue'));
        add_action('init', array($this, 'init_block'));
        add_action('enqueue_block_editor_assets', array($this, 'editor_content'));
        add_action('init', array($this, 'datahub_rewrite_tag'), 10, 0);
        add_action('init', array($this, 'datahub_product_rewrite_rule'), 10, 0);
    }

    function set_page_title($title_parts)
    {
        global $post;
        $title_parts['title'] = "Page Title";
        return $title_parts;
    }

    function editor_content()
    {
        global $wpdb;
        global $graphQL;
        $database = new DataHubDatabase();

        $date = date('Y-m-d h:i:s', time());
        $table_name = $wpdb->prefix . 'visithame_datahub_updates';
        $result = $wpdb->get_results("
            SELECT updated_at
            FROM $table_name
            WHERE target_field = 'editor'
            ");
        $tags = null;
        $municipalities = null;

        // 60 * 60 * 24 * 7 86400 / 604800
        if ($result > 0 && (strtotime($date) - strtotime($result[0]->updated_at) > 604800)) {
            $datahub_options = $database->get_datahub_options();
            delete_option('access_token');
            $access_token = $graphQL->get_access_token($datahub_options['datahub_client_secret'], $datahub_options['datahub_username'], $datahub_options['datahub_password']);

            if ($access_token == false) {
                return "Can't get access token";
            }

            $tags = $graphQL->get_tags($access_token);

            if (property_exists($tags, 'data')) {
                foreach ($tags->data->tags as $key) {
                    $key->checked = false;
                }

                if (is_object($tags)) {
                    $database->insert_or_update_editor_tags($tags->data->tags);
                }
            }
        }

        $tags = $database->get_tags();
        $municipalities = $database->get_municipalities();

        wp_enqueue_script('inline-script', plugins_url('inline-script.js', __FILE__));
        wp_add_inline_script('inline-script', 'const searchParams = ' . json_encode(
            array(
                'tags' => $tags,
                'municipalities' => $municipalities
            )
        ), 'before');
    }
    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
     */
    function init_block()
    {
        register_block_type(__DIR__, array(
            'render_callback' => array($this, 'datahub_render_callback'),
            'attributes' => [
                'categories' => [
                    'type' => 'string',
                    'default' => 'No selection'
                ],
                'municipalities' => [
                    'type' => 'string',
                    'default' => 'No selection'
                ],
                'language' => [
                    'type' => 'string',
                    'default' => 'fi'
                ],
                'target_groups' => [
                    'type' => 'string',
                    'default' => 'No selection'
                ]
            ]
        ));
    }

    function initialize_plugin()
    {
        $database = new DataHubDatabase();
        $database->create_database_tables();
        $database->add_initial_values();

        $this->create_page();
    }
    function create_page()
    {
        $check_page_exist = get_page_by_title('title_of_the_page', 'OBJECT', 'page');
        // Check if the page already exists
        $page_id = null;
        $page_id_fi = null;
        $page_id_de = null;


        // if (empty($check_page_exist)) {
        $page_id = wp_insert_post(
            array(
                'comment_status' => 'close',
                'ping_status'    => 'close',
                'post_author'    => 1,
                'post_title'     => ucwords('Product - DataHub'),
                'post_name'      => sanitize_title('product_datahub'),
                'post_status'    => 'publish',
                'post_content'   => 'DataHub product content',
                'post_type'      => 'page',
                'page_template'  => 'product-template.php'
            )
        );

        $database = new DataHubDatabase();
        $database->insert_page($page_id);
    }

    function deactivate()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'visithame_datahub_translations';
        $sql = "SELECT page_id FROM $table_name ";
        $pages = $wpdb->get_results($sql);

        wp_delete_post($pages[0]->page_id, true);

        $table_prefix = $wpdb->prefix;
        $tables = array(
            $table_prefix . 'visithame_datahub',
            $table_prefix . 'visithame_datahub_tags',
            $table_prefix . 'visithame_datahub_municipalities',
            $table_prefix . 'visithame_datahub_updates',
            $table_prefix . 'visithame_datahub_social_media_link',
            $table_prefix . 'visithame_datahub_opening_hours',
            $table_prefix . 'visithame_datahub_postal_area',
            $table_prefix . 'visithame_datahub_postal_address',
            $table_prefix . 'visithame_datahub_product_availability',
            $table_prefix . 'visithame_datahub_product_available_months',
            $table_prefix . 'visithame_datahub_product_image',
            $table_prefix . 'visithame_datahub_product_information',
            $table_prefix . 'visithame_datahub_product_pricing',
            $table_prefix . 'visithame_datahub_product_tag',
            $table_prefix . 'visithame_datahub_product_video',
            $table_prefix . 'visithame_datahub_product_pricing',
            $table_prefix . 'visithame_datahub_contact_details',
            $table_prefix . 'visithame_datahub_company',
            $table_prefix . 'visithame_datahub_product',
            $table_prefix . 'visithame_datahub_target_group',
            $table_prefix . 'visithame_datahub_translations'
        );

        foreach ($tables as $table) {
            $sql = "DROP TABLE IF EXISTS $table";
            $wpdb->query($sql);
        }

        delete_option('datahub_option_name');
    }

    function datahub_rewrite_tag()
    {
        add_rewrite_tag('%product%', '([^&]+)');
        add_rewrite_tag('%language%', '([^&]{2})');
    }

    function datahub_product_rewrite_rule()
    {
        $database = new DataHubDatabase();
        $pages = $database->get_pages();

        add_rewrite_rule('^datahub/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Default
        add_rewrite_rule('^tuotteet/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Fi
        add_rewrite_rule('^produkte/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // De
        add_rewrite_rule('^products/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // En
        add_rewrite_rule('^製品/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Ja
        add_rewrite_rule('^продукты/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Ru
        add_rewrite_rule('^产品/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Zh
        add_rewrite_rule('^produkter/([^/]*)/([^/]{2})/?', sprintf('index.php?page_id=%s&product=$matches[1]&language=$matches[2]', $pages[0]->page_id), 'top'); // Sv

        flush_rewrite_rules();
    }

    function register_styles()
    {
        // wp_register_style('prefix-style', plugins_url('src/card.css', __FILE__));
        wp_enqueue_style('prefix-style');
        wp_enqueue_style('custom-google-fonts', 'https://fonts.googleapis.com/icon?family=Material+Icons');
    }

    function content_enqueue()
    {
        wp_enqueue_script(
            'content-script',
            plugins_url('tabs.js', __FILE__)
        );
    }

    function datahub_query($attributes)
    {
        global $wpdb;
        global $graphQL;
        $database = new DataHubDatabase();

        $date = date('Y-m-d h:i:s', time());
        $table_name = $wpdb->prefix . 'visithame_datahub_updates';
        $result = $wpdb->get_results("
    SELECT *
    FROM $table_name
    WHERE target_field = 'products'
    ");
        $products = null;
        // 60 * 60 * 24 * 7 604800
        if (empty($result) || !property_exists($result[0], 'target_field') || (strtotime($date) - strtotime($result[0]->updated_at) > 86400)) {
            $datahub_options = $database->get_datahub_options();

            delete_option('access_token');

            $access_token = $graphQL->get_access_token($datahub_options['datahub_client_secret'], $datahub_options['datahub_username'], $datahub_options['datahub_password']);
            
            if ($access_token == false) {
                return "Can't get access token";
            }

            $products = $graphQL->get_products($attributes['categories'], $attributes['municipalities'], $access_token);

            if (is_object($products)) {
                $database->insert_or_update_products($products->data->product);
            }
        }

        if ($attributes['municipalities'] === 'No selection' || $attributes['categories'] === 'No selection') {
            return '<p>No filters have been added. Please add at least one municipality and one category to show products.</p>';
        }

        $products = $database->get_products_list($attributes['categories'], $attributes['municipalities'], $attributes['language']);
        $content = '';

        // Hämeenlinna  109
        // Hattula      82
        // Janakkala    165

        // Riihimäki    694
        // Hausjärvi    86
        // Loppi        433

        // Forssa       61
        // Humppila     103
        // Jokioinen    169
        // Tammela      834
        // Ypäjä        981

        $areas = array(
            'ALL' => array(
                'id' => 'ALL',
                'cityCodes' => array('109', '82', '165', '694', '86', '433', '61', '103', '169', '834', '981'),
                'label' => array('en' => 'All', 'fi' => 'Kaikki', 'de' => 'Alle', 'sv' => 'Allt', 'ru' => 'Все', 'ja' => '全て', 'zh' => '全部',)
            ),
            'HML' => array(
                'id' => 'HML',
                'cityCodes' => array('109', '82', '165'),
                'label' => array('en' => 'Hämeenlinna region', 'fi' => 'Hämeenlinnan seutu', 'de' => 'Hämeenlinna region', 'sv' => 'Hämeenlinna område', 'ru' => 'Hämeenlinna область', 'ja' => 'Hämeenlinna 領域', 'zh' => 'Hämeenlinna 地区')
            ),
            'RMK' => array(
                'id' => 'RMK',
                'cityCodes' => array('694', '86', '433'),
                'label' => array('en' => 'Riihimäki region', 'fi' => 'Riihimäen seutu', 'de' => 'Riihimäki region', 'sv' => 'Riihimäki område', 'ru' => 'Riihimäki область', 'ja' => 'Riihimäki 領域', 'zh' => 'Riihimäki 地区')
            ),
            'FRS' => array(
                'id' => 'FRS',
                'cityCodes' => array('61', '103', '169', '834', '981'),
                'label' => array('en' => 'Forssa region', 'fi' => 'Forssan seutu', 'de' => 'Forssa region', 'sv' => 'Forssa område', 'ru' => 'Forssa область', 'ja' => 'Forssa 領域', 'zh' => 'Forssa 地区')
            ),
        );

        $areas_found = array();

        foreach ($products as $product) {
            if (!in_array($product->city_code, $areas_found)) {
                array_push($areas_found, $product->city_code);
            }
        }

        $buttons = '';

        foreach ($areas as $area) {
            foreach ($areas_found as $area_found) {
                if (in_array($area_found, $area['cityCodes'])) {
                    if (!strpos($buttons, $area['id']) !== false) {
                        $buttons .= sprintf('<button class="tablinks" onclick="openArea(event, \'%s\')">%s</button>', $area['id'], $area['label'][$attributes['language']]);
                    }
                }
            }
        }

        $pos = strpos($buttons, 'tablinks');
        if ($pos !== false) {
            $buttons = substr_replace($buttons, 'tablinks active', $pos, strlen('tablinks'));
        }


        $content .= '<div class="datahub-region-tab">' . $buttons . '</div>';
        $content .= datahub_render_cards($products, $areas, $attributes['language'], $attributes['target_groups']);

        return $content;
    }

    function datahub_render_callback($block_attributes, $content)
    {
        return $this->datahub_query($block_attributes);
    }

    /**
     * Adds our template to the page dropdown for v4.7+
     *
     */
    public function add_new_template($posts_templates)
    {
        $posts_templates = array_merge($posts_templates, $this->templates);
        return $posts_templates;
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates($atts)
    {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        // Retrieve the cache list. 
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete($cache_key, 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge($templates, $this->templates);

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;
    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template($template)
    {

        // Get global post
        global $post;

        // Return template if post is empty
        if (!$post) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if (!isset($this->templates[get_post_meta(
            $post->ID,
            '_wp_page_template',
            true
        )])) {
            return $template;
        }

        $file = plugin_dir_path(__FILE__) . 'templates/' . get_post_meta(
            $post->ID,
            '_wp_page_template',
            true
        );

        // Just to be safe, we check if the file exist first
        if (file_exists($file)) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;
    }
}

$visitHameDataHub = new VisitHameDataHub();
