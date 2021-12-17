<?php
include plugin_dir_path(__FILE__) . '../classes/class-product.php';
$database = new DataHubDatabase();
global $wp_query;
$product_details = $database->datahub_get_product_details_from_database($wp_query->query_vars['product'], $wp_query->query_vars['language']);
$product = new Product($product_details[0]);

$product_name = $product->product_name;
/**
 * Template Name: Product card
 * 
 * The template for displaying all pages
 *
 */

/**
 * Setting meta description and title
 */
add_filter('wpseo_metadesc', function ($description) {

  global $product;

  $description = $product->product_description;

  return $description;
});

add_filter('wpseo_title', function ($title) {

  global $product_name;

  $title = $product_name . ' - ' . get_bloginfo('name');

  return $title;
});
function datahub_meta()
{
  global $product;
  echo '<meta name="description" content="' . mb_substr($product->product_description, 0, 180) . '">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  ';
}
add_action('wp_head', 'datahub_meta');

/**
 * Check if WPML plugin is installed on the site
 */
global $sitepress;
if (isset($sitepress)) {
  $sitepress->switch_lang($wp_query->query_vars['language'], true);
}

get_header(); ?>

<svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <symbol id="icon-cart" viewBox="0 0 32 32">
      <path d="M12 29c0 1.657-1.343 3-3 3s-3-1.343-3-3c0-1.657 1.343-3 3-3s3 1.343 3 3z"></path>
      <path d="M32 29c0 1.657-1.343 3-3 3s-3-1.343-3-3c0-1.657 1.343-3 3-3s3 1.343 3 3z"></path>
      <path d="M32 16v-12h-24c0-1.105-0.895-2-2-2h-6v2h4l1.502 12.877c-0.915 0.733-1.502 1.859-1.502 3.123 0 2.209 1.791 4 4 4h24v-2h-24c-1.105 0-2-0.895-2-2 0-0.007 0-0.014 0-0.020l26-3.98z"></path>
    </symbol>
    <symbol id="icon-link" viewBox="0 0 32 32">
      <path d="M13.757 19.868c-0.416 0-0.832-0.159-1.149-0.476-2.973-2.973-2.973-7.81 0-10.783l6-6c1.44-1.44 3.355-2.233 5.392-2.233s3.951 0.793 5.392 2.233c2.973 2.973 2.973 7.81 0 10.783l-2.743 2.743c-0.635 0.635-1.663 0.635-2.298 0s-0.635-1.663 0-2.298l2.743-2.743c1.706-1.706 1.706-4.481 0-6.187-0.826-0.826-1.925-1.281-3.094-1.281s-2.267 0.455-3.094 1.281l-6 6c-1.706 1.706-1.706 4.481 0 6.187 0.635 0.635 0.635 1.663 0 2.298-0.317 0.317-0.733 0.476-1.149 0.476z"></path>
      <path d="M8 31.625c-2.037 0-3.952-0.793-5.392-2.233-2.973-2.973-2.973-7.81 0-10.783l2.743-2.743c0.635-0.635 1.664-0.635 2.298 0s0.635 1.663 0 2.298l-2.743 2.743c-1.706 1.706-1.706 4.481 0 6.187 0.826 0.826 1.925 1.281 3.094 1.281s2.267-0.455 3.094-1.281l6-6c1.706-1.706 1.706-4.481 0-6.187-0.635-0.635-0.635-1.663 0-2.298s1.663-0.635 2.298 0c2.973 2.973 2.973 7.81 0 10.783l-6 6c-1.44 1.44-3.355 2.233-5.392 2.233z"></path>
    </symbol>
    <symbol id="icon-facebook2" viewBox="0 0 32 32">
      <path d="M29 0h-26c-1.65 0-3 1.35-3 3v26c0 1.65 1.35 3 3 3h13v-14h-4v-4h4v-2c0-3.306 2.694-6 6-6h4v4h-4c-1.1 0-2 0.9-2 2v2h6l-1 4h-5v14h9c1.65 0 3-1.35 3-3v-26c0-1.65-1.35-3-3-3z"></path>
    </symbol>
    <symbol id="icon-instagram" viewBox="0 0 32 32">
      <path d="M16 2.881c4.275 0 4.781 0.019 6.462 0.094 1.563 0.069 2.406 0.331 2.969 0.55 0.744 0.288 1.281 0.638 1.837 1.194 0.563 0.563 0.906 1.094 1.2 1.838 0.219 0.563 0.481 1.412 0.55 2.969 0.075 1.688 0.094 2.194 0.094 6.463s-0.019 4.781-0.094 6.463c-0.069 1.563-0.331 2.406-0.55 2.969-0.288 0.744-0.637 1.281-1.194 1.837-0.563 0.563-1.094 0.906-1.837 1.2-0.563 0.219-1.413 0.481-2.969 0.55-1.688 0.075-2.194 0.094-6.463 0.094s-4.781-0.019-6.463-0.094c-1.563-0.069-2.406-0.331-2.969-0.55-0.744-0.288-1.281-0.637-1.838-1.194-0.563-0.563-0.906-1.094-1.2-1.837-0.219-0.563-0.481-1.413-0.55-2.969-0.075-1.688-0.094-2.194-0.094-6.463s0.019-4.781 0.094-6.463c0.069-1.563 0.331-2.406 0.55-2.969 0.288-0.744 0.638-1.281 1.194-1.838 0.563-0.563 1.094-0.906 1.838-1.2 0.563-0.219 1.412-0.481 2.969-0.55 1.681-0.075 2.188-0.094 6.463-0.094zM16 0c-4.344 0-4.887 0.019-6.594 0.094-1.7 0.075-2.869 0.35-3.881 0.744-1.056 0.412-1.95 0.956-2.837 1.85-0.894 0.888-1.438 1.781-1.85 2.831-0.394 1.019-0.669 2.181-0.744 3.881-0.075 1.713-0.094 2.256-0.094 6.6s0.019 4.887 0.094 6.594c0.075 1.7 0.35 2.869 0.744 3.881 0.413 1.056 0.956 1.95 1.85 2.837 0.887 0.887 1.781 1.438 2.831 1.844 1.019 0.394 2.181 0.669 3.881 0.744 1.706 0.075 2.25 0.094 6.594 0.094s4.888-0.019 6.594-0.094c1.7-0.075 2.869-0.35 3.881-0.744 1.050-0.406 1.944-0.956 2.831-1.844s1.438-1.781 1.844-2.831c0.394-1.019 0.669-2.181 0.744-3.881 0.075-1.706 0.094-2.25 0.094-6.594s-0.019-4.887-0.094-6.594c-0.075-1.7-0.35-2.869-0.744-3.881-0.394-1.063-0.938-1.956-1.831-2.844-0.887-0.887-1.781-1.438-2.831-1.844-1.019-0.394-2.181-0.669-3.881-0.744-1.712-0.081-2.256-0.1-6.6-0.1v0z"></path>
      <path d="M16 7.781c-4.537 0-8.219 3.681-8.219 8.219s3.681 8.219 8.219 8.219 8.219-3.681 8.219-8.219c0-4.537-3.681-8.219-8.219-8.219zM16 21.331c-2.944 0-5.331-2.387-5.331-5.331s2.387-5.331 5.331-5.331c2.944 0 5.331 2.387 5.331 5.331s-2.387 5.331-5.331 5.331z"></path>
      <path d="M26.462 7.456c0 1.060-0.859 1.919-1.919 1.919s-1.919-0.859-1.919-1.919c0-1.060 0.859-1.919 1.919-1.919s1.919 0.859 1.919 1.919z"></path>
    </symbol>
    <symbol id="icon-twitter" viewBox="0 0 32 32">
      <path d="M32 7.075c-1.175 0.525-2.444 0.875-3.769 1.031 1.356-0.813 2.394-2.1 2.887-3.631-1.269 0.75-2.675 1.3-4.169 1.594-1.2-1.275-2.906-2.069-4.794-2.069-3.625 0-6.563 2.938-6.563 6.563 0 0.512 0.056 1.012 0.169 1.494-5.456-0.275-10.294-2.888-13.531-6.862-0.563 0.969-0.887 2.1-0.887 3.3 0 2.275 1.156 4.287 2.919 5.463-1.075-0.031-2.087-0.331-2.975-0.819 0 0.025 0 0.056 0 0.081 0 3.181 2.263 5.838 5.269 6.437-0.55 0.15-1.131 0.231-1.731 0.231-0.425 0-0.831-0.044-1.237-0.119 0.838 2.606 3.263 4.506 6.131 4.563-2.25 1.762-5.075 2.813-8.156 2.813-0.531 0-1.050-0.031-1.569-0.094 2.913 1.869 6.362 2.95 10.069 2.95 12.075 0 18.681-10.006 18.681-18.681 0-0.287-0.006-0.569-0.019-0.85 1.281-0.919 2.394-2.075 3.275-3.394z"></path>
    </symbol>
    <symbol id="icon-sina-weibo" viewBox="0 0 32 32">
      <path d="M13.444 28.063c-5.3 0.525-9.875-1.875-10.219-5.35-0.344-3.481 3.675-6.719 8.969-7.244 5.3-0.525 9.875 1.875 10.213 5.35 0.35 3.481-3.669 6.725-8.963 7.244zM24.038 16.519c-0.45-0.137-0.762-0.225-0.525-0.819 0.512-1.287 0.563-2.394 0.006-3.188-1.038-1.481-3.881-1.406-7.137-0.037 0 0-1.025 0.444-0.762-0.363 0.5-1.613 0.425-2.956-0.356-3.738-1.769-1.769-6.469 0.069-10.5 4.1-3.013 3.006-4.763 6.213-4.763 8.981 0 5.288 6.787 8.506 13.425 8.506 8.7 0 14.494-5.056 14.494-9.069 0-2.431-2.044-3.806-3.881-4.375z"></path>
      <path d="M29.819 6.831c-2.1-2.331-5.2-3.219-8.063-2.612v0c-0.663 0.144-1.081 0.794-0.938 1.45 0.144 0.662 0.788 1.081 1.45 0.938 2.038-0.431 4.238 0.2 5.731 1.856s1.9 3.912 1.256 5.888v0c-0.206 0.644 0.144 1.331 0.788 1.544 0.644 0.206 1.331-0.144 1.544-0.787v-0.006c0.9-2.762 0.331-5.938-1.769-8.269z"></path>
      <path d="M26.587 9.75c-1.025-1.137-2.538-1.569-3.925-1.269-0.569 0.119-0.931 0.688-0.813 1.256 0.125 0.569 0.688 0.931 1.25 0.806v0c0.681-0.144 1.419 0.069 1.919 0.619 0.5 0.556 0.637 1.313 0.419 1.975v0c-0.175 0.55 0.125 1.15 0.681 1.331 0.556 0.175 1.15-0.125 1.331-0.681 0.438-1.356 0.163-2.906-0.863-4.037z"></path>
      <path d="M13.738 21.769c-0.188 0.319-0.594 0.469-0.912 0.337-0.319-0.125-0.412-0.488-0.231-0.794 0.188-0.306 0.581-0.456 0.894-0.337 0.313 0.113 0.425 0.469 0.25 0.794zM12.044 23.931c-0.512 0.819-1.613 1.175-2.438 0.8-0.813-0.369-1.056-1.319-0.544-2.119 0.506-0.794 1.569-1.15 2.387-0.806 0.831 0.356 1.1 1.3 0.594 2.125zM13.969 18.144c-2.519-0.656-5.369 0.6-6.463 2.819-1.119 2.262-0.037 4.781 2.506 5.606 2.637 0.85 5.75-0.456 6.831-2.894 1.069-2.394-0.262-4.85-2.875-5.531z"></path>
    </symbol>
    <symbol id="icon-youtube" viewBox="0 0 32 32">
      <path d="M31.681 9.6c0 0-0.313-2.206-1.275-3.175-1.219-1.275-2.581-1.281-3.206-1.356-4.475-0.325-11.194-0.325-11.194-0.325h-0.012c0 0-6.719 0-11.194 0.325-0.625 0.075-1.987 0.081-3.206 1.356-0.963 0.969-1.269 3.175-1.269 3.175s-0.319 2.588-0.319 5.181v2.425c0 2.587 0.319 5.181 0.319 5.181s0.313 2.206 1.269 3.175c1.219 1.275 2.819 1.231 3.531 1.369 2.563 0.244 10.881 0.319 10.881 0.319s6.725-0.012 11.2-0.331c0.625-0.075 1.988-0.081 3.206-1.356 0.962-0.969 1.275-3.175 1.275-3.175s0.319-2.587 0.319-5.181v-2.425c-0.006-2.588-0.325-5.181-0.325-5.181zM12.694 20.15v-8.994l8.644 4.513-8.644 4.481z"></path>
    </symbol>
    <symbol id="icon-vkontakte" viewBox="0 0 22 22">
      <path d="M13.162 18.994c.609 0 .858-.406.851-.915-.031-1.917.714-2.949 2.059-1.604 1.488 1.488 1.796 2.519 3.603 2.519h3.2c.808 0 1.126-.26 1.126-.668 0-.863-1.421-2.386-2.625-3.504-1.686-1.565-1.765-1.602-.313-3.486 1.801-2.339 4.157-5.336 2.073-5.336h-3.981c-.772 0-.828.435-1.103 1.083-.995 2.347-2.886 5.387-3.604 4.922-.751-.485-.407-2.406-.35-5.261.015-.754.011-1.271-1.141-1.539-.629-.145-1.241-.205-1.809-.205-2.273 0-3.841.953-2.95 1.119 1.571.293 1.42 3.692 1.054 5.16-.638 2.556-3.036-2.024-4.035-4.305-.241-.548-.315-.974-1.175-.974h-3.255c-.492 0-.787.16-.787.516 0 .602 2.96 6.72 5.786 9.77 2.756 2.975 5.48 2.708 7.376 2.708z"></path>
    </symbol>
    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path class="st0" d="M13.162 18.994c.609 0 .858-.406.851-.915-.031-1.917.714-2.949 2.059-1.604 1.488 1.488 1.796 2.519 3.603 2.519h3.2c.808 0 1.126-.26 1.126-.668 0-.863-1.421-2.386-2.625-3.504-1.686-1.565-1.765-1.602-.313-3.486 1.801-2.339 4.157-5.336 2.073-5.336h-3.981c-.772 0-.828.435-1.103 1.083-.995 2.347-2.886 5.387-3.604 4.922-.751-.485-.407-2.406-.35-5.261.015-.754.011-1.271-1.141-1.539-.629-.145-1.241-.205-1.809-.205-2.273 0-3.841.953-2.95 1.119 1.571.293 1.42 3.692 1.054 5.16-.638 2.556-3.036-2.024-4.035-4.305-.241-.548-.315-.974-1.175-.974h-3.255c-.492 0-.787.16-.787.516 0 .602 2.96 6.72 5.786 9.77 2.756 2.975 5.48 2.708 7.376 2.708z"/></svg> -->
  </defs>
