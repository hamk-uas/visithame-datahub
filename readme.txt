=== DataHub ===
Contributors:      VisitHäme
Tags:              block
Tested up to:      5.8.0
Stable tag:        1.0.0
License:           MIT License
License URI:       https://opensource.org/licenses/MIT

Query products from DataHub

== Description ==

This plugin is a developed for the Gutenberg editor.

Plugin shows products from the DataHub API. Products area is limited to Tavastia region (Kanta-Häme: Hämeenlinna region, Forssa region and Riihimäki region). 
Showing the products on the page can be limited to current municipalities, such as Hämeenlinna, Jokioinen etc. 

Plugin works by fetching the tags and products from DataHub. New tags and products are fetched when a page containing the plugin is loaded and the last update was more than a week ago. 

With the initial installation, some usable tags are inserted to the database. 

== Installation ==

1. Build and upload the plugin files to the `/wp-content/plugins/visithame-datahub` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add DataHub Gutenberg block to a page with selected filters. 

== Usage ==

Under settings, find DataHub entry and add your registered DataHub account and client secret that you received from registration.

Using the Block editor/Gutenberg editor (Comes with Wordpress version >5.0) add a new block and find element called DataHub.
After adding the element to a page, you need to add at least one municipality and one category. Language by default is Finnish.

Filters:

- Municipalities:
    - Selection can contain multiple municipalities

- Tags:
    - Tags can contain multiple tags

- Language:
    - Selection of language is limited to one per widget

- Target groups:
    - Selecting 'List products with B2B' will include products that have set the B2B target group for the product. Otherwise, these products are excluded from the list. 

