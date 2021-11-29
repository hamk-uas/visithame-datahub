=== DataHub ===
Contributors:      VisitHäme
Tags:              block
Tested up to:      5.8.0
Stable tag:        0.3.0
License:           MIT License
License URI:       https://creativecommons.org/licenses/by/4.0/

Query products from DataHub

== Description ==

Plugin shows products from the DataHub API. Products area is limited to Tavastia region (Kanta-Häme: Hämeenlinna region, Forssa region and Riihimäki region). 
Showing the products on the page can be limited to current municipalities, such as Hämeenlinna, Jokioinen etc. 

Plugin works by fetching the tags and products from DataHub. Data is fetched when user loads a page containing the widget and the last update for the tags was over seven days ago. 

With the initial installation, some usable tags are inserted to the database. 

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/visithame-datahub` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add DataHub Gutenberg block to a page with selected filters. 

== Usage ==

Filters:

- Municipalities:
    - Selection can contain multiple municipalities

- Tags:
    - Tags can contain multiple tags

- Language:
    - Selection of language is limited to one per widget

- Target groups:
    - Currently selection works so that if only B2B is selected, only products with B2B are listed. If any other is selected, no filters are applied. 

