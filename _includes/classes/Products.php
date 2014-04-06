<?php

class Products
{

    // database resource
    private $mysqli;
    private $filters = array();
    private $sql = '';
    private $products = array();
    private $total_products = 0;
    private $total_featured = 0;
    private $total_new = 0;
    private $total_used = 0;
    private $total_on_sale = 0;
    private $total_top_sellers = 0;
    private $total_combo = 0;
    private $total_bundled = 0;
    private $brands = array();
    private $ratings = array();
    private $ids = array();
    private $max_price = 0;

    // constructor
    public function __construct()
    {
        global $mysqli;

        if (!$mysqli instanceof MySQLi)
            throw new Exception('Invalid mysqli object');
        $this->mysqli = $mysqli;
    }

    public function create_tmp()
    {
        global $config_site;

        $current_datetime = date('Y-m-d H:i:s');
        $id_customer_type = $_SESSION['customer']['id_customer_type'];

        $filters = $this->filters;

        // create temporary table
        // filter
        $where = array();

        if (sizeof($filters)) {
            if (isset($filters['rating']) && is_array($filters['rating']) && sizeof($filters['rating'])) {
                $in = array();

                foreach ($filters['rating'] as $key => $value) {
                    $value = (int) $value;

                    $in[] = $value;
                }

                $where[] = 'product_rating_count.avg_rating IN (' . implode(',', $in) . ')';
            }

            if (isset($filters['brand']) && $filters['brand'] != '')
                $where[] = 'product.brand = "' . $this->mysqli->escape_string($filters['brand']) . '"';
            if (isset($filters['featured_products']) && $filters['featured_products'] == 1)
                $where[] = 'product.featured = 1';
            if (isset($filters['new_products']) && $filters['new_products'] == 1)
                $where[] = '(product.used = 0 AND product.date_created >= DATE_SUB("' . $current_datetime . '", INTERVAL "' . $config_site['cf_new_products_no_days'] . '" DAY))';
            if (isset($filters['top_sellers']) && $filters['top_sellers'] == 1)
                $where[] = 'has_been_sold(product.id,0,IF(product_image_variant.variant_code IS NOT NULL,product_image_variant.variant_code,"")) > 0';
            if (isset($filters['combo_deals']) && $filters['combo_deals'] == 1)
                $where[] = 'product.product_type = 1';
            if (isset($filters['bundled_products']) && $filters['bundled_products'] == 1)
                $where[] = 'product.product_type = 2';

            if (isset($filters['s']) && !empty($filters['s'])) {
                $s = trim($filters['s']);

                $where[] = '
				(product_description.name LIKE "%' . $this->mysqli->escape_string($s) . '%" 
				OR product.sku LIKE "%' . $this->mysqli->escape_string($s) . '%"
				OR product.brand LIKE "%' . $this->mysqli->escape_string($s) . '%" 
				OR product.model LIKE "%' . $this->mysqli->escape_string($s) . '%" 
				OR product_image_variant_description.name LIKE "%' . $this->mysqli->escape_string($s) . '%")';
            }
        }

        $where = sizeof($where) ? implode(' AND ', $where) : '';

        // filter price
        $where_price = array();
        if (sizeof($filters)) {
            if (isset($filters['price'])) {
                $or = array();

                //foreach ($filters['price'] as $key => $value) {			
                list($start, $end) = explode('-', $filters['price']);

                $start = (float) $start;
                $end = (float) $end;

                $or[] = 't.sell_price BETWEEN "' . $start . '" AND "' . $end . '"';
                //}

                $where_price[] = '(' . implode(' OR ', $or) . ')';
            }

            //if (isset($filters['on_sale']) && $filters['on_sale'] == 1) $where_price[] = 'calc_sell_price(t.price,IF(product_variant.price_type IS NOT NULL,product_variant.price_type,0),IF(product_variant.price IS NOT NULL,product_variant.price,0),0,0,0,0) != calc_sell_price(t.sell_price,IF(product_variant.price_type IS NOT NULL,product_variant.price_type,0),IF(product_variant.price IS NOT NULL,product_variant.price,0),IF(customer_type.percent_discount IS NOT NULL,customer_type.percent_discount,0),IF(customer_type.apply_on_rebate IS NOT NULL,customer_type.apply_on_rebate,0),IF(rebate_coupon.discount_type IS NOT NULL,rebate_coupon.discount_type,0),IF(rebate_coupon.discount IS NOT NULL,rebate_coupon.discount,0))';
            if (isset($filters['on_sale']) && $filters['on_sale'] == 1)
                $where_price[] = '(t.sell_price < t.price)';
        }

        $where_price = sizeof($where_price) ? implode(' AND ', $where_price) : '';

        $this->mysqli->query('DROP TEMPORARY TABLE IF EXISTS tmp_product_tpl');

        $sql = 'CREATE TEMPORARY TABLE tmp_product_tpl ENGINE = MEMORY (SELECT t.id, 
		t.id_product_image_variant,	
		t.variant_code,
		t.id_product_variant,		
		t.product_type,
		t.sku,
		t.featured,
		t.used,
		t.min_qty,
		t.id_rebate_coupon,
		t.model,
		t.brand,
		t.on_sale_end_date,
		t.date_created,
		t.avg_rating,
		t.total_rating,
		t.has_variants,
		t.name,
		t.short_desc,
		t.alias,
		t.variant_name,
		t.new_product,
		t.display_price_exception,		
		t.price,		
		t.cost_price,
		t.sell_price,
		t.sort_order,
		t.special_price_to_date
		FROM 
		(SELECT 
			product.id, 
			product_image_variant.id AS id_product_image_variant,	
			product_image_variant.variant_code,
			IF(product_image_variant.id IS NOT NULL,IFNULL((SELECT product_variant.id FROM product_variant WHERE product_variant.id_product = product.id AND product_variant.active = 1 AND product_variant.variant_code LIKE CONCAT(product_image_variant.variant_code,"%") ORDER BY product_variant.sort_order ASC LIMIT 1),0),0) AS id_product_variant,		
			product.product_type,
			product.sku,
			product.featured,
			product.used,
			product.min_qty,
			product.cost_price,
			product.special_price_to_date,
			product.id_rebate_coupon,
			product.model,
			product.brand,
			product.on_sale_end_date,
			product.date_created,
			product_rating_count.avg_rating,
			product_rating_count.total_rating,
			product.has_variants,
			product_description.name,
			product_description.alias,
			product_description.short_desc,
			product_image_variant_description.name AS variant_name,
			IF(product.used = 0 AND product.date_created >= DATE_SUB("' . $current_datetime . '", INTERVAL "' . $config_site['cf_new_products_no_days'] . '" DAY),1,0) AS new_product,
			IF(config_display_price_exceptions.id_product IS NOT NULL,1,0) AS display_price_exception,
			calc_sell_price(product.price,product_variant.price_type,product_variant.price,0,0,0,0) AS price,
            calc_sell_price(product.sell_price,product_variant.price_type,product_variant.price,customer_type.percent_discount,customer_type.apply_on_rebate,rebate_coupon.discount_type,rebate_coupon.discount) AS sell_price
            '
                . (isset($filters['suggested_products']) || isset($filters['related_products']) ? (isset($filters['suggested_products']) ? ',product_suggestion.sort_order ' : ',product_related.sort_order ') : ',product_variant.sort_order ') . '
			FROM
			product	
			
			INNER JOIN 
			product_description
			ON
			(product.id = product_description.id_product AND product_description.language_code = "' . $_SESSION['customer']['language'] . '")
			
			LEFT JOIN 
			(product_image_variant CROSS JOIN product_image_variant_description)
			ON 
			(product.id = product_image_variant.id_product AND product_image_variant.displayed_in_listing = 1 AND product_image_variant.id = product_image_variant_description.id_product_image_variant AND product_image_variant_description.language_code = product_description.language_code) 			
					
			LEFT JOIN
			product_rating_count
			ON
			(product.id = product_rating_count.id_product) 		
			
			LEFT JOIN 
			config_display_price_exceptions
			ON
			(product.id = config_display_price_exceptions.id_product)
			
			'
                . (isset($filters['id_category']) ? ' INNER JOIN product_category ON (product.id = product_category.id_product) ' : '')
                . (isset($filters['suggested_products']) ? ' INNER JOIN product_suggestion ON (product.id = product_suggestion.id_product_suggestion) ' : '')
                . (isset($filters['related_products']) ? ' INNER JOIN product_related ON (product.id = product_related.id_product_related) ' : '') . '
			
			LEFT JOIN
            customer_type
            ON
            (customer_type.id = "' . $id_customer_type . '")
            
            LEFT JOIN
            rebate_coupon 
            ON
            (product.id_rebate_coupon = rebate_coupon.id) 
            
            LEFT JOIN 
            (product_variant)
            ON
            (product_variant.id = IF(product_image_variant.id IS NOT NULL,IFNULL((SELECT product_variant.id FROM product_variant WHERE product_variant.id_product = product.id AND product_variant.active = 1 AND product_variant.variant_code LIKE CONCAT(product_image_variant.variant_code,"%") ORDER BY product_variant.sort_order ASC LIMIT 1),0),0))
			
			WHERE
			product.active = 1
			AND 
			product.display_in_catalog = 1
			AND 
			product.date_displayed <= "' . $current_datetime . '" '
                . (isset($filters['id_category']) ? ' AND product_category.id_category = "' . (int) $filters['id_category'] . '" ' : '')
                . (isset($filters['suggested_products']) ? ' AND product_suggestion.id_product = "' . (int) $filters['suggested_products'] . '" ' : '')
                . (isset($filters['related_products']) ? ' AND product_related.id_product = "' . (int) $filters['related_products'] . '" ' : '')
                . (!empty($where) ? ' AND ' . $where : '')
                . (isset($filters['suggested_products']) ? ' ORDER BY sort_order ASC' : '')
                . (isset($filters['related_products']) ? ' ORDER BY sort_order ASC' : '') . '						
			) AS t
			' . (!empty($where_price) ? ' WHERE ' . $where_price : '') . '
		)';

