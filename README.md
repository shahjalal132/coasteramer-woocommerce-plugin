## Coaster amer woocommerce plugin. 

This is a custom plugin as client requirement. when plugin activate then 4 table will create named wp_sync_price, wp_sync_category, wp_sync_inventory, wp_sync_products.

## Call api and stored data to database

I created 6 shortcode for  fetch data from api and insert them to database. to use and test these create one or multiple page on wordpress dashboard then paste and run one by one. if successfully insert it will return a successful message

## Test shortcode

1. call category api and insert categories to database
    ```php
        [insert_categories]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

2. call products api and insert products to database
    ```php
        [insert_products]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

3. call inventory api and insert inventory to database
    ```php
        [coaster_inventory_api]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

4. call price api and insert products to database
    ```php
        [insert_price_to_db_shortcode]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

5. this shortcode for custom query. it will fetch all category from database. and insert them into woocommerce products category. letter when add products then it will add category to products
    ```php
        [fetch_all_categories]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

6. finally add a product to woocommerce including all information
    ```php
        [coaster_product_insert_to_woocommerce]
    ```
    create a page and paste this shortcode and run. if successfully insert it will return a successfull message.

**Make sure already you install woocommerce plugin**