</svg>
<!-- <div >id="primary" class="site-content"> -->
<div id="datahub__content" role="main">
  <!-- <div id="datahub__title">
    <?php echo $product->render_title() ?>
  </div> -->
  <div id="datahub__main_content">
    <div class="datahub-row">
      <?php echo $product->render_title() ?>
    </div>
    <div class="datahub-row">

      <?php
      echo wpautop($product->render_main_content());
      ?>

      <?php
      echo $product->render_sidebar();
      ?>
    </div><!-- .datahub-row -->
    <div class="datahub-row-media">
      <?php
      echo $product->render_images();
      ?>
    </div><!-- .datahub-row -->
    <div class="datahub-row-media">
      <?php
      echo $product->render_videos();
      ?>
    </div><!-- .datahub-row -->
  </div><!-- #datahub__main_content -->
</div><!-- #datahub__content -->

<?php //get_sidebar(); 
?>
<?php get_footer(); ?>

<script>
  function datahubOpenModal() {
    document.getElementById("datahub-lightbox-modal").style.display = "flex";
  }

  // Close the Modal
  function datahubCloseModal() {
    document.getElementById("datahub-lightbox-modal").style.display = "none";
  }

  let slideIndex = 1;
  datahubShowSlides(slideIndex);

  // Next/previous controls
  function datahubPlusSlides(n) {
    datahubShowSlides(slideIndex += n);
  }

  // Thumbnail image controls
  function datahubCurrentSlide(n) {
    datahubShowSlides(slideIndex = n);
  }

  function datahubShowSlides(n) {
    let i;
    let slides = document.getElementsByClassName("datahub-lightbox-slides");

    if (n > slides.length) {
      slideIndex = 1
    }
    if (n < 1) {
      slideIndex = slides.length
    }

    let img = document.getElementById("full_image_" + slideIndex);
    let data = img.getAttribute('data-image-url');
    img.src = data;

    for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
    }
    slides[slideIndex - 1].style.display = "block";
  }

  document.querySelector('#datahub-images-carousel-prev').addEventListener('click', function() {
    document.getElementById("datahub-lightbox-row-content").scrollBy({
      left: -100,
      top: 0,
      behavior: 'smooth'
    })
  });

  document.querySelector('#datahub-images-carousel-next').addEventListener('click', function() {
    document.getElementById("datahub-lightbox-row-content").scrollBy({
      left: 100,
      top: 0,
      behavior: 'smooth'
    })
  });

  if (window.innerWidth < 700) {
    let cont = document.getElementById("datahub-main-content-column");
    let p_elements = cont.querySelectorAll("p")

    if (p_elements.length > 1) {
      document.getElementById("datahub-show-more-container").style.display = "block";
      for (i = 0; i < p_elements.length; i++) {
        if (i > 0) {
          p_elements[i].style.display = "none";
        }
      }
    }

    function open_description() {
      let cont = document.getElementById("datahub-main-content-column");
      let p_elements = cont.querySelectorAll("p")
      document.getElementById("datahub-show-more-container").style.display = "none";

      for (i = 0; i < p_elements.length; i++) {
        if (i > 0) {
          p_elements[i].style.display = "block";
          p_elements[i].style.animation = "animation 1s";
        }
      }
    }
  }
</script>