        if (!$this->mysqli->query($sql))
            throw new Exception('An error occured while trying to create temporary table.' . "\r\n\r\n" . $this->mysqli->error);

        //echo '<pre>'.$sql.'</pre>';	

        if ($this->filters['id_category']) {
            $this->current_id_category = $this->filters['id_category'];
            unset($this->filters['id_category']);
        }
    }

    public function count_products($filters = array())
    {
        global $config_site, $url_prefix;

        $products = array();

        $current_datetime = date('Y-m-d H:i:s');
        $id_customer_type = $_SESSION['customer']['id_customer_type'];

        $orderby = (int) $orderby;


        if ($orderby)
            $filters['orderby'] = $orderby;

        $this->filters = $filters;

        /*
          FILTERS
          - by category
          - featured products
          - new products
          - on sale
          - top sellers
          - search string
          - brand
          - price range
          - ratings

          array(
          'id_category' => $id_category,
          'featured_products' => 1,
          'new_products' => 1,
          'on_sale' => 1,
          'top_sellers' => 1,

          's' => $s,
          'brand' => array(),
          'price_range' => array(),
          'ratings' => array(),
          )

          ORDERING
          - by created date
          - featured products
          - best rating
          - lowest price
          - most reviews
         */

        // create tmp table / filter
        $this->create_tmp();

        // query results and order
        $sql = 'SELECT 
		COUNT(product.id) AS total
		
		FROM 
		tmp_product_tpl AS product ';

        if (!$result = $this->mysqli->query($sql))
            throw new Exception('An error occured while trying to query table.' . "\r\n\r\n" . $this->mysqli->error);
        $row = $result->fetch_assoc();
        $result->free();

        return $row['total'];
    }

    public function get_products($filters = array(), $orderby = 0, $offset = 0, $limit = 12, $create_tmp = 0)
    {
        global $config_site, $url_prefix;

        $products = array();

        $current_datetime = date('Y-m-d H:i:s');
        $id_customer_type = $_SESSION['customer']['id_customer_type'];

        if ($id_customer_type) {
            if (!$result = $this->mysqli->query('SELECT * FROM customer_type WHERE id = "' . $id_customer_type . '" LIMIT 1'))
                throw new Exception('An error occured while trying to get customer type.');

            $row = $result->fetch_assoc();
            $apply_on_rebate = $row['apply_on_rebate'];
        }

        $offset = (int) $offset;
        $limit = (int) $limit;
        if (!$limit)
            $limit = 12;

        $orderby = (int) $orderby;


        if ($orderby)
            $filters['orderby'] = $orderby;
        if ($offset)
            $filters['offset'] = $offset;
        if ($limit)
            $filters['limit'] = $limit;

        $this->filters = $filters;

        /*
          FILTERS
          - by category
          - featured products
          - new products
          - on sale
          - top sellers
          - search string
          - brand
          - price range
          - ratings

          array(
          'id_category' => $id_category,
          'featured_products' => 1,
          'new_products' => 1,
          'on_sale' => 1,
          'top_sellers' => 1,

          's' => $s,
          'brand' => array(),
          'price_range' => array(),
          'ratings' => array(),
          )

          ORDERING
          - by created date
          - featured products
          - best rating
          - lowest price
          - most reviews
         */

        // create tmp table / filter
        if ($create_tmp)
            $this->create_tmp();

        // query results and order
        $sql = 'SELECT 
		product.id,
		product.id_product_variant,
		product.variant_code,
		product.id_product_image_variant,
		product.price,
		product.cost_price,
		product.sell_price,
		product.sku,
		product.featured,
		product.min_qty,
		product.variant_code,
		product.name,
		product.variant_name,
		product.alias,
		product.avg_rating,
		product.total_rating,
		product.has_variants,
		product.on_sale_end_date,
		product.date_created,
		product.new_product,
		product.product_type,
		product.sort_order,
		product.brand,
		product.short_desc,
		product.display_price_exception,
		product.special_price_to_date,
		IF(config_allow_add_to_cart_exceptions.id_product IS NOT NULL,1,0) AS allow_add_to_cart_exceptions
		FROM 
		tmp_product_tpl AS product 
		LEFT JOIN config_allow_add_to_cart_exceptions ON (product.id = config_allow_add_to_cart_exceptions.id_product)';

        $this->sql = preg_replace('/SELECT(.*?)FROM/is', 'SELECT {{column_names}} FROM ', $sql);

        switch ($orderby) {
            // featured products
            case 0:
                $sql .= ' ORDER BY 
				featured DESC,
				date_created DESC,
				sort_order';
                break;
            // best rating
            case 1:
                $sql .= ' ORDER BY 
				avg_rating DESC,
				featured,
				date_created DESC,
				sort_order';
                break;
            // lowest price
            case 2:
                $sql .= ' ORDER BY 
				sell_price,
				featured,
				date_created DESC,
				sort_order';
                break;
            // highest price
            case 3:
                $sql .= ' ORDER BY 
				sell_price DESC,
				featured,
				date_created DESC,
				sort_order';
                break;
            // most reviews
            case 4:
                $sql .= ' ORDER BY 
				total_rating DESC,
				featured,
				date_created DESC,
				sort_order';
                break;
            // by name asc
            case 5:
                $sql .= ' ORDER BY 
				name ASC,
				date_created DESC,
				sort_order';
                break;
            // by name desc
            case 6:
                $sql .= ' ORDER BY 
				name DESC,
				date_created DESC,
				sort_order';
                break;
            // by Random
            case 7:
                $sql .= ' ORDER BY rand()';
                break;
        }



        if (!$result = $this->mysqli->query($sql))
            throw new Exception('An error occured while trying to query table.' . "\r\n\r\n" . $this->mysqli->error);

        $products = array();

        if (!$stmt_variant_image = $this->mysqli->prepare('SELECT 
		product_image_variant_image.filename
		FROM 
		product_image_variant
		INNER JOIN
		product_image_variant_image
		ON
		(product_image_variant.id = product_image_variant_image.id_product_image_variant)
		WHERE
		product_image_variant.id_product = ?
		AND
		product_image_variant.variant_code = ?
		AND
		product_image_variant_image.cover = 1
		ORDER BY 
		product_image_variant_image.cover DESC,
		product_image_variant_image.sort_order ASC
		LIMIT 1'))
            throw new Exception('An error occured while trying to prepare get variant cover image statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_image = $this->mysqli->prepare('SELECT
		product_image.filename
		FROM
		product_image
		WHERE
		product_image.id_product = ?
		AND
		product_image.cover = 1
		LIMIT 1'))
            throw new Exception('An error occured while trying to prepare get variant image statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_has_been_sold = $this->mysqli->prepare('SELECT
		has_been_sold(?,?,?)
		'))
            throw new Exception('An error occured while trying to prepare check if product has been sold statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_in_stock = $this->mysqli->prepare('SELECT
		is_product_in_stock(?,?,?)
		'))
            throw new Exception('An error occured while trying to prepare check if product is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_variants_in_stock = $this->mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		product_variant.variant_code LIKE CONCAT(?,"%")
		AND
		is_product_in_stock(product_variant.id_product,product_variant.id,1) = 1
		AND 
		product_variant.id_product = ?
		'))
            throw new Exception('An error occured while trying to prepare check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_variants_in_stock_no_variant_code = $this->mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		is_product_in_stock(product_variant.id_product,product_variant.id,1) = 1
		AND 
		product_variant.id_product = ?
		'))
            throw new Exception('An error occured while trying to prepare check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

        if (!$stmt_single_variant = $this->mysqli->prepare('SELECT
		COUNT(product_variant.id)
		FROM
		product_variant
		WHERE
		product_variant.variant_code = ?
		AND
		product_variant.active = 1
		AND 
		product_variant.id_product = ?
		'))
            throw new Exception('An error occured while trying to prepare check if product is a single variant statement.' . "\r\n\r\n" . $this->mysqli->error);

        if ($result->num_rows) {
            while ($row = $result->fetch_assoc()) {
                $on_sale = ($row['sell_price'] < $row['price']) ? 1 : 0;
                //$on_sale_end_date = ($row['on_sale_end_date'] != '0000-00-00 00:00:00') ? df_date($row['on_sale_end_date'],1,3):'';													
                // url
                $url = $url_prefix . 'product/' . $row['alias'];
                if (!empty($row['variant_code']))
                    $url .= '?variant_code=' . $row['variant_code'];

                $products[] = array(
                    'id' => $row['id'],
                    'sku' => $row['sku'],
                    'name' => $row['name'],
                    'short_desc' => $row['short_desc'],
                    'has_variants' => $row['has_variants'],
                    'variant_code' => $row['variant_code'],
                    'variant_name' => $row['variant_name'],
                    'price' => $row['price'],
                    'cost_price' => $row['cost_price'],
                    'sell_price' => $row['sell_price'],
                    'featured' => $row['featured'],
                    'used' => $row['used'],
                    'min_qty' => $row['min_qty'],
                    'new_product' => $row['new_product'],
                    'product_type' => $row['product_type'],
                    'on_sale' => $on_sale,
                    'on_sale_end_date' => (!$id_customer_type || $id_customer_type && $apply_on_rebate || $id_customer_type && !$apply_on_rebate && $row['special_price_to_date'] == $row['on_sale_end_date'] ? $row['on_sale_end_date'] : '0000-00-00 00:00:00'),
                    'url' => $url,
                    'brand' => $row['brand'],
                    'avg_rating' => $row['avg_rating'],
                    'total_rating' => $row['total_rating'],
                    'display_price_exception' => $row['display_price_exception'],
                    'allow_add_to_cart_exceptions' => $row['allow_add_to_cart_exceptions'],
                );

                // totals
                ++$this->total_products;
                if ($row['featured'])
                    ++$this->total_featured;
                if ($row['new_product'])
                    ++$this->total_new;
                if ($on_sale)
                    ++$this->total_on_sale;
                if ($row['used'])
                    ++$this->total_used;
                if ($row['product_type'] == 1)
                    ++$this->total_combo;
                if ($row['product_type'] == 2)
                    ++$this->total_bundled;

                if (!empty($row['brand'])) {
                    if (!isset($this->brands[$row['brand']]))
                        $this->brands[$row['brand']] = array('brand' => $row['brand'], 'total' => 1);
                    else
                        ++$this->brands[$row['brand']]['total'];
                }

                if ($row['avg_rating'] > 0) {
                    if (!isset($this->ratings[$row['avg_rating']]))
                        $this->ratings[$row['avg_rating']] = array('avg_rating' => $row['avg_rating'], 'total' => 1);
                    else
                        ++$this->ratings[$row['avg_rating']]['total'];
                }

                $this->ids[] = $row['id'];

                if ($row['sell_price'] > $this->max_price)
                    $this->max_price = $row['sell_price'];
            }
        }

        ksort($this->brands);
        krsort($this->ratings);

        $this->products = $products;

        // split products array based on offet and limit
        $products = array_slice($products, $offset, $limit);


        foreach ($products as $key => $row) {
            $filename = '';
            $temp_filename = '';
            $filename_thumb = '';
            $filename_cover = '';
            $filename_suggest = '';
            $filename_zoom = '';

            // check if product has been sold
            if (!$stmt_has_been_sold->bind_param("iis", $row['id'], $row['id_product_variant'], $row['variant_code']))
                throw new Exception('An error occured while trying to bind params to check if product has been sold statement.' . "\r\n\r\n" . $this->mysqli->error);

            /* Execute the statement */
            if (!$stmt_has_been_sold->execute())
                throw new Exception('An error occured while trying to execute check if product has been sold statement.' . "\r\n\r\n" . $this->mysqli->error);

            /* store result */
            $stmt_has_been_sold->store_result();

            /* bind result variables */
            $stmt_has_been_sold->bind_result($has_been_sold);

            $stmt_has_been_sold->fetch();

            $products[$key]['has_been_sold'] = $has_been_sold;

            // check if variant
            if (!empty($row['variant_code'])) {
                /*
                  This code below outputs this result (example)
                  12:25,13:27,14:32
                  12:25,13:27,14
                  12:25,13,14

                  In that order, it allows us to get the variant codes of product image variants and loop through each to find an image
                 */
                $i = sizeof(explode(',', $row['variant_code']));
                $variant_codes = array();
                $tmp_array = explode(',', $row['variant_code']);
                for ($x = 0; $x < $i; ++$x) {
                    $tmpstr = implode(',', $tmp_array);
                    if (!in_array($tmpstr, $variant_codes))
                        $variant_codes[] = $tmpstr;

                    $z = 1;
                    foreach (array_reverse($tmp_array, 1) as $k => $v) {
                        // skip the last array (the first one we do not split)
                        if ($z == $i)
                            break;

                        if (strstr($v, ':')) {
                            $v = array_shift(explode(':', $v));
                            $tmp_array[$k] = $v;
                            break;
                        }
                        ++$z;
                    }
                }

                foreach ($variant_codes as $row_variant_code) {
                    // check if we have a cover image for this variant code
                    if (!$stmt_variant_image->bind_param("is", $row['id'], $row_variant_code))
                        throw new Exception('An error occured while trying to bind params to get variant cover image statement.' . "\r\n\r\n" . $this->mysqli->error);

                    /* Execute the statement */
                    if (!$stmt_variant_image->execute())
                        throw new Exception('An error occured while trying to get variant cover image.' . "\r\n\r\n" . $this->mysqli->error);

                    /* store result */
                    $stmt_variant_image->store_result();

                    /* bind result variables */
                    $stmt_variant_image->bind_result($filename);

                    $stmt_variant_image->fetch();

                    // if an image was found
                    if (!empty($filename))
                        break;
                }
            }

            if (!$filename) {
                if (!$stmt_image->bind_param("i", $row['id']))
                    throw new Exception('An error occured while trying to bind params to get image statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* Execute the statement */
                if (!$stmt_image->execute())
                    throw new Exception('An error occured while trying to execute get image statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* store result */
                $stmt_image->store_result();

                /* bind result variables */
                $stmt_image->bind_result($filename);

                $stmt_image->fetch();
            }

            // if we have an image create link
            if ($filename && is_file(dirname(__FILE__) . '/../../images/products/listing/' . $filename)) {
                $temp_filename = $filename;
                $filename = '/images/products/listing/' . $filename;
            } else {
                $filename = '';
                $temp_filename = '';
            }

            if ($temp_filename && is_file(dirname(__FILE__) . '/../../images/products/thumb/' . $temp_filename))
                $filename_thumb = '/images/products/thumb/' . $temp_filename;
            else
                $filename_thumb = '';

            if ($temp_filename && is_file(dirname(__FILE__) . '/../../images/products/cover/' . $temp_filename))
                $filename_cover = '/images/products/cover/' . $temp_filename;
            else
                $filename_cover = '';

            if ($temp_filename && is_file(dirname(__FILE__) . '/../../images/products/suggest/' . $temp_filename))
                $filename_suggest = '/images/products/suggest/' . $temp_filename;
            else
                $filename_suggest = '';

            if ($temp_filename && is_file(dirname(__FILE__) . '/../../images/products/zoom/' . $temp_filename))
                $filename_zoom = '/images/products/zoom/' . $temp_filename;
            else
                $filename_zoom = '';



            // check if product is in stock
            $qty = 1;
            if (!$row['has_variants']) {
                $id_product_variant = 0;

                if (!$stmt_in_stock->bind_param("iii", $row['id'], $id_product_variant, $qty))
                    throw new Exception('An error occured while trying to bind params to check if product is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* Execute the statement */
                if (!$stmt_in_stock->execute())
                    throw new Exception('An error occured while trying to execute check if product is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* store result */
                $stmt_in_stock->store_result();

                /* bind result variables */
                $stmt_in_stock->bind_result($in_stock);

                $stmt_in_stock->fetch();
                // check if at least one variant is in stock
            } else {
                if (!empty($row['variant_code'])) {
                    if (!$stmt_variants_in_stock->bind_param("si", $row['variant_code'], $row['id']))
                        throw new Exception('An error occured while trying to bind params to check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                    /* Execute the statement */
                    if (!$stmt_variants_in_stock->execute())
                        throw new Exception('An error occured while trying to execute check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                    /* store result */
                    $stmt_variants_in_stock->store_result();

                    /* bind result variables */
                    $stmt_variants_in_stock->bind_result($in_stock);

                    $stmt_variants_in_stock->fetch();
                } else {
                    if (!$stmt_variants_in_stock_no_variant_code->bind_param("i", $row['id']))
                        throw new Exception('An error occured while trying to bind params to check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                    /* Execute the statement */
                    if (!$stmt_variants_in_stock_no_variant_code->execute())
                        throw new Exception('An error occured while trying to execute check if product variants is in stock statement.' . "\r\n\r\n" . $this->mysqli->error);

                    /* store result */
                    $stmt_variants_in_stock_no_variant_code->store_result();

                    /* bind result variables */
                    $stmt_variants_in_stock_no_variant_code->bind_result($in_stock);

                    $stmt_variants_in_stock_no_variant_code->fetch();
                }

                $in_stock = $in_stock ? 1 : 0;

                if (!$stmt_single_variant->bind_param("si", $row['variant_code'], $row['id']))
                    throw new Exception('An error occured while trying to bind params to check if product is single variant statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* Execute the statement */
                if (!$stmt_single_variant->execute())
                    throw new Exception('An error occured while trying to execute check if product is single variant statement.' . "\r\n\r\n" . $this->mysqli->error);

                /* store result */
                $stmt_single_variant->store_result();

                /* bind result variables */
                $stmt_single_variant->bind_result($single_variant);

                $stmt_single_variant->fetch();

                $single_variant = $single_variant ? 1 : 0;
            }

            $products[$key]['image'] = $filename;
            $products[$key]['image_thumb'] = $filename_thumb;
            $products[$key]['image_suggest'] = $filename_suggest;
            $products[$key]['image_cover'] = $filename_cover;
            $products[$key]['image_zoom'] = $filename_zoom;
            $products[$key]['single_variant'] = $single_variant;
            $products[$key]['in_stock'] = $in_stock;

            $filename = '';
            $temp_filename = '';
            $filename_thumb = '';
            $filename_cover = '';
            $filename_suggest = '';
            $filename_zoom = '';
        }

        $result->free();

        $stmt_variant_image->close();
        $stmt_image->close();
        $stmt_has_been_sold->close();
        $stmt_in_stock->close();
        $stmt_variants_in_stock->close();
        $stmt_single_variant->close();

        return $products;
    }

    public function get_filter_by_featured_products()
    {
        global $config_site;

        if ($config_site['cf_show_featured_products_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['featured_products']) && $array['featured_products'] == 1)
                unset($array['featured_products']);
            else
                $array['featured_products'] = 1;

            $querystr = http_build_query($array);

            /*
              // count products
              $total = 0;
              if (sizeof($this->products)) {
              foreach ($this->products as $row) {
              if ($row['featured']) ++$total;
              }
              } */

            $total = $this->total_featured;

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['featured_products']) && $this->filters['featured_products'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_products_on_sale()
    {
        global $config_site;

        if ($config_site['cf_show_on_sale_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['on_sale']) && $array['on_sale'] == 1)
                unset($array['on_sale']);
            else
                $array['on_sale'] = 1;

            $querystr = http_build_query($array);

            // count products
            /* $total = 0;
              if (sizeof($this->products)) {
              foreach ($this->products as $row) {
              if ($row['on_sale']) ++$total;
              }
              } */

            /* $sql = str_replace('{{column_names}}','COUNT(product.id) AS total',$this->sql);
              $sql .= ' WHERE product.sell_price < product.price ';

              if (!$result = $this->mysqli->query($sql)) throw new Exception('An error occured while trying to get max sell price.'."\r\n\r\n".$this->mysqli->error);
              $row = $result->fetch_assoc();
              $total = $row['total']; */

            $total = $this->total_on_sale;

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['on_sale']) && $this->filters['on_sale'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_new_products()
    {
        global $config_site;

        if ($config_site['cf_show_new_products_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['new_products']) && $array['new_products'] == 1)
                unset($array['new_products']);
            else
                $array['new_products'] = 1;

            $querystr = http_build_query($array);

            // count products
            $total = 0;
            /* if (sizeof($this->products)) {	
              foreach ($this->products as $row) {
              if ($row['new_product']) ++$total;
              }
              } */
            /*
              $sql = str_replace('{{column_names}}','COUNT(product.id) AS total',$this->sql);
              $sql .= ' WHERE product.new_product = 1 ';

              if (!$result = $this->mysqli->query($sql)) throw new Exception('An error occured while trying to get max sell price.'."\r\n\r\n".$this->mysqli->error);
              $row = $result->fetch_assoc();
              $total = $row['total']; */

            $total = $this->total_new;

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['new_products']) && $this->filters['new_products'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_top_sellers()
    {
        global $config_site;

        if ($config_site['cf_show_top_sellers_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['top_sellers']) && $array['top_sellers'] == 1)
                unset($array['top_sellers']);
            else
                $array['top_sellers'] = 1;

            $querystr = http_build_query($array);

            // count products
            $total = 0;
            if (sizeof($this->products)) {
                foreach ($this->products as $row) {
                    if ($row['has_been_sold'])
                        ++$total;
                }
            }

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['top_sellers']) && $this->filters['top_sellers'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_combo_deals()
    {
        global $config_site;

        if ($config_site['cf_show_combo_deals_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['combo_deals']) && $array['combo_deals'] == 1)
                unset($array['combo_deals']);
            else
                $array['combo_deals'] = 1;

            $querystr = http_build_query($array);

            /*
              // count products
              $total = 0;
              if (sizeof($this->products)) {
              foreach ($this->products as $row) {
              if ($row['product_type'] == 1) ++$total;
              }
              } */

            $total = $this->total_combo;

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['combo_deals']) && $this->filters['combo_deals'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_bundled_products()
    {
        global $config_site;

        if ($config_site['cf_show_bundled_product_menu']) {
            $array = $this->filters;

            unset($array['id_category']);

            if (isset($array['bundled_products']) && $array['bundled_products'] == 1)
                unset($array['bundled_products']);
            else
                $array['bundled_products'] = 1;

            $querystr = http_build_query($array);

            /*
              // count products
              $total = 0;
              if (sizeof($this->products)) {
              foreach ($this->products as $row) {
              if ($row['product_type'] == 2) ++$total;
              }
              } */

            $total = $this->total_bundled;

            return array(
                'url' => (!empty($querystr) ? '?' . $querystr : ''),
                'total' => $total,
                'selected' => isset($this->filters['bundled_products']) && $this->filters['bundled_products'] == 1 ? 1 : 0,
            );
        } else
            return array();
    }

    public function get_filter_by_brand()
    {
        global $config_site;

        if ($config_site['cf_show_brands']) {
            // current filters selected		
            $filters = $this->filters;
            // brands 
            $brands = array();

            foreach ($this->brands as $row) {
                // add brand to filters list and create query string
                $tmp_array = $filters;
                $tmp_array['brand'] = $row['brand'];
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                $brands[$row['brand']] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'brand' => $row['brand'],
                    'total' => $row['total'],
                );
            }


            // if we have filters by brand		
            if (isset($filters['brand'])) {
                $tmp_array = $filters;
                unset($tmp_array['brand']);
                $querystr = http_build_query($tmp_array);

                $brands[$filters['brand']]['url'] = (!empty($querystr) ? '?' . $querystr : '');

                $brands[$filters['brand']]['selected'] = 1;

                $brands = array(0 => $brands[$filters['brand']]);
            }

            return $brands;
        } else
            return array();
    }

    public function get_filter_by_price()
    {
        global $config_site;

        if ($config_site['display_price'] && $config_site['cf_show_price_range']) {
            // current filters selected		
            $filters = $this->filters;
            // price range 
            $prices = array();

            /*
              // get max sell price
              $sql = str_replace('{{column_names}}','MAX(product.sell_price) AS max_price',$this->sql);

              if (!$result = $this->mysqli->query($sql)) throw new Exception('An error occured while trying to get max sell price.'."\r\n\r\n".$this->mysqli->error);
              $row = $result->fetch_assoc();
              $result->free();
              $max_price = $row['max_price']; */

            $max_price = $this->max_price;
            $min_price = 0.00;
            $price_increment = $config_site["price_increment"];

            // check if category has it's own price increment
            if (!$result = $this->mysqli->query('SELECT price_increment FROM category WHERE id = ' . (int) $this->current_id_category . ' LIMIT 1'))
                throw new Exception('An error occured while trying to get category price increment.' . "\r\n\r\n" . $this->mysqli->error);
            $row = $result->fetch_assoc();
            $result->free();

            if ($row['price_increment'])
                $price_increment = $row['price_increment'];

            $counter_price = ($min_price + $price_increment) - 0.01;

            $products = $this->products;

            do {
                // count products
                $total = 0;
                if (sizeof($products)) {
                    foreach ($products as $key => $row) {
                        if ($row['sell_price'] >= $min_price && $row['sell_price'] <= $counter_price && !$row['display_price_exception']) {
                            ++$total;

                            unset($products[$key]);
                        }
                    }
                }

                if ($total) {

                    // add price range to filters list and create query string
                    $tmp_array = $filters;
                    $tmp_array['price'] = $min_price . '-' . $counter_price;
                    unset($tmp_array['id_category']);
                    $querystr = http_build_query($tmp_array);

                    $prices[$min_price . '-' . $counter_price] = array(
                        'url' => (!empty($querystr) ? '?' . $querystr : ''),
                        'min' => nf_currency($min_price, 0),
                        'max' => nf_currency($counter_price, 0),
                        'total' => $total,
                    );
                }

                // if max price fits in a range we are done
                if ($max_price >= $min_price && $max_price <= $counter_price)
                    break;

                $min_price = $counter_price + 0.01;
                //$counter_price += $price_increment-0.01;
                $counter_price += $price_increment;
            } while (true);

            // if we have filters by price		
            if (isset($filters['price'])) {
                $tmp_array = $filters;
                unset($tmp_array['price']);
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                $prices[$filters['price']]['url'] = (!empty($querystr) ? '?' . $querystr : '');
                $prices[$filters['price']]['selected'] = 1;

                //$prices = array(0 => $prices[$filters['price']]);
            }

            return $prices;
        } else
            return array();
    }

    public function get_filter_by_rating()
    {
        global $config_site;

        if ($config_site['cf_show_ratings'] && $config_site['display_menu_rate_product']) {
            // current filters selected		
            $filters = $this->filters;
            // ratings 
            $ratings = array();

            /*

              // get list of brands from search results
              $sql = str_replace('{{column_names}}','product.avg_rating, COUNT(product.id) AS total',$this->sql).' WHERE product.avg_rating > 1 GROUP BY product.avg_rating ORDER BY avg_rating ASC';

              if (!$result = $this->mysqli->query($sql)) throw new Exception('An error occured while trying to get ratings.'."\r\n\r\n".$this->mysqli->error);
              while ($row = $result->fetch_assoc()) {
              // add rating to filters list and create query string
              $tmp_array = $filters;
              $tmp_array['rating'] = array($row['avg_rating'] => $row['avg_rating']);
              unset($tmp_array['id_category']);
              $querystr = http_build_query($tmp_array);

              $ratings[$row['avg_rating']] = array(
              'url' => (!empty($querystr) ? '?'.$querystr:''),
              'rating' => $row['avg_rating'],
              'total' => $row['total'],
              );
              }
              $result->free();

             */

            /* if (sizeof($this->products)) {
              foreach ($this->products as $row) {
              if ($row['avg_rating'] > 0) {
              if (!isset($ratings[$row['avg_rating']])) {
              // add rating to filters list and create query string
              $tmp_array = $filters;
              $tmp_array['rating'] = array($row['avg_rating'] => $row['avg_rating']);
              unset($tmp_array['id_category']);
              $querystr = http_build_query($tmp_array);

              $ratings[$row['avg_rating']] = array(
              'url' => (!empty($querystr) ? '?'.$querystr:''),
              'rating' => $row['avg_rating'],
              'total' => 1,
              );
              } else ++$ratings[$row['avg_rating']]['total'];
              }
              }
              } */

            foreach ($this->ratings as $row) {
                // add rating to filters list and create query string
                $tmp_array = $filters;
                $tmp_array['rating'] = array($row['avg_rating'] => $row['avg_rating']);
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                $ratings[$row['avg_rating']] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'rating' => $row['avg_rating'],
                    'total' => $row['total'],
                );
            }

            // if we have filters by rating		
            if (isset($filters['rating'])) {
                foreach ($filters['rating'] as $row_rating) {
                    $tmp_array = $filters;
                    unset($tmp_array['rating'][$row_rating]);
                    unset($tmp_array['id_category']);
                    $querystr = http_build_query($tmp_array);

                    $ratings[$row_rating]['url'] = (!empty($querystr) ? '?' . $querystr : '');
                    $ratings[$row_rating]['selected'] = 1;

                    //$ratings = array(0 => $ratings[$filters['rating']]);
                }
            }

            return $ratings;
        } else
            return array();
    }

    public function get_filters()
    {
        $filters = array();

        if (sizeof($this->filters)) {
            // featured products
            if (isset($this->filters['featured_products']) && $this->filters['featured_products'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['featured_products']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'featured_products',
                );
            }

            // new products
            if (isset($this->filters['new_products']) && $this->filters['new_products'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['new_products']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'new_products',
                );
            }

            // on sale
            if (isset($this->filters['on_sale']) && $this->filters['on_sale'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['on_sale']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'on_sale',
                );
            }

            // top sellers
            if (isset($this->filters['top_sellers']) && $this->filters['top_sellers'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['top_sellers']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'top_sellers',
                );
            }

            // combo deals
            if (isset($this->filters['combo_deals']) && $this->filters['combo_deals'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['combo_deals']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'combo_deals',
                );
            }

            // bundled products
            if (isset($this->filters['bundled_products']) && $this->filters['bundled_products'] == 1) {
                $tmp_array = $this->filters;
                unset($tmp_array['bundled_products']);
                unset($tmp_array['id_category']);

                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'type' => 'bundled_products',
                );
            }

            // search string
            if (isset($this->filters['s']) && !empty($this->filters['s'])) {
                $tmp_array = $this->filters;
                $s = htmlspecialchars($tmp_array['s']);

                unset($tmp_array['s']);
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    's' => $s,
                    'type' => 's',
                );
            }

            // brand
            if (isset($this->filters['brand']) && sizeof($this->filters['brand'])) {
                $tmp_array = $this->filters;
                unset($tmp_array['brand']);
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'brand' => htmlspecialchars($this->filters['brand']),
                    'type' => 'brand',
                );
            }

            // price range
            if (isset($this->filters['price']) && sizeof($this->filters['price'])) {
                $tmp_array = $this->filters;
                unset($tmp_array['price']);
                unset($tmp_array['id_category']);
                $querystr = http_build_query($tmp_array);

                list($min, $max) = explode('-', $this->filters['price']);

                $filters[] = array(
                    'url' => (!empty($querystr) ? '?' . $querystr : ''),
                    'min' => nf_currency($min, 0),
                    'max' => nf_currency($max, 0),
                    'min_raw' => $min,
                    'max_raw' => $max,
                    'type' => 'price',
                );
            }

            // ratings
            if (isset($this->filters['rating']) && sizeof($this->filters['rating'])) {
                foreach ($this->filters['rating'] as $row_rating) {
                    $tmp_array = $this->filters;
                    unset($tmp_array['rating'][$row_rating]);
                    unset($tmp_array['id_category']);
                    $querystr = http_build_query($tmp_array);

                    $filters[] = array(
                        'url' => (!empty($querystr) ? '?' . $querystr : ''),
                        'rating' => $row_rating,
                        'type' => 'rating',
                    );
                }
            }
        }

        return $filters;
    }

    public function get_filters_array()
    {
        return $this->filters;
    }

    public function get_total_products_in_category($id_category)
    {
        /* $sql = str_replace('{{column_names}}','COUNT(product.id) AS total',$this->sql);

          if (strstr($sql,'WHERE')) $sql = preg_replace('/WHERE/si','INNER JOIN product_category ON (product.id = product_category.id_product) WHERE product_category.id_category = "'.$id_category.'" AND ',$sql);
          else $sql .= ' INNER JOIN product_category ON (product.id = product_category.id_product) WHERE product_category.id_category = "'.$id_category.'"';

          if (!$result = $this->mysqli->query($sql)) throw new Exception('An error occured while trying to get total products in category.'."\r\n\r\n".$this->mysqli->error);

          $row = $result->fetch_assoc(); */

        /* if (!is_array($this->ids) || !sizeof($this->ids)) {
          $this->ids = array();

          if (sizeof($this->products)) {
          foreach ($this->products as $key => $row) {
          $this->ids[] = $row['id'];
          }
          }
          } */

        if (sizeof($this->ids)) {

            if (!$result = $this->mysqli->query('SELECT COUNT(product_category.id_product) AS total FROM product_category WHERE id_category = "' . $id_category . '" AND id_product IN (' . implode(',', $this->ids) . ')'))
                throw new Exception('An error occured while trying to get product count.');
            $row = $result->fetch_assoc();
            $result->free();
        }

        return $row['total'];
    }

    public function get_total_products()
    {
        return $this->total_products;
    }

}

?>