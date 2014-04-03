-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 02, 2014 at 01:22 PM
-- Server version: 5.5.36-cll
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `devleoha_01`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`devleoha`@`localhost` FUNCTION `calc_sell_price`(`price` DECIMAL(13,2), `price_type` TINYINT(1), `variant_price` DECIMAL(13,2), `customer_discount` TINYINT, `customer_discount_apply_rebate` TINYINT, `rebate_type` TINYINT, `rebate_discount` TINYINT) RETURNS decimal(13,2)
BEGIN
IF variant_price > 0 THEN
	SET price = price+(CASE price_type
    WHEN 0 THEN variant_price
    WHEN 1 THEN ROUND((price*variant_price)/100,2)
    END);
END IF;

IF customer_discount IS NOT NULL AND customer_discount > 0 THEN 
	SET price = price-IFNULL(ROUND((price*customer_discount)/100,2),0);
END IF;

IF rebate_discount IS NOT NULL AND rebate_discount > 0 AND (customer_discount IS NULL OR customer_discount IS NOT NULL AND customer_discount_apply_rebate = 1) THEN
	SET price = price-(CASE rebate_type
		WHEN 0 THEN IF(rebate_discount > price,price,rebate_discount)
		WHEN 1 THEN IFNULL(ROUND((price*rebate_discount)/100,2),0)
	END);		
END IF;

RETURN price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_applicable_rebate_id`(`id_product` INT, `price` DECIMAL(13,2)) RETURNS int(11)
BEGIN

RETURN IFNULL((SELECT 
rebate_coupon.id
FROM 
rebate_coupon		
WHERE
rebate_coupon.active = 1
AND
rebate_coupon.coupon = 0
AND
rebate_coupon.type = 0 
AND 
(
	rebate_coupon.end_date = "0000-00-00 00:00:00"
    OR
	NOW() BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
)    
AND
(
    (IF((SELECT 
    rebate_coupon_product.id_rebate_coupon
    FROM
    rebate_coupon_product 
    WHERE 
    rebate_coupon_product.id_product = id_product
    LIMIT 1) IS NOT NULL,1,0)) = 1        


    OR 

    (IF((SELECT 
    rebate_coupon_category.id_rebate_coupon
    FROM
    rebate_coupon_category INNER JOIN product_category
    ON (rebate_coupon_category.id_category = product_category.id_category)
    WHERE 
    rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
    AND
    product_category.id_product = id_product
    LIMIT 1) IS NOT NULL,1,0)) = 1
)
ORDER BY 
(CASE rebate_coupon.discount_type 
	WHEN 0 THEN rebate_coupon.discount/price
    WHEN 1 THEN	rebate_coupon.discount/100
END) DESC
LIMIT 1),0);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_bundle_product_current_price`(`id_product_bundled_product_group_product` INT, `id_customer_type` INT) RETURNS decimal(26,10)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_price DECIMAL(26,10);
DECLARE customer_type_percent_discount TINYINT(1);

SET current_datetime = NOW();

SET customer_type_percent_discount = IFNULL((SELECT percent_discount FROM customer_type WHERE id = id_customer_type),0);

SELECT 
(/* check if we use sub product regular price or the price we set */
CASE p_product.use_product_current_price
    /* no */
    WHEN 0 THEN 
        /* check price type we specified */
        (CASE pg_product.price_type
            /* fixed */
            WHEN 0 THEN
                /* add fixed price */
                pg_product.price*pg_product.qty
            /* percentage */
            WHEN 1 THEN
            	(CASE p_product.use_product_special_price
                	WHEN 0 THEN
                        /* check if sub product is a variant */
                        (CASE 
                            /* yes */
                            WHEN product_variant.id IS NOT NULL THEN
                                /* check variant price type */
                                (CASE product_variant.price_type
                                    /* fixed */
                                    WHEN 0 THEN
                                        /* get sub product price (if special price or not) 
                                        add variant price
                                        and multiply the resulting price by our product bundle percent, round to 2 decimals
                                        and multiply the resulting price by the qty specified */
                                        
                                        ROUND(((product.price+product_variant.price)*pg_product.price)/100,2)*pg_product.qty
                                    /* percentage */
                                    WHEN 1 THEN
                                        /* get sub product price (if special price or not) 
                                        multiply variant percentage to our product price, round by 2 decimals
                                        add resulting price to our sub product price
                                        and multiply resulting price by product bundle percent, round by 2 decimals
                                        and multiply resulting price by the qty specified
                                        */
                                    
                                        ROUND(((product.price+ROUND((product.price*product_variant.price)/100,2))*pg_product.price)/100,2)*pg_product.qty
                                END)
                            /* no */
                            ELSE
                                /* get sub product price (if special price or not)
                                and multiply by product bundle percent, round by 2 decimals
                                and multiply resulting price by qty specified */
                                ROUND((product.price*pg_product.price)/100,2)*pg_product.qty
                        END)                    
                    
                    WHEN 1 THEN
                        /* check if sub product is a variant */
                        (CASE 
                            /* yes */
                            WHEN product_variant.id IS NOT NULL THEN
                                /* check variant price type */
                                (CASE product_variant.price_type
                                    /* fixed */
                                    WHEN 0 THEN
                                        /* get sub product price (if special price or not) 
                                        add variant price
                                        and multiply the resulting price by our product bundle percent, round to 2 decimals
                                        and multiply the resulting price by the qty specified */
                                        
                                        ROUND(((product.sell_price+product_variant.price)*pg_product.price)/100,2)*pg_product.qty
                                    /* percentage */
                                    WHEN 1 THEN
                                        /* get sub product price (if special price or not) 
                                        multiply variant percentage to our product price, round by 2 decimals
                                        add resulting price to our sub product price
                                        and multiply resulting price by product bundle percent, round by 2 decimals
                                        and multiply resulting price by the qty specified
                                        */
                                    
                                        ROUND(((product.sell_price+ROUND((product.sell_price*product_variant.price)/100,2))*pg_product.price)/100,2)*pg_product.qty
                                END)
                            /* no */
                            ELSE
                                /* get sub product price (if special price or not)
                                and multiply by product bundle percent, round by 2 decimals
                                and multiply resulting price by qty specified */
                                ROUND((product.sell_price*pg_product.price)/100,2)*pg_product.qty
                        END)                        
                    
				END)            
        END)
    /* yes */
    WHEN 1 THEN
        (CASE p_product.use_product_special_price
            WHEN 0 THEN
                /* check if sub product is a variant */
                (CASE 
                    /* yes */
                    WHEN product_variant.id IS NOT NULL THEN
                        /* check variant price type */
                        (CASE product_variant.price_type
                            /* fixed */
                            WHEN 0 THEN
                                /* get sub product price (if special price or not) 
                                add variant price
                                and multiply the resulting price by the qty specified */
                                
                                (product.price+product_variant.price)*pg_product.qty

                            /* percentage */
                            WHEN 1 THEN
                                /* get sub product price (if special price or not) 
                                multiply variant percentage to our product price, round by 2 decimals
                                add resulting price to our sub product price
                                and multiply resulting price by the qty specified
                                */             
                                
                                (product.price+ROUND((product.price*product_variant.price)/100,2))*pg_product.qty
                        END)
                    /* no */
                    ELSE
                        product.price*pg_product.qty
                END)                
    		WHEN 1 THEN
                /* check if sub product is a variant */
                (CASE 
                    /* yes */
                    WHEN product_variant.id IS NOT NULL THEN
                        /* check variant price type */
                        (CASE product_variant.price_type
                            /* fixed */
                            WHEN 0 THEN
                                /* get sub product price (if special price or not) 
                                add variant price
                                and multiply the resulting price by the qty specified */
                                
                                (product.sell_price+product_variant.price)*pg_product.qty

                            /* percentage */
                            WHEN 1 THEN
                                /* get sub product price (if special price or not) 
                                multiply variant percentage to our product price, round by 2 decimals
                                add resulting price to our sub product price
                                and multiply resulting price by the qty specified
                                */             
                                
                                (product.sell_price+ROUND((product.sell_price*product_variant.price)/100,2))*pg_product.qty
                        END)
                    /* no */
                    ELSE
                        product.sell_price*pg_product.qty
                END)              
		END)
END) AS product_price

INTO
product_price

FROM
product_bundled_product_group_product AS pg_product
INNER JOIN
product 
ON
(pg_product.id_product = product.id)
LEFT JOIN
product_variant
ON
(pg_product.id_product_variant = product_variant.id)
INNER JOIN
(product_bundled_product_group AS pg CROSS JOIN product AS p_product)
ON
(pg_product.id_product_bundled_product_group = pg.id AND pg.id_product = p_product.id)
WHERE
pg_product.id = id_product_bundled_product_group_product
LIMIT 1;

SET product_price = product_price-((product_price*customer_type_percent_discount)/100);

RETURN product_price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_combo_base_price`(`id_product` INT) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_price DECIMAL(13,2);

SET current_datetime = NOW();

SET product_price = IFNULL((SELECT 
(SELECT 
    SUM(CASE 
        WHEN product_combo_variant.id IS NOT NULL
            /* check variant price type */
            THEN (CASE t_variant.price_type
                /* fixed */
                WHEN 0 THEN
                    /* get sub product price (no special price) 
                    add variant price
                    and multiply the resulting price by the qty specified */
                    (t.price+t_variant.price)*product_combo.qty
                /* percentage */
                WHEN 1 THEN
                    /* get sub product price (no special price) 
                    multiply variant percentage to our product price, round by 2 decimals
                    add resulting price to our sub product price
                    and multiply resulting price by the qty specified
                    */
                    (t.price+ROUND((t.price*t_variant.price)/100,2))*product_combo.qty
            END)
        ELSE
            t.price*product_combo.qty
    END)
    FROM 
    product AS t
    INNER JOIN 
    product_combo 
    ON 
    (t.id = product_combo.id_combo_product)
    LEFT JOIN 
    (product_combo_variant CROSS JOIN product_variant AS t_variant)
    ON
    (product_combo.id = product_combo_variant.id_product_combo AND product_combo_variant.id_product_variant = t_variant.id AND product_combo_variant.default_variant = 1)
    WHERE
    product_combo.id_product = product.id) 
FROM
product 
WHERE
product.id = id_product
LIMIT 1),0);

RETURN product_price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_combo_product_cart_price`(`id_cart_item_product` INT) RETURNS decimal(26,10)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_price DECIMAL(26,10);
DECLARE product_type TINYINT(1);
DECLARE discount DECIMAL(26,10);
DECLARE customer_type_percent_discount TINYINT(1);
DECLARE use_product_current_price TINYINT(1);
DECLARE use_product_special_price TINYINT(1);

SET current_datetime = NOW();

(SELECT
product.product_type,
(CASE product.discount_type 
	WHEN 0 THEN IF(product.price > 0,(product.discount/product.price),0)
    WHEN 1 THEN (product.discount/100)
END) AS discount,
IF(customer_type.id IS NOT NULL,customer_type.percent_discount,0) AS percent_discount,
product.use_product_current_price,
product.use_product_special_price
INTO
product_type,
discount,
customer_type_percent_discount,
use_product_current_price,
use_product_special_price
FROM
cart_item_product
INNER JOIN
(cart_item_product AS cip CROSS JOIN product)
ON
(cart_item_product.id_cart_item_product = cip.id AND cip.id_product = product.id)
INNER JOIN 
(cart_item CROSS JOIN cart)
ON
(cip.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
LEFT JOIN
customer_type
ON
(cart.id_customer_type = customer_type.id)
WHERE
cart_item_product.id = id_cart_item_product);

IF product_type = 1 THEN
	SET product_price = (SELECT product.price+IF(product_variant.id IS NOT NULL,(CASE product_variant.price_type
    	WHEN 0 THEN product_variant.price
        WHEN 1 THEN ROUND((product.price*product_variant.price)/100,2)
	END),0)       
    FROM
    cart_item_product
    INNER JOIN 
    product
    ON
    (cart_item_product.id_product = product.id)
    LEFT JOIN 
  	product_variant
    ON
    (cart_item_product.id_product_variant = product_variant.id)
    WHERE
    cart_item_product.id = id_cart_item_product);
    
    SET product_price = product_price-IF(discount > 0,(product_price*discount), 0);

ELSEIF product_type = 2 THEN

	SET product_price = (SELECT IFNULL((SELECT 
    /* check if we use sub product regular price or the price we set */
    (CASE use_product_current_price
        /* no */
		WHEN 0 THEN        
            /* check price type we specified */
            (CASE pg_product.price_type
                /* fixed */
                WHEN 0 THEN
                    /* add fixed price */
                    pg_product.price*pg_product.qty
                /* percentage */
                WHEN 1 THEN
                	(CASE use_product_special_price
                    	WHEN 0 THEN
                            /* check if sub product is a variant */
                            (CASE 
                                /* yes */
                                WHEN product_variant.id IS NOT NULL THEN
                                    /* check variant price type */
                                    (CASE product_variant.price_type
                                        /* fixed */
                                        WHEN 0 THEN
                                            /* get sub product price (if special price or not) 
                                            add variant price
                                            and multiply the resulting price by our product bundle percent, round to 2 decimals
                                            and multiply the resulting price by the qty specified */
                                            
                                            ROUND(((product.sell_price+product_variant.price)*pg_product.price)/100,2)*pg_product.qty
                                        /* percentage */
                                        WHEN 1 THEN
                                            /* get sub product price (if special price or not) 
                                            multiply variant percentage to our product price, round by 2 decimals
                                            add resulting price to our sub product price
                                            and multiply resulting price by product bundle percent, round by 2 decimals
                                            and multiply resulting price by the qty specified
                                            */
                                            ROUND(((product.sell_price+ROUND((product.sell_price*product_variant.price)/100,2))*pg_product.price)/100,2)*pg_product.qty
                                    END)
                                /* no */
                                ELSE
                                    /* get sub product price (if special price or not)
                                    and multiply by product bundle percent, round by 2 decimals
                                    and multiply resulting price by qty specified */
                                    ROUND((product.sell_price*pg_product.price)/100,2)*pg_product.qty
                            END)                        
                        WHEN 1 THEN
                            /* check if sub product is a variant */
                            (CASE 
                                /* yes */
                                WHEN product_variant.id IS NOT NULL THEN
                                    /* check variant price type */
                                    (CASE product_variant.price_type
                                        /* fixed */
                                        WHEN 0 THEN
                                                /* get sub product price (if special price or not) 
                                                add variant price
                                                and multiply the resulting price by our product bundle percent, round to 2 decimals
                                                and multiply the resulting price by the qty specified */
                                                
                                                ROUND(((product.price+product_variant.price)*pg_product.price)/100,2)*pg_product.qty
                                        /* percentage */
                                        WHEN 1																	
                                            THEN
                                                /* get sub product price (if special price or not) 
                                                multiply variant percentage to our product price, round by 2 decimals
                                                add resulting price to our sub product price
                                                and multiply resulting price by product bundle percent, round by 2 decimals
                                                and multiply resulting price by the qty specified
                                                */                                                                                   
                                            
	                                            ROUND(((product.price+ROUND((product.price*product_variant.price)/100,2))*pg_product.price)/100,2)*pg_product.qty
                                    END)
                                /* no */
                                ELSE
                                    /* get sub product price (if special price or not)
                                    and multiply by product bundle percent, round by 2 decimals
                                    and multiply resulting price by qty specified */
                                    ROUND((product.price*pg_product.price)/100,2)*pg_product.qty
                            END)
					END)
            END)
        /* yes */
        WHEN 1 THEN
            (CASE use_product_special_price
                WHEN 0 THEN       
                    /* check if sub product is a variant */
                    (CASE 
                        /* yes */
                        WHEN product_variant.id IS NOT NULL THEN
                            /* check variant price type */
                            (CASE product_variant.price_type
                                /* fixed */
                                WHEN 0 THEN
                                    /* get sub product price (if special price or not) 
                                    add variant price
                                    and multiply the resulting price by the qty specified */
                                    
                                    (product.price+product_variant.price)*pg_product.qty
                                /* percentage */
                                WHEN 1 THEN
                                    /* get sub product price (if special price or not) 
                                    multiply variant percentage to our product price, round by 2 decimals
                                    add resulting price to our sub product price
                                    and multiply resulting price by the qty specified
                                    */                                
                                
                                    (product.price+ROUND((product.price*product_variant.price)/100,2))*pg_product.qty		
                            END)
                        /* no */
                        ELSE
                           product.price*pg_product.qty
                    END)                	
                WHEN 1 THEN
                    /* check if sub product is a variant */
                    (CASE 
                        /* yes */
                        WHEN product_variant.id IS NOT NULL THEN
                            /* check variant price type */
                            (CASE product_variant.price_type
                                /* fixed */
                                WHEN 0 THEN
                                    /* get sub product price (if special price or not) 
                                    add variant price
                                    and multiply the resulting price by the qty specified */
                                    
                                    (product.sell_price+product_variant.price)*pg_product.qty
                                /* percentage */
                                WHEN 1 THEN
                                    /* get sub product price (if special price or not) 
                                    multiply variant percentage to our product price, round by 2 decimals
                                    add resulting price to our sub product price
                                    and multiply resulting price by the qty specified
                                    */                                
                                
                                    (product.sell_price+ROUND((product.sell_price*product_variant.price)/100,2))*pg_product.qty		
                            END)
                        /* no */
                        ELSE
                           product.price*pg_product.qty
                    END)                 
			END)         
    END) 
    FROM 
    cart_item_product
    INNER JOIN 
    (product_bundled_product_group_product AS pg_product CROSS JOIN product)
    ON
    (cart_item_product.id_product_bundled_product_group_product = pg_product.id AND pg_product.id_product = product.id)
    LEFT JOIN 
    product_variant
    ON
    (pg_product.id_product_variant = product_variant.id)
    WHERE 
    cart_item_product.id = id_cart_item_product),0));	

END IF;

SET product_price = product_price-((product_price*customer_type_percent_discount)/100);

RETURN product_price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_max_qty_allowed`(`id_product` INT, `qty` TINYINT(1), `price` DECIMAL(13,2), `id_cart` INT) RETURNS tinyint(1)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE max_qty TINYINT(1);
DECLARE max_qty_tmp TINYINT(1);

SET current_datetime = NOW();

SET max_qty = IFNULL((SELECT product.max_qty FROM product WHERE product.id = id_product),0);

/* check for rebate applicable to product in cart, if not applicable */
SET max_qty_tmp = IFNULL((SELECT 
	rebate_coupon.max_qty_allowed
    FROM 
    cart_discount 
    INNER JOIN
    cart_discount_item_product
    ON
    (cart_discount.id = cart_discount_item_product.id_cart_discount)
    INNER JOIN
    rebate_coupon
    ON
    (cart_discount.id_rebate_coupon = rebate_coupon.id) 
    WHERE
    cart_discount.id_cart = id_cart
    AND
    (rebate_coupon.type = 0 OR rebate_coupon.type = 2)
    ORDER BY 
    IF(rebate_coupon.max_qty_allowed > 0,rebate_coupon.max_qty_allowed,99999) ASC
    LIMIT 1),IFNULL((SELECT
    MIN(t.max_qty_allowed) AS max_qty_allowed 
    FROM 
    ((SELECT 
        rebate_coupon.max_qty_allowed
        FROM 
        rebate_coupon
        LEFT JOIN
        rebate_coupon_product
        ON
        (rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = id_product)
        
        LEFT JOIN
        (rebate_coupon_category CROSS JOIN product_category)
        ON
        (rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND rebate_coupon_category.id_category = product_category.id_category AND product_category.id_product = id_product) 
        
        WHERE
        rebate_coupon.active = 1
        AND
        (
            rebate_coupon.end_date = "0000-00-00 00:00:00"
            OR
            current_datetime BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
        )
        AND
        rebate_coupon.coupon = 0
        AND
        rebate_coupon.type = 0 
        AND 
        rebate_coupon.min_qty_required <= qty
        AND
        rebate_coupon.max_qty_allowed > 0 
        AND
        (rebate_coupon_product.id_rebate_coupon IS NOT NULL OR rebate_coupon_category.id_rebate_coupon IS NOT NULL)
        ORDER BY (CASE rebate_coupon.discount_type
            WHEN 0 THEN rebate_coupon.discount/price
            WHEN 1 THEN rebate_coupon.discount/100
        END) DESC
        LIMIT 1)
        
    UNION
    
    (SELECT 
        rebate_coupon.max_qty_allowed
        FROM 
        rebate_coupon
        LEFT JOIN
        rebate_coupon_product
        ON
        (rebate_coupon.id = rebate_coupon_product.id_rebate_coupon AND rebate_coupon_product.id_product = id_product)
        
        LEFT JOIN
        (rebate_coupon_category CROSS JOIN product_category)
        ON
        (rebate_coupon.id = rebate_coupon_category.id_rebate_coupon AND rebate_coupon_category.id_category = product_category.id_category AND product_category.id_product = id_product) 
        
        WHERE
        rebate_coupon.active = 1
        AND
        (
            rebate_coupon.end_date = "0000-00-00 00:00:00"
            OR
            current_datetime BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
        )
        AND
        rebate_coupon.coupon = 0
        AND
        rebate_coupon.type = 2 
        AND 
        rebate_coupon.buy_x_qty <= qty
        AND
        rebate_coupon.max_qty_allowed > 0 
        AND
        (rebate_coupon_product.id_rebate_coupon IS NOT NULL OR rebate_coupon_category.id_rebate_coupon IS NOT NULL)
        ORDER BY (CASE rebate_coupon.discount_type
            WHEN 0 THEN rebate_coupon.discount/price
            WHEN 1 THEN rebate_coupon.discount/100
        END) DESC
        LIMIT 1)) AS t),0));

SET max_qty = IF((max_qty > 0 AND max_qty_tmp > 0 AND max_qty_tmp < max_qty) OR max_qty = 0,max_qty_tmp,max_qty);

RETURN max_qty;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_option_cart_price`(`id_cart_item_option` INT) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_price DECIMAL(13,2);
DECLARE option_price_type INT;
DECLARE option_price DECIMAL(13,2);

SET current_datetime = NOW();

(SELECT
get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1) AS product_sell_price,
options.price_type,
options.price
INTO 
product_price,
option_price_type,
option_price
FROM
cart_item
INNER JOIN
(cart_item_option CROSS JOIN options)
ON
(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_options = options.id)
INNER JOIN
(cart_item_product CROSS JOIN product)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
LEFT JOIN
product_variant
ON
(cart_item_product.id_product_variant = product_variant.id)
WHERE
cart_item_option.id = id_cart_item_option);

RETURN (CASE option_price_type
	WHEN 0 THEN
    	option_price
    WHEN 1 THEN
    	ROUND((product_price*option_price)/100)
END);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_option_cart_sell_price`(`id_cart_item_option` INT) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_price DECIMAL(13,2);
DECLARE option_price_type INT;
DECLARE option_price DECIMAL(13,2);

SET current_datetime = NOW();

(SELECT
get_product_cart_price(product.sell_price, product_variant.price_type, product_variant.price, product.id, product.product_type, cart_item.id_cart, 1) AS product_sell_price,
options.price_type,
IF(current_datetime BETWEEN options.special_price_from_date AND options.special_price_to_date,options.special_price,options.price) AS sell_price
INTO 
product_price,
option_price_type,
option_price
FROM
cart_item
INNER JOIN
(cart_item_option CROSS JOIN options)
ON
(cart_item.id = cart_item_option.id_cart_item AND cart_item_option.id_options = options.id)
INNER JOIN
(cart_item_product CROSS JOIN product)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id_product = product.id)
LEFT JOIN
product_variant
ON
(cart_item_product.id_product_variant = product_variant.id)
WHERE
cart_item_option.id = id_cart_item_option);

RETURN (CASE option_price_type
	WHEN 0 THEN
    	option_price
    WHEN 1 THEN
    	ROUND((product_price*option_price)/100)
END);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_cart_price`(`price` DECIMAL(13,2), `price_type` TINYINT(1), `variant_price` DECIMAL(13,2), `id_product` INT, `product_type` TINYINT(1), `id_cart` INT, `include_rebates` TINYINT(1)) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_qty INT;
DECLARE product_tier_price DECIMAL(13,2);
DECLARE customer_type_percent_discount TINYINT;
DECLARE customer_type_apply_on_rebate TINYINT;
DECLARE rebate_discount DECIMAL(16,13);
DECLARE id_customer_type INT;

SET current_datetime = NOW();

(SELECT
IF(customer_type.id IS NOT NULL,customer_type.percent_discount,0) AS percent_discount,
IF(customer_type.id IS NOT NULL,customer_type.apply_on_rebate,1) AS apply_on_rebate,
cart.id_customer_type
INTO
customer_type_percent_discount,
customer_type_apply_on_rebate,
id_customer_type
FROM 
cart
LEFT JOIN
customer_type
ON
(cart.id_customer_type = customer_type.id)
WHERE
cart.id = id_cart);

IF product_type = 0 THEN
    SET product_qty = IFNULL((SELECT
    SUM(cart_item_product.qty)
    FROM
    cart_item
    INNER JOIN
    cart_item_product
    ON
    (cart_item.id = cart_item_product.id_cart_item)
    WHERE
    cart_item.id_cart = id_cart
    AND
    cart_item.id_cart_discount = 0
    AND
    cart_item_product.id_product = id_product),0);
    
    SET product_tier_price = IFNULL((SELECT 
    product_price_tier.price
    FROM
    product_price_tier
    WHERE
    product_price_tier.id_product = id_product
    AND
    (product_price_tier.id_customer_type = 0 OR product_price_tier.id_customer_type = id_customer_type)
    AND
    product_price_tier.qty <= product_qty
    ORDER BY 
    product_price_tier.price ASC
    LIMIT 1),0);    
    
    SET price = IF(product_tier_price > 0 AND product_tier_price < price,product_tier_price,price);
    
    IF variant_price > 0 THEN
        SET price = (CASE price_type         
            WHEN 0 THEN price+variant_price
            WHEN 1 THEN price+ROUND((price*variant_price)/100,2)
        END);    
	END IF;
END IF;    

IF customer_type_percent_discount > 0 THEN
	SET price = price-ROUND((price*customer_type_percent_discount)/100,2);
END IF;

IF product_type = 0 THEN
    IF include_rebates = 1 AND customer_type_apply_on_rebate = 1 THEN
    
        SET rebate_discount = IFNULL((SELECT 
        (CASE rebate_coupon.discount_type 
            WHEN 0 THEN
                (rebate_coupon.discount/price)
            WHEN 1 THEN
                (rebate_coupon.discount/100)
        END) AS discount
        FROM 
        rebate_coupon 
        LEFT JOIN
        cart_discount 
        ON
        (rebate_coupon.id = cart_discount.id_rebate_coupon)
        
        WHERE
        rebate_coupon.active = 1
        AND 
        (
        	rebate_coupon.end_date = "0000-00-00 00:00:00"
            OR
        	current_datetime BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
        )
        AND
        rebate_coupon.coupon = 0
        AND
        rebate_coupon.type = 0
        AND
        (
            (IF((SELECT 
            rebate_coupon_product.id_rebate_coupon
            FROM
            rebate_coupon_product 
            WHERE 
            rebate_coupon_product.id_product = id_product
            LIMIT 1) IS NOT NULL,1,0)) = 1                
			
        	OR         
			
            (IF((SELECT 
            rebate_coupon_category.id_rebate_coupon
            FROM
            rebate_coupon_category INNER JOIN product_category
            ON (rebate_coupon_category.id_category = product_category.id_category)
            WHERE 
            rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
            AND
            product_category.id_product = id_product
            LIMIT 1) IS NOT NULL,1,0)) = 1		
		)
        AND
        get_product_qty_applicable_discount(id_product,id_cart,rebate_coupon.id) >= rebate_coupon.min_qty_required
        ORDER BY 
        (CASE 
            WHEN cart_discount.id IS NOT NULL THEN 0
            ELSE 1
        END) ASC,
        (CASE rebate_coupon.discount_type 
            WHEN 0 THEN
                (rebate_coupon.discount/price)
            WHEN 1 THEN
                (rebate_coupon.discount/100)
        END) DESC
        LIMIT 1),0);
        
        IF rebate_discount > 0 THEN       
	        SET price = price-IF(rebate_discount > 0,ROUND(price*rebate_discount,2),0);
        END IF;
        
        SET rebate_discount = IFNULL((SELECT 
        (CASE rebate_coupon.discount_type 
            WHEN 0 THEN
                (rebate_coupon.discount/price)
            WHEN 1 THEN
                (rebate_coupon.discount/100)
        END) AS discount
        FROM 
        cart_discount 
        INNER JOIN
        rebate_coupon
        ON
        (cart_discount.id_rebate_coupon = rebate_coupon.id)
        
        WHERE
        rebate_coupon.active = 1
        AND 
        (
        	rebate_coupon.end_date = "0000-00-00 00:00:00"
            OR
        	current_datetime BETWEEN rebate_coupon.start_date AND rebate_coupon.end_date
        )
        AND
        rebate_coupon.coupon = 1
        AND
        rebate_coupon.type = 0
        AND
        (
            (IF((SELECT 
            rebate_coupon_product.id_rebate_coupon
            FROM
            rebate_coupon_product 
            WHERE 
            rebate_coupon_product.id_product = id_product
            LIMIT 1) IS NOT NULL,1,0)) = 1                
			
        	OR         
			
            (IF((SELECT 
            rebate_coupon_category.id_rebate_coupon
            FROM
            rebate_coupon_category INNER JOIN product_category
            ON (rebate_coupon_category.id_category = product_category.id_category)
            WHERE 
            rebate_coupon_category.id_rebate_coupon = rebate_coupon.id
            AND
            product_category.id_product = id_product
            LIMIT 1) IS NOT NULL,1,0)) = 1			
		)
        AND
        get_product_qty_applicable_discount(id_product,id_cart,rebate_coupon.id) >= rebate_coupon.min_qty_required
        ORDER BY 
        (CASE rebate_coupon.discount_type 
            WHEN 0 THEN
                (rebate_coupon.discount/price)
            WHEN 1 THEN
                (rebate_coupon.discount/100)
        END) DESC
        LIMIT 1),0);
        
        IF rebate_discount > 0 THEN
	        SET price = price-IF(rebate_discount > 0,ROUND(price*rebate_discount,2),0);
		END IF;
    
    END IF;
END IF;    

RETURN price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_cost_price`(`id_product` INT, `id_product_variant` INT) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
SET current_datetime = NOW();
RETURN IFNULL((SELECT 
(CASE product.product_type
    WHEN 0 THEN (CASE 
        WHEN product_variant.id IS NOT NULL THEN (product.cost_price+product_variant.price)
        ELSE 
            product.cost_price		
		END)
    WHEN 1 THEN (SELECT 
        SUM(CASE 
            WHEN product_combo_variant.id IS NOT NULL THEN 
				(t.cost_price+t_variant.price)*product_combo.qty
            ELSE
                t.cost_price*product_combo.qty
        END)
        FROM 
        product AS t
        INNER JOIN 
        product_combo 
        ON 
        (t.id = product_combo.id_combo_product)
        LEFT JOIN 
        (product_combo_variant CROSS JOIN product_variant AS t_variant)
        ON
        (product_combo.id = product_combo_variant.id_product_combo AND product_combo_variant.id_product_variant = t_variant.id AND product_combo_variant.default_variant = 1)
        WHERE
        product_combo.id_product = product.id)
    WHEN 2 THEN (SELECT 
        /* get the sum of all sub products (selected) in required groups */
        SUM(
        /* check if sub product is a variant */
        (CASE 
            /* yes */
            WHEN t_variant.id IS NOT NULL THEN
            	(t.cost_price+t_variant.price)*pg_product.qty
            /* no */
            ELSE
                t.cost_price*pg_product.qty
        END))
        FROM 
        product_bundled_product_group_product AS pg_product
        INNER JOIN 
        product AS t
        ON
        (pg_product.id_product = t.id)
        LEFT JOIN 
        product_variant AS t_variant
        ON
        (pg_product.id_product_variant = t_variant.id)
        INNER JOIN 
        product_bundled_product_group AS pg
        ON 
        (pg_product.id_product_bundled_product_group = pg.id)
        INNER JOIN
        product AS parent_product
        ON
        (pg.id_product = parent_product.id)
        WHERE 
        parent_product.id = product.id
        AND 
        pg_product.selected = 1)					
END) 
FROM
product 
LEFT JOIN 
product_variant
ON
(product.id = product_variant.id_product AND product_variant.id = id_product_variant)
WHERE
product.id = id_product
LIMIT 1),0);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_current_price`(`id_product` INT, `id_product_variant` INT, `id_customer_type` INT) RETURNS decimal(13,2)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE product_type INT;
DECLARE product_discount INT;
DECLARE product_discount_type INT;
DECLARE product_price DECIMAL(13,2);
DECLARE customer_type_percent_discount DECIMAL(13,2);

SET current_datetime = NOW();

SET customer_type_percent_discount = IFNULL((SELECT percent_discount FROM customer_type WHERE id = id_customer_type),0);

SELECT 
product.product_type,
product.discount,
product.discount_type
INTO
product_type,
product_discount,
product_discount_type
FROM
product 
LEFT JOIN 
product_variant
ON
(product.id = product_variant.id_product AND product_variant.id = id_product_variant)
WHERE
product.id = id_product
LIMIT 1;

SET product_price = IFNULL((SELECT 
(CASE product.product_type
    WHEN 0
        THEN (CASE 
        	WHEN product_variant.id IS NOT NULL
            	THEN
                /* check variant price type */
                (CASE product_variant.price_type
                    /* fixed */
                    WHEN 0
                        THEN
                            /* get product price (if special price or not) 
                            add variant price
							*/                            
                            (IF(current_datetime BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)+product_variant.price)
                    /* percentage */
                    WHEN 1																	
                        THEN
                            /* get product price (if special price or not) 
                            multiply variant percentage to our product price, round by 2 decimals
                            add resulting price to our sub product price
                            */                        
                           (IF(current_datetime BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)+ROUND((IF(current_datetime BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)*product_variant.price)/100,2))
                END)            	
            ELSE 
            	IF(current_datetime BETWEEN product.special_price_from_date AND product.special_price_to_date,product.special_price,product.price)		
		END)
    WHEN 1
        THEN (SELECT 
        SUM(CASE 
            WHEN product_combo_variant.id IS NOT NULL
                /* check variant price type */
                THEN (CASE t_variant.price_type
                    /* fixed */
                    WHEN 0
                        THEN
                            /* get sub product price (no special price) 
                            add variant price
                            and multiply the resulting price by the qty specified */
                            (t.price+t_variant.price)*product_combo.qty
                    /* percentage */
                    WHEN 1																	
                        THEN
                            /* get sub product price (no special price) 
                            multiply variant percentage to our product price, round by 2 decimals
                            add resulting price to our sub product price
                            and multiply resulting price by the qty specified
                            */
                            (t.price+ROUND((t.price*t_variant.price)/100,2))*product_combo.qty
                END)
            ELSE
                t.price*product_combo.qty
        END)
        FROM 
        product AS t
        INNER JOIN 
        product_combo 
        ON 
        (t.id = product_combo.id_combo_product)
        LEFT JOIN 
        (product_combo_variant CROSS JOIN product_variant AS t_variant)
        ON
        (product_combo.id = product_combo_variant.id_product_combo AND product_combo_variant.id_product_variant = t_variant.id AND product_combo_variant.default_variant)
        WHERE
        product_combo.id_product = product.id)
    WHEN 2
        THEN (SELECT 
        /* get the sum of all sub products (selected) in required groups */
        SUM(
        /* check if we use sub product regular price or the price we set */
        CASE parent_product.use_product_current_price
            /* no */
            WHEN 0 
                THEN 
                    /* check price type we specified */
                    (CASE pg_product.price_type
                        /* fixed */
                        WHEN 0
                            THEN
                                /* add fixed price */
                                pg_product.price*pg_product.qty
                        /* percentage */
                        WHEN 1
                            THEN
                                /* check if sub product is a variant */
                                (CASE 
                                    /* yes */
                                    WHEN t_variant.id IS NOT NULL
                                        THEN
                                            /* check variant price type */
                                            (CASE t_variant.price_type
                                                /* fixed */
                                                WHEN 0
                                                    THEN
                                                        /* get sub product price (if special price or not) 
                                                        add variant price
                                                        and multiply the resulting price by our product bundle percent, round to 2 decimals
                                                        and multiply the resulting price by the qty specified */
                                                        
                                                        ROUND(((IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)+t_variant.price)*pg_product.price/100),2)*pg_product.qty
                                                /* percentage */
                                                WHEN 1																	
                                                    THEN
                                                        /* get sub product price (if special price or not) 
                                                        multiply variant percentage to our product price, round by 2 decimals
                                                        add resulting price to our sub product price
                                                        and multiply resulting price by product bundle percent, round by 2 decimals
                                                        and multiply resulting price by the qty specified
                                                        */
                                                    
                                                        ROUND(((IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)+ROUND(IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)*t_variant.price,2))*pg_product.price)/100,2)*pg_product.qty
                                            END)
                                    /* no */
                                    ELSE
                                        /* get sub product price (if special price or not)
                                        and multiply by product bundle percent, round by 2 decimals
                                        and multiply resulting price by qty specified */
                                        ROUND((IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)*pg_product.price)/100,2)*pg_product.qty
                                END)
                    END)
            /* yes */
            WHEN 1
                THEN
                    /* check if sub product is a variant */
                    (CASE 
                        /* yes */
                        WHEN t_variant.id IS NOT NULL
                            THEN
                                /* check variant price type */
                                (CASE t_variant.price_type
                                    /* fixed */
                                    WHEN 0 THEN
                                            /* get sub product price (if special price or not) 
                                            add variant price
                                            and multiply the resulting price by the qty specified */
                                        
                                        	/* check if we use special price */
                                            (CASE parent_product.use_product_special_price 
                                            	WHEN 0 THEN
                                                	(t.price+t_variant.price)*pg_product.qty
                                                WHEN 1 THEN
                                                	(IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)+t_variant.price)*pg_product.qty
                                            END)
                                    /* percentage */
                                    WHEN 1 THEN
                                            /* get sub product price (if special price or not) 
                                            multiply variant percentage to our product price, round by 2 decimals
                                            add resulting price to our sub product price
                                            and multiply resulting price by the qty specified
                                            */                                
                                        
                                        	/* check if we use special price */
                                            (CASE parent_product.use_product_special_price 
                                            	WHEN 0 THEN
                                                	(t.price+ROUND((t.price*t_variant.price)/100,2))*pg_product.qty
                                                WHEN 1 THEN     
                                                	(IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)+ROUND((IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)*t_variant.price)/100,2))*pg_product.qty	
											END)
                                END)
                        /* no */
                        ELSE
                            /* check if we use special price */
                            (CASE parent_product.use_product_special_price 
                                WHEN 0 THEN
                                    t.price*pg_product.qty
                                WHEN 1 THEN     
                                    IF(current_datetime BETWEEN t.special_price_from_date AND t.special_price_to_date,t.special_price,t.price)*pg_product.qty
                            END)                                                    
                    END)
        END) 
        FROM 
        product_bundled_product_group_product AS pg_product
        INNER JOIN 
        product AS t
        ON
        (pg_product.id_product = t.id)
        LEFT JOIN 
        product_variant AS t_variant
        ON
        (pg_product.id_product_variant = t_variant.id)
        INNER JOIN 
        product_bundled_product_group AS pg
        ON 
        (pg_product.id_product_bundled_product_group = pg.id)
        INNER JOIN
        product AS parent_product
        ON
        (pg.id_product = parent_product.id)
        WHERE 
        parent_product.id = product.id
        AND 
        pg_product.selected = 1)					
END) 
FROM
product 
LEFT JOIN 
product_variant
ON
(product.id = product_variant.id_product AND product_variant.id = id_product_variant)
WHERE
product.id = id_product
LIMIT 1),0);

SET product_price = (CASE product_type
WHEN 0 THEN product_price
WHEN 1 THEN (CASE product_discount_type
	WHEN 0
    	THEN IF(product_price < product_discount,0,product_price-product_discount)
	WHEN 1
    	THEN (product_price-ROUND((product_price*product_discount)/100,2))
END)
WHEN 2 THEN product_price
END);

SET product_price = product_price-ROUND((product_price*customer_type_percent_discount)/100,2);

RETURN product_price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_discounted_price`(`id_cart_item` INT, `id_rebate_coupon` INT) RETURNS decimal(13,2)
BEGIN

DECLARE rebate_type INT;
DECLARE rebate_type_coupon INT;
DECLARE product_price DECIMAL(13,2);

/* Check current discount we are applying for the type and if it is a coupon */
(SELECT 
type,
coupon
INTO 
rebate_type,
rebate_type_coupon
FROM 
rebate_coupon
WHERE
id = id_rebate_coupon);

/* Get current product sell price in cart */
SET product_price = IFNULL((SELECT 
cart_item_product.sell_price
FROM
cart_item
INNER JOIN
cart_item_product
ON
(cart_item.id = cart_item_product.id_cart_item)
WHERE
cart_item.id = id_cart_item),0);

/* If current discount is a product discount coupon, check if we have a product discount rebate applied, if yes remove it from sell price */
SET product_price = product_price-IF(rebate_type = 0 AND rebate_type_coupon = 1,IFNULL((SELECT 
(cart_discount_item_product.amount/cart_item_product.qty)
FROM 
cart_item 
INNER JOIN 
(cart_item_product CROSS JOIN cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
WHERE
cart_item.id = id_cart_item
AND
rebate_coupon.type = 0 
AND 
rebate_coupon.coupon = 0),0),0);

/* If current discount is a buy and get rebate or coupon, check if we have a product discount rebate applied, if yes remove it from sell price */
SET product_price = product_price-IF(rebate_type = 2,IFNULL((SELECT 
SUM(cart_discount_item_product.amount/cart_item_product.qty)
FROM 
cart_item 
INNER JOIN 
(cart_item_product CROSS JOIN cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
WHERE
cart_item.id = id_cart_item
AND
rebate_coupon.type = 0),0),0);

/* If current discount is a cart discount, check if we have discounts applied to this product, if yes remove it from sell price */
SET product_price = product_price-IF((rebate_type = 1 OR rebate_type = 5) AND rebate_type_coupon = 0,IFNULL((SELECT 
SUM(cart_discount_item_product.amount/cart_item_product.qty)
FROM 
cart_item 
INNER JOIN 
(cart_item_product CROSS JOIN cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
WHERE
cart_item.id = id_cart_item
AND 
rebate_coupon.type = 0),0),0);

/* If current discount is a cart discount coupon, check if we have a cart discount first purchase or cart rebate applied to this product, if yes remove it from sell price */
SET product_price = product_price-IF(rebate_type = 1 AND rebate_type_coupon = 1,IFNULL((SELECT 
SUM(cart_discount_item_product.amount/cart_item_product.qty)
FROM 
cart_item 
INNER JOIN 
(cart_item_product CROSS JOIN cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon)
ON
(cart_item.id = cart_item_product.id_cart_item AND cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount_item_product.id_cart_discount = cart_discount.id AND cart_discount.id_rebate_coupon = rebate_coupon.id)
WHERE
cart_item.id = id_cart_item
AND 
(rebate_coupon.type = 1 AND rebate_coupon.coupon = 1) IS NOT TRUE),0),0);

RETURN product_price;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_image_variant_name`(`id_product_image_variant` INT, `language_code` VARCHAR(2)) RETURNS text CHARSET utf8 COLLATE utf8_unicode_ci
BEGIN

RETURN IFNULL((SELECT			
GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ")
FROM 
product_image_variant_option 
INNER JOIN 
(product_variant_group 
CROSS JOIN product_variant_group_option
CROSS JOIN product_variant_group_option_description
CROSS JOIN product_variant_group_description)
ON 
(product_image_variant_option.id_product_variant_group = product_variant_group.id 
AND product_image_variant_option.id_product_variant_group_option = product_variant_group_option.id 
AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option 
AND product_variant_group_option_description.language_code = language_code
AND product_image_variant_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
AND product_variant_group_description.language_code = language_code)
WHERE
product_image_variant_option.id_product_image_variant = id_product_image_variant),"");
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `get_product_qty_applicable_discount`(`id_product` INT, `id_cart` INT, `id_rebate_coupon` INT) RETURNS int(11)
BEGIN
DECLARE current_datetime DATETIME;

SET current_datetime = NOW();

RETURN IFNULL((SELECT 
SUM(cart_item.qty)
FROM 
cart_item 
INNER JOIN 
cart_item_product
ON
(cart_item.id = cart_item_product.id_cart_item) 

LEFT JOIN
rebate_coupon AS rc
ON
(rc.id = id_rebate_coupon)

LEFT JOIN
(cart_discount_item_product CROSS JOIN cart_discount CROSS JOIN rebate_coupon AS rc_other)
ON
(cart_item_product.id = cart_discount_item_product.id_cart_item_product AND cart_discount.id = cart_discount_item_product.id_cart_discount AND cart_discount.id_rebate_coupon = rc_other.id AND rc.type = rc_other.type AND rc_other.coupon = 0 AND rc.id != rc_other.id)
                    
WHERE
cart_item.id_cart = id_cart
AND
cart_item.id_cart_discount = 0
AND
cart_item_product.id_product = id_product
AND
(
    (IF((SELECT 
    rebate_coupon_product.id_rebate_coupon
    FROM
    rebate_coupon_product 
    WHERE 
    rebate_coupon_product.id_product = id_product
    LIMIT 1) IS NOT NULL,1,0)) = 1        


    OR 

    (IF((SELECT 
    rebate_coupon_category.id_rebate_coupon
    FROM
    rebate_coupon_category INNER JOIN product_category
    ON (rebate_coupon_category.id_category = product_category.id_category)
    WHERE 
    rebate_coupon_category.id_rebate_coupon = rc.id
    AND
    product_category.id_product = id_product
    LIMIT 1) IS NOT NULL,1,0)) = 1
)
AND
cart_discount_item_product.id IS NULL),0);

END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `has_been_sold`(`id_product` INT, `id_product_variant` INT, `variant_code` CHAR) RETURNS tinyint(1)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE has_been_sold TINYINT(1);
SET current_datetime = NOW();

IF variant_code IS NOT NULL AND variant_code != "" THEN
	SET has_been_sold = (SELECT 
    COUNT(product_variant.id)
    FROM 
    product_variant
    
    LEFT JOIN 
    (orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
    ON
    (product_variant.id = orders_item_product.id_product_variant AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
    
    LEFT JOIN
    (orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
    ON
    (product_variant.id = oip.id_product_variant AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
    
    WHERE
    product_variant.variant_code LIKE CONCAT(variant_code,"%")
    AND
    (
        orders.id IS NOT NULL AND orders.status NOT IN (-1,0)
        OR
        o.id IS NOT NULL AND o.status NOT IN (-1,0)
    )
    LIMIT 1);
ELSE    
	SET has_been_sold = (SELECT
    COUNT(orders.id)
    FROM 
    orders
    INNER JOIN
    (orders_item CROSS JOIN orders_item_product)
    ON
    (orders.id = orders_item.id_orders AND orders_item.id = orders_item_product.id_orders_item)
    WHERE
    orders.status NOT IN (-1,0)
    AND
    orders_item_product.id_product = id_product
    AND
    orders_item_product.id_product_variant = IF(id_product_variant IS NOT NULL AND id_product_variant != 0,id_product_variant,0)
    LIMIT 1);
END IF;

RETURN has_been_sold;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `is_option_in_stock`(`id_options` INT) RETURNS int(11)
BEGIN
DECLARE current_datetime DATETIME;
SET current_datetime = NOW();

RETURN IF((SELECT
    options.id
    FROM 
	options
    WHERE
    options.id = id_options
    AND
    options.active = 1
    AND
    (
        (
            options.track_inventory = 1
            AND
            options.in_stock = 1
			AND
			(options.qty-options.out_of_stock-IFNULL((SELECT
			SUM(cart_item.qty*cart_item_option.qty)
			FROM
			cart_item_option
			INNER JOIN
			(cart_item CROSS JOIN cart)
			ON
			(cart_item_option.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
			WHERE
			cart_item_option.id_options = id_options
			AND
			cart.date_expired > current_datetime),0)) >= 1
        )
        OR
        options.track_inventory = 0
    )				
    LIMIT 1)>0,1,0);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `is_product_in_stock`(`id_product` INT, `id_product_variant` INT, `qty` SMALLINT) RETURNS int(11)
BEGIN
DECLARE current_datetime DATETIME;
SET current_datetime = NOW();
SET qty = IF(qty IS NULL OR qty <= 0,1,qty);

RETURN IF((SELECT 
	/* check product type */
    (CASE product.product_type
		/* single */
        WHEN 0 THEN (CASE 
				/* yes */
                WHEN product_variant.id IS NOT NULL
					/* count rows returned */
                    THEN (SELECT
                    COUNT(t.id)
                    FROM 
                    product AS t
                    INNER JOIN 
                    product_variant AS t_variant
                    ON
                    (t.id = t_variant.id_product)
                    WHERE					
                    t.id = product.id
                    AND
					/* product is active */
                    t.active = 1
                    AND
                    t_variant.id = product_variant.id
                    AND
					/* variant is active */
                    t_variant.active = 1
                    AND
                    (
                    	/* if inventory tracking is on */
                        (
                        	/* yes, proceed */
                            t.track_inventory = 1
                            AND
                            (
                                /* in stock */
                                t.in_stock = 1
                                AND
                                /* variant in stock */
                                t_variant.in_stock = 1
								AND
								(t_variant.qty-t.out_of_stock-IFNULL((SELECT
								SUM(cart_item_product.qty*IF(cart.id IS NOT NULL,1,ci.qty))
								FROM
								cart_item_product 
								
								LEFT JOIN
								(cart_item CROSS JOIN cart)
								ON
								(cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
								
								LEFT JOIN
								(cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c) 
								ON
								(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
								
								WHERE
								cart_item_product.id_product = t.id
								AND
								cart_item_product.id_product_variant = t_variant.id
								AND
								(
									cart.id IS NOT NULL AND cart.date_expired > current_datetime
									OR
									c.id IS NOT NULL AND c.date_expired > current_datetime
								)),0)) >= qty
                            )
                        )
                        /* if not skip */
                        OR
                        (
                        	t.track_inventory = 0
                        	AND
                            (
                                /* in stock */
                                t.in_stock = 1
                                AND
                                /* variant in stock */
                                t_variant.in_stock = 1    
							)
                        )
                    )
					LIMIT 1)
                /* no variant, count rows returned */
                ELSE (SELECT
                COUNT(t.id)
                FROM 
                product AS t
                WHERE
                t.id = product.id
                AND
                /* product is active */
                t.active = 1
                AND
                (
                	/* if inventory tracking is on */
                    (
                    	/* yes */
                        t.track_inventory = 1
                        AND
                        t.in_stock = 1
						AND
						(t.qty-t.out_of_stock-IFNULL((SELECT
						SUM(cart_item_product.qty*IF(cart.id IS NOT NULL,1,ci.qty))
						FROM
						cart_item_product 
						
						LEFT JOIN
						(cart_item CROSS JOIN cart)
						ON
						(cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
						
						LEFT JOIN
						(cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c) 
						ON
						(cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
						
						WHERE
						cart_item_product.id_product = t.id
						AND
						(
							cart.id IS NOT NULL AND cart.date_expired > current_datetime
							OR
							c.id IS NOT NULL AND c.date_expired > current_datetime
						)),0)) >= qty
                    )
					/* if not skip */
                    OR
                    (
                        t.track_inventory = 0
                        AND
                        /* in stock */
                        t.in_stock = 1
                    )
                )
                LIMIT 1)
            END)
        /* combo deal */
        WHEN 1 THEN 1
		/* bundled product */            
        WHEN 2 THEN 1
    END) 
    FROM
    product 
    LEFT JOIN 
    product_variant
    ON
    (product.id = product_variant.id_product AND product_variant.id = id_product_variant)  
    WHERE
    product.id = id_product
    LIMIT 1)>0,1,0);
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `option_qty_in_stock`(`id_options` INT) RETURNS tinyint(1)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE qty, qty_in_cart TINYINT(1);
SET current_datetime = NOW();

SET qty = IFNULL((SELECT
options.qty-options.out_of_stock
FROM
options
WHERE
options.id = id_options),0);

SET qty_in_cart = IFNULL((SELECT
SUM(cart_item.qty*cart_item_option.qty)
FROM 
cart_item_option
INNER JOIN
(cart_item CROSS JOIN cart)
ON
(cart_item_option.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)

WHERE
cart_item_option.id_options = id_options
AND
cart.date_expired > current_datetime),0);

RETURN qty-qty_in_cart;
END$$

CREATE DEFINER=`devleoha`@`localhost` FUNCTION `qty_in_stock`(`id_product` INT, `id_product_variant` INT) RETURNS tinyint(1)
BEGIN
DECLARE current_datetime DATETIME;
DECLARE qty, qty_in_cart TINYINT(1);
SET current_datetime = NOW();

IF id_product_variant IS NULL OR id_product_variant = 0 THEN

    SET qty = IFNULL((SELECT
    IF(product.track_inventory=1,product.qty-product.out_of_stock,-1)
    FROM
    product
    WHERE
    product.id = id_product),0);
    
    SET qty_in_cart = IFNULL((SELECT
    SUM(cart_item_product.qty*IF(cart.id IS NOT NULL,1,ci.qty))
    FROM 
    cart_item_product
    LEFT JOIN
    (cart_item CROSS JOIN cart)
    ON
    (cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
    
    LEFT JOIN
    (cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c)
    ON
    (cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
    
    WHERE
    cart_item_product.id_product = id_product
    AND
    (
    	(cart.id IS NOT NULL AND cart.date_expired > current_datetime)
        OR
        (c.id IS NOT NULL AND c.date_expired > current_datetime)
	)),0);

ELSEIF id_product_variant IS NOT NULL AND id_product_variant != 0 THEN

    SET qty = IFNULL((SELECT
    IF(product.track_inventory=1,product_variant.qty-product.out_of_stock,-1)
    FROM
    product
    INNER JOIN
    product_variant
    ON
    (product.id = product_variant.id_product)
    WHERE
    product.id = id_product
    AND
    product_variant.id = id_product_variant),0);
    
    SET qty_in_cart = IFNULL((SELECT
    SUM(cart_item_product.qty*IF(cart.id IS NOT NULL,1,ci.qty))
    FROM 
    cart_item_product
    LEFT JOIN
    (cart_item CROSS JOIN cart)
    ON
    (cart_item_product.id_cart_item = cart_item.id AND cart_item.id_cart = cart.id)
    
    LEFT JOIN
    (cart_item_product AS cip CROSS JOIN cart_item AS ci CROSS JOIN cart AS c)
    ON
    (cart_item_product.id_cart_item_product = cip.id AND cip.id_cart_item = ci.id AND ci.id_cart = c.id)
    
    WHERE
    cart_item_product.id_product = id_product
    AND
    cart_item_product.id_product_variant = id_product_variant
    AND
    (
    	(cart.id IS NOT NULL AND cart.date_expired > current_datetime)
        OR
        (c.id IS NOT NULL AND c.date_expired > current_datetime)
	)),0);

END IF;

RETURN IF(qty>=0,qty-qty_in_cart,qty);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `AuthAssignment`
--

CREATE TABLE IF NOT EXISTS `AuthAssignment` (
  `itemname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `userid` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `bizrule` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `AuthAssignment`
--

INSERT INTO `AuthAssignment` (`itemname`, `userid`, `bizrule`, `data`) VALUES
('customers', '99', NULL, NULL),
('catalog_manage_product', '99', NULL, NULL),
('catalog_manage_categories', '99', NULL, NULL),
('catalog', '99', NULL, NULL),
('sales', '99', NULL, NULL),
('settings_manage_customer_types', '99', NULL, NULL),
('settings', '99', NULL, NULL),
('reports', '99', NULL, NULL),
('marketing', '99', NULL, NULL),
('cms', '99', NULL, NULL),
('settings_manage_users', '99', NULL, NULL),
('cms', '113', NULL, NULL),
('marketing', '113', NULL, NULL),
('catalog_manage_product', '113', NULL, NULL),
('customers', '113', NULL, NULL),
('catalog_manage_categories', '113', NULL, NULL),
('catalog', '113', NULL, NULL),
('sales', '113', NULL, NULL),
('reports', '113', NULL, NULL),
('settings', '113', NULL, NULL),
('settings_manage_customer_types', '113', NULL, NULL),
('settings_manage_users', '113', NULL, NULL),
('settings_manage_customer_types', '115', NULL, NULL),
('settings', '115', NULL, NULL),
('reports', '115', NULL, NULL),
('cms', '115', NULL, NULL),
('marketing', '115', NULL, NULL),
('customers', '115', NULL, NULL),
('catalog_manage_product', '115', NULL, NULL),
('catalog_manage_categories', '115', NULL, NULL),
('catalog', '115', NULL, NULL),
('sales', '115', NULL, NULL),
('settings_manage_users', '115', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `AuthItem`
--

CREATE TABLE IF NOT EXISTS `AuthItem` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci COMMENT 'No use for us...Simple Commerce, instead we use the new table that we create : AuthItem_description',
  `bizrule` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  `order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `AuthItem`
--

INSERT INTO `AuthItem` (`name`, `type`, `description`, `bizrule`, `data`, `order`) VALUES
('sales', 0, NULL, NULL, NULL, 1),
('catalog', 0, NULL, NULL, NULL, 2),
('catalog_manage_categories', 0, NULL, NULL, NULL, 0),
('catalog_manage_product', 0, NULL, NULL, NULL, 0),
('customers', 0, NULL, NULL, NULL, 3),
('marketing', 0, NULL, NULL, NULL, 4),
('cms', 0, NULL, NULL, NULL, 5),
('reports', 0, NULL, NULL, NULL, 6),
('settings', 0, NULL, NULL, NULL, 7),
('settings_manage_users', 0, NULL, NULL, NULL, 0),
('settings_manage_customer_types', 0, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `AuthItemChild`
--

CREATE TABLE IF NOT EXISTS `AuthItemChild` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `AuthItemChild`
--

INSERT INTO `AuthItemChild` (`parent`, `child`) VALUES
('catalog', 'catalog_manage_categories'),
('catalog', 'catalog_manage_product'),
('settings', 'settings_manage_customer_types'),
('settings', 'settings_manage_users');

-- --------------------------------------------------------

--
-- Table structure for table `AuthItem_description`
--

CREATE TABLE IF NOT EXISTS `AuthItem_description` (
  `name_AuthItem` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name_permission` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_category` (`name_AuthItem`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `AuthItem_description`
--

INSERT INTO `AuthItem_description` (`name_AuthItem`, `language_code`, `name_permission`, `description`) VALUES
('sales', 'en', 'Sales', ''),
('sales', 'fr', 'Ventes', ''),
('catalog', 'en', 'Catalog', ''),
('catalog', 'fr', 'Catalogue', ''),
('catalog_manage_categories', 'en', 'Manage Categories', ''),
('catalog_manage_categories', 'fr', 'Gérer les catégories', ''),
('catalog_manage_product', 'en', 'Manage Products', ''),
('catalog_manage_product', 'fr', 'Gérer les produits', ''),
('customers', 'en', 'Customers', ''),
('customers', 'fr', 'Clients', ''),
('marketing', 'en', 'Marketing', ''),
('marketing', 'fr', 'Marketing', ''),
('cms', 'en', 'Pages (CMS)', ''),
('cms', 'fr', 'Pages (CMS)', ''),
('reports', 'en', 'Statistics', ''),
('reports', 'fr', 'Statistiques', ''),
('settings', 'en', 'Settings', ''),
('settings', 'fr', 'Paramètres', ''),
('settings_manage_users', 'en', 'Manage Users', ''),
('settings_manage_users', 'fr', 'Gérer les utilisateurs', ''),
('settings_manage_customer_types', 'en', 'Manage Customer Types', ''),
('settings_manage_customer_types', 'fr', 'Gérer les prix clients', '');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE IF NOT EXISTS `banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `display_start_date` datetime NOT NULL,
  `display_end_date` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `display_start_date` (`display_start_date`),
  KEY `display_end_date` (`display_end_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `name`, `display_start_date`, `display_end_date`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(5, 'léo', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 113, 113, '2014-03-03 13:10:56', '2014-03-03 18:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `banner_description`
--

CREATE TABLE IF NOT EXISTS `banner_description` (
  `id_banner` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `url_type` tinyint(1) unsigned NOT NULL COMMENT '0 = no url, 1 = url, 2 = cmspage, 3 = subscription / contest',
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `target_blank` tinyint(1) unsigned NOT NULL,
  `id_cmspage` int(10) unsigned NOT NULL,
  `id_subscription_contest` int(10) unsigned NOT NULL,
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_banner`,`language_code`),
  KEY `id_cmspage` (`id_cmspage`),
  KEY `id_subscription_contest` (`id_subscription_contest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `banner_description`
--

INSERT INTO `banner_description` (`id_banner`, `language_code`, `url_type`, `url`, `target_blank`, `id_cmspage`, `id_subscription_contest`, `filename`) VALUES
(5, 'fr', 1, 'http://www.leoharleydavidson.com', 0, 0, 0, '7f83910c028ff279f4375b2b0f0eacae.jpg'),
(5, 'en', 1, 'http://www.leoharleydavidson.com', 0, 0, 0, '5cd13342697e3eef4195ecb4f43d437a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `id_customer_type` int(10) unsigned NOT NULL,
  `id_tax_rule` int(10) unsigned NOT NULL,
  `billing_id` int(10) unsigned NOT NULL,
  `billing_firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `billing_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `billing_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `billing_telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `billing_fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_id` int(10) unsigned NOT NULL,
  `shipping_firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `subtotal` decimal(13,2) unsigned NOT NULL,
  `local_pickup` tinyint(1) NOT NULL,
  `local_pickup_id` int(10) unsigned NOT NULL,
  `local_pickup_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `free_shipping` tinyint(1) NOT NULL,
  `shipping_gateway_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_service` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping` decimal(13,2) unsigned NOT NULL COMMENT 'Combine Shipping Gateway + Shipping by product',
  `shipping_estimated` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_validated` tinyint(1) unsigned NOT NULL,
  `taxes` decimal(13,2) unsigned NOT NULL,
  `total` decimal(13,2) unsigned NOT NULL,
  `gift_certificates` decimal(13,2) unsigned NOT NULL,
  `grand_total` decimal(13,2) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `date_expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language_code` (`language_code`),
  KEY `id_customer` (`id_customer`),
  KEY `id_customer_type` (`id_customer_type`),
  KEY `id_tax_rule` (`id_tax_rule`),
  KEY `billing_id` (`billing_id`),
  KEY `shipping_id` (`shipping_id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_discount`
--

CREATE TABLE IF NOT EXISTS `cart_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int(10) unsigned NOT NULL,
  `id_rebate_coupon` int(10) unsigned NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart` (`id_cart`),
  KEY `id_rebate_coupon` (`id_rebate_coupon`),
  KEY `id_cart_2` (`id_cart`,`id_rebate_coupon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_discount_item_option`
--

CREATE TABLE IF NOT EXISTS `cart_discount_item_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_discount` int(10) unsigned NOT NULL,
  `id_cart_item_option` int(10) unsigned NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_discount` (`id_cart_discount`),
  KEY `id_cart_item_option` (`id_cart_item_option`),
  KEY `id_cart_discount_2` (`id_cart_discount`,`id_cart_item_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_discount_item_product`
--

CREATE TABLE IF NOT EXISTS `cart_discount_item_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_discount` int(10) unsigned NOT NULL,
  `id_cart_item_product` int(10) unsigned NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_discount` (`id_cart_discount`),
  KEY `id_cart_item_product` (`id_cart_item_product`),
  KEY `id_cart_discount_2` (`id_cart_discount`,`id_cart_item_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_gift_certificate`
--

CREATE TABLE IF NOT EXISTS `cart_gift_certificate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int(10) unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(13,2) NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart` (`id_cart`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_cart_2` (`id_cart`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE IF NOT EXISTS `cart_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int(10) unsigned NOT NULL,
  `id_cart_discount` int(10) unsigned NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart` (`id_cart`),
  KEY `id_cart_discount` (`id_cart_discount`),
  KEY `id_cart_2` (`id_cart`,`id_cart_discount`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item_option`
--

CREATE TABLE IF NOT EXISTS `cart_item_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_item` int(10) unsigned NOT NULL,
  `id_product_options_group` int(10) unsigned NOT NULL,
  `id_options_group` int(10) unsigned NOT NULL,
  `id_options` int(10) unsigned NOT NULL,
  `id_tax_rule_exception` int(10) unsigned NOT NULL,
  `use_shipping_price` tinyint(1) unsigned NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `sell_price` decimal(13,2) unsigned NOT NULL,
  `special_price_start_date` datetime NOT NULL,
  `special_price_end_date` datetime NOT NULL,
  `textfield` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `textarea` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename_tmp` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `datetime_start` datetime NOT NULL,
  `datetime_end` datetime NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `subtotal` decimal(13,2) unsigned NOT NULL,
  `taxes` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_item` (`id_cart_item`),
  KEY `id_product_options_group` (`id_product_options_group`),
  KEY `id_options_group` (`id_options_group`),
  KEY `id_options` (`id_options`),
  KEY `id_tax_rule_exception` (`id_tax_rule_exception`),
  KEY `id_cart_item_2` (`id_cart_item`,`id_options_group`,`id_options`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item_option_tax`
--

CREATE TABLE IF NOT EXISTS `cart_item_option_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_item_option` int(10) unsigned NOT NULL,
  `id_tax_rule_rate` int(10) unsigned NOT NULL,
  `amount` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_item_option` (`id_cart_item_option`),
  KEY `id_tax_rule_rate` (`id_tax_rule_rate`),
  KEY `id_cart_item_option_2` (`id_cart_item_option`,`id_tax_rule_rate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item_product`
--

CREATE TABLE IF NOT EXISTS `cart_item_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_item` int(10) unsigned NOT NULL,
  `id_cart_item_product` int(10) unsigned NOT NULL COMMENT 'For subproduct in Combo or Bundled Product',
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `id_product_combo_product` int(10) unsigned NOT NULL,
  `id_product_bundled_product_group_product` int(10) unsigned NOT NULL,
  `id_product_related` int(10) unsigned NOT NULL,
  `id_tax_rule_exception` int(10) unsigned NOT NULL,
  `use_shipping_price` tinyint(1) NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `sell_price` decimal(26,10) unsigned NOT NULL,
  `special_price_start_date` datetime NOT NULL,
  `special_price_end_date` datetime NOT NULL,
  `subtotal` decimal(26,10) unsigned NOT NULL,
  `taxes` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_item` (`id_cart_item`),
  KEY `id_cart_item_product` (`id_cart_item_product`),
  KEY `id_product` (`id_product`),
  KEY `id_product_variant` (`id_product_variant`),
  KEY `id_product_combo_product` (`id_product_combo_product`),
  KEY `id_product_bundled_product_group_product` (`id_product_bundled_product_group_product`),
  KEY `id_product_related` (`id_product_related`),
  KEY `id_tax_rule_exception` (`id_tax_rule_exception`),
  KEY `id_cart_item_2` (`id_cart_item`,`id_product`,`id_product_variant`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_item_product_tax`
--

CREATE TABLE IF NOT EXISTS `cart_item_product_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart_item_product` int(10) unsigned NOT NULL,
  `id_tax_rule_rate` int(10) unsigned NOT NULL,
  `amount` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart_item_product` (`id_cart_item_product`),
  KEY `id_tax_rule_rate` (`id_tax_rule_rate`),
  KEY `id_cart_item_product_2` (`id_cart_item_product`,`id_tax_rule_rate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_shipping_tax`
--

CREATE TABLE IF NOT EXISTS `cart_shipping_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cart` int(10) unsigned NOT NULL,
  `id_tax_rule_rate` int(10) unsigned NOT NULL,
  `amount` decimal(26,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cart` (`id_cart`),
  KEY `id_tax_rule_rate` (`id_tax_rule_rate`),
  KEY `id_cart_2` (`id_cart`,`id_tax_rule_rate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `featured` tinyint(1) unsigned NOT NULL,
  `display_type` tinyint(1) unsigned NOT NULL,
  `product_sort_by` tinyint(1) unsigned NOT NULL COMMENT '0=Featured Items,1=Best Rating,2=Lowest Price,3=Highest Price,4=Most Reviews,5=Name ASC,6=Name DESC',
  `price_increment` smallint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_parent` (`id_parent`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `active` (`active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `id_parent`, `start_date`, `end_date`, `featured`, `display_type`, `product_sort_by`, `price_increment`, `sort_order`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 6, 0, 1, 1, 113, 113, '2014-03-03 12:12:56', '2014-03-03 17:12:56'),
(2, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 6, 0, 2, 1, 113, 113, '2014-03-19 10:25:15', '2014-03-19 14:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `category_description`
--

CREATE TABLE IF NOT EXISTS `category_description` (
  `id_category` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `meta_description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_category`,`language_code`),
  KEY `name` (`name`),
  KEY `alias` (`alias`),
  KEY `id_category` (`id_category`,`language_code`,`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `category_description`
--

INSERT INTO `category_description` (`id_category`, `language_code`, `name`, `description`, `meta_description`, `meta_keywords`, `alias`) VALUES
(1, 'fr', 'Motos usagées', '<p>Voyez notre vaste inventaire de motos usag&eacute;es...Plusieurs mod&egrave;les 2014 d&eacute;monstrateurs en essai disponible.</p>\r\n\r\n<p>Vous d&eacute;sirez changer de moto? &nbsp;Sachez que nous prenons toutes les marques en &eacute;change.</p>\r\n\r\n<p>Pour tous les go&ucirc;ts et tous les budgets.&nbsp; Visitez notre&nbsp; inventaire en ligne et n&#39;h&eacute;sitez pas &agrave; communiquer avec nous pour toute information suppl&eacute;mentaire et venez voir en magasin la moto de vos r&ecirc;ves...</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'motos usagées à vendre, harley davidson', 'motos usagées à vendre, harley davidson', 'motos-usages'),
(1, 'en', 'Used motos', '<p><span style="font-family: Arial, Helvetica, sans; font-size: 11px; line-height: 14px; text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor</span></p>\r\n\r\n<p><span style="font-family: Arial, Helvetica, sans; font-size: 11px; line-height: 14px; text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor</span></p>\r\n', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla ', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auct', 'used-motos'),
(2, 'fr', 'test', '', 'test', 'test', 'test'),
(2, 'en', 'test', '', 'test', 'test', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `cmspage`
--

CREATE TABLE IF NOT EXISTS `cmspage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned NOT NULL,
  `header_only` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `display` tinyint(1) unsigned NOT NULL,
  `display_menu` tinyint(1) unsigned NOT NULL COMMENT '0 = Both, 1 = Top menu only, 2 = Bottom page only',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `protected` tinyint(1) unsigned NOT NULL COMMENT '1 = Cannot be deleted',
  `home_page` tinyint(1) unsigned NOT NULL COMMENT '1 = this is the home page so it''s different from the other pages',
  `indexing` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `external_link` tinyint(1) unsigned NOT NULL,
  `id_subscription_contest` int(10) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_parent` (`id_parent`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `active` (`active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=34 ;

--
-- Dumping data for table `cmspage`
--

INSERT INTO `cmspage` (`id`, `id_parent`, `header_only`, `active`, `display`, `display_menu`, `sort_order`, `protected`, `home_page`, `indexing`, `external_link`, `id_subscription_contest`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(18, 0, 0, 1, 1, 0, 2, 1, 0, 1, 0, 0, 18, 113, '2011-10-18 09:30:55', '2013-01-28 13:38:17'),
(24, 0, 0, 0, 0, 2, 3, 1, 0, 1, 0, 0, 18, 18, '2011-11-09 12:09:23', '2013-01-28 13:38:17'),
(25, 0, 0, 1, 1, 2, 1, 1, 0, 1, 0, 0, 18, 29, '2011-11-09 12:31:49', '2013-01-28 13:38:17'),
(1, 0, 0, 1, 0, 0, 0, 1, 1, 1, 0, 0, 18, 99, '2012-03-26 09:52:58', '2013-01-28 13:38:17'),
(33, 0, 0, 1, 1, 0, 7, 1, 0, 1, 0, 0, 113, 113, '2014-03-27 14:30:53', '2014-03-27 18:46:10'),
(27, 0, 0, 1, 1, 0, 4, 0, 0, 1, 1, 0, 113, 113, '2014-03-03 10:53:08', '2014-03-03 15:53:08'),
(28, 0, 0, 1, 1, 0, 5, 0, 0, 1, 0, 0, 113, 113, '2014-03-03 10:54:05', '2014-03-03 15:54:05'),
(29, 0, 0, 1, 1, 0, 6, 0, 0, 1, 0, 0, 113, 113, '2014-03-03 10:55:03', '2014-03-03 15:55:03'),
(30, 0, 0, 1, 1, 0, 8, 0, 0, 1, 0, 0, 113, 113, '2014-03-03 13:49:02', '2014-03-03 18:49:02'),
(31, 0, 0, 1, 1, 0, 9, 0, 0, 1, 0, 0, 113, 113, '2014-03-19 10:41:00', '2014-03-19 14:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `cmspage_description`
--

CREATE TABLE IF NOT EXISTS `cmspage_description` (
  `id_cmspage` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `external_link_link` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `external_link_target_blank` tinyint(1) unsigned NOT NULL,
  `meta_description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_cmspage`,`language_code`),
  KEY `alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cmspage_description`
--

INSERT INTO `cmspage_description` (`id_cmspage`, `language_code`, `name`, `description`, `external_link_link`, `external_link_target_blank`, `meta_description`, `meta_keywords`, `alias`) VALUES
(18, 'en', 'Contact Us', '<div>\r\n<div style="float:left;"><strong>L&eacute;o Harley-Davidson</strong><br />\r\n8705, Boul Taschereau<br />\r\nBrossard (Qu&eacute;bec)<br />\r\nJ4Y 1A4</div>\r\n\r\n<div style="float: right;"><iframe frameborder="0" height="350" marginheight="0" marginwidth="0" scrolling="no" src="https://maps.google.ca/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=L%C3%A9o+Harley+Davidson,+Brossard,+QC&amp;aq=0&amp;oq=leo+harley&amp;sll=56,-96&amp;sspn=88.183799,270.527344&amp;ie=UTF8&amp;hq=L%C3%A9o+Harley+Davidson,&amp;hnear=Brossard,+Champlain,+Quebec&amp;t=m&amp;z=14&amp;iwloc=A&amp;cid=9429651033642915310&amp;ll=45.442063,-73.472486&amp;output=embed" width="425"></iframe><br />\r\n<small><a href="https://maps.google.ca/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=L%C3%A9o+Harley+Davidson,+Brossard,+QC&amp;aq=0&amp;oq=leo+harley&amp;sll=56,-96&amp;sspn=88.183799,270.527344&amp;ie=UTF8&amp;hq=L%C3%A9o+Harley+Davidson,&amp;hnear=Brossard,+Champlain,+Quebec&amp;t=m&amp;z=14&amp;iwloc=A&amp;cid=9429651033642915310&amp;ll=45.442063,-73.472486" style="color:#0000FF;text-align:left">View Larger Map</a></small></div>\r\n\r\n<div style="clear:both;">&nbsp;</div>\r\n</div>\r\n', '', 0, 'Contact Us', 'Contact Us', 'contact-us'),
(18, 'fr', 'Nous joindre', '<div>\r\n<div style="float:left;"><strong>L&eacute;o Harley-Davidson</strong><br />\r\n8705, Boul Taschereau<br />\r\nBrossard (Qu&eacute;bec)<br />\r\nJ4Y 1A4</div>\r\n\r\n<div style="float: right;"><iframe frameborder="0" height="350" marginheight="0" marginwidth="0" scrolling="no" src="https://maps.google.ca/maps?f=q&amp;source=s_q&amp;hl=fr&amp;geocode=&amp;q=L%C3%A9o+Harley+Davidson,+Brossard,+QC&amp;aq=0&amp;oq=leo+harley&amp;sll=56,-96&amp;sspn=88.183799,270.527344&amp;ie=UTF8&amp;hq=L%C3%A9o+Harley+Davidson,&amp;hnear=Brossard,+Champlain,+Quebec&amp;t=m&amp;z=14&amp;iwloc=A&amp;cid=9429651033642915310&amp;ll=45.442063,-73.472486&amp;output=embed" width="425"></iframe><br />\r\n<small><a href="https://maps.google.ca/maps?f=q&amp;source=embed&amp;hl=fr&amp;geocode=&amp;q=L%C3%A9o+Harley+Davidson,+Brossard,+QC&amp;aq=0&amp;oq=leo+harley&amp;sll=56,-96&amp;sspn=88.183799,270.527344&amp;ie=UTF8&amp;hq=L%C3%A9o+Harley+Davidson,&amp;hnear=Brossard,+Champlain,+Quebec&amp;t=m&amp;z=14&amp;iwloc=A&amp;cid=9429651033642915310&amp;ll=45.442063,-73.472486" style="color:#0000FF;text-align:left">Agrandir le plan</a></small></div>\r\n\r\n<div style="clear:both;">&nbsp;</div>\r\n</div>\r\n', '', 0, 'Contactez-nous', 'Contactez-nous', 'contactez-nous'),
(24, 'en', 'Terms and Conditions', '<p>\r\n	<em><strong>This model is provided to you as an example.</strong></em></p>\r\n<p>\r\n	<em><strong>Whichever model you choose to use, you must ensure that it corresponds to the reality of your business and your approach.</strong></em></p>\r\n<p>\r\n	<em><strong>You can consult various models from other e-commerce sites and also consult a lawyer specialized in contracts to ensure you are well protected.</strong></em></p>\r\n<p>\r\n	Head Office<br />\r\n	<em>Company name<br />\r\n	22 rue Oster<br />\r\n	H1B 3Y9<br />\r\n	Quebec, QC<br />\r\n	Canada<br />\r\n	+1 (418) 444-2223<br />\r\n	info@nomdelacompagnie.ca</em></p>\r\n<h2>\r\n	Acceptance of the conditions</h2>\r\n<p>\r\n	The present general conditions of sale are applicable to all the orders placed on the internet site &ldquo;www.name.ca&rdquo;. The validation of the ordering of a product suggested on the &ldquo;www. name.ca&rdquo; site implies express acceptance of your share of these general conditions of sale, without that this acceptance require your handwritten signature. The present general conditions of sale govern the contractual relations enter the &ldquo;Company name&rdquo; et his customer, both parts accepting them without reserve. These general conditions of sale will prevail over quite other conditions appearing in quite other document, except preliminary, express and written dispensation. &ldquo;Company name&rdquo; reserves the right to constantly modify the present general conditions of sale.</p>\r\n<h2>\r\n	Order</h2>\r\n<p>\r\n	The orders are validated from the reception of the payment. Each product order implies that the Buyer accepts the present General Terms and Conditions without any restriction and exception. When the Buyer saves his/her order he/she is considered to have read, understood and accepted the present General Terms and Conditions without restriction as well as the prices, volumes and quantities of the products offered of sale and ordered by him/her.</p>\r\n<p>\r\n	Once you have chosen your method of payment, you must pay for your order, which legally finalises the purchase agreement made with &ldquo;www.name.ca&rdquo;. &ldquo;Company name&rdquo; reserves the right to cancel any order made by a customer with whom there exists a legal dispute relating to the payment of a past order.</p>\r\n<h2>\r\n	Price</h2>\r\n<p>\r\n	The product prices are indicated in $CAN except tax and are those valid at the time of the placement of the order by the Buyer. The product prices don&#39;t include shipping and handling. Shipping and handling will be charged in addition to the price of the products ordered.</p>\r\n<p>\r\n	The amount of shipping and handling costs charged lies in the sole decision of &ldquo;Company name&rdquo;.</p>\r\n<p>\r\n	The product prices can be modified by &ldquo;Company name&rdquo; at any time. The Buyer will be informed about such modifications before placing his/her order. The price indicated in the confirmation of order is the definitive price. This price includes the price of products, packaging as well as the transport costs.</p>\r\n<h2>\r\n	Product</h2>\r\n<p>\r\n	The site &ldquo;www.name.ca&rdquo; undertakes to render a true picture of the products presented, however photographs, texts, styles of drawing, technical data sheets are given in title informative and no contractual.</p>\r\n<h2>\r\n	Availability</h2>\r\n<p>\r\n	The command will be executed no later than 15 business days from the day following that on which the consumer places his order. In case of unavailability of the product ordered, including due to our suppliers stock shortage, the consumer will be informed as soon possible and he will be able to cancel the order. The consumer will then have the option of requesting either a refund of amounts paid within 30 days of their payment, or exchange the product.</p>\r\n<h2>\r\n	Delivery</h2>\r\n<p>\r\n	&ldquo;Company name&rdquo; makes sure that every package is carefully prepared and that every work is protected in best to limit any deterioration during the transit. &ldquo;Company name&rdquo; cannot see its involved responsibility in case of delay in delivery. No shipment between Friday afternoon and Sunday evening. Responsibility and ownership of the goods is transferred from &ldquo;www.name.ca&rdquo; site to the customer at the time of support by the carrier. Goods travel at customer&#39;s own risk. The buyer has to check the packing of the merchandise when being delivered, and he/she has to report any damages to the shipper on the delivery sheet as well as to &ldquo;Company name&rdquo; within a week at the latest.</p>\r\n<p>\r\n	The Buyer has the right to retract and return at his expense the product he/she ordered within seven days from the reception date. The products must imperatively be returned to &ldquo;Company name&rdquo; &nbsp;in a perfect condition for reselling, in their original condition, sealed in due form and accompanied by the invoice for this order. Any incomplete, used or damaged product and any product with damaged original package can neither be refunded nor exchanged. If the Buyer uses his right of retraction, he/she can choose between: a cash refund payable within 30 days or a voucher.</p>\r\n<p>\r\n	Please note that the following items cannot be returned:</p>\r\n<ul>\r\n	<li>\r\n		Personalized and custom-made items</li>\r\n	<li>\r\n		Perishable and non-perishable food</li>\r\n</ul>\r\n<p>\r\n	Shipping charges are non-refundable, except if you are returning an item because of an error on our part or if we have determined that the product is defective.</p>\r\n<p>\r\n	On purchases made with a credit card, the same card used to make the purchase will be credited with the refund. Your credit card will be credited shortly after we received and verified the returned merchandise.</p>\r\n<h2>\r\n	Partial non-validity</h2>\r\n<p>\r\n	If one or more stipulations of the present General Terms and Conditions are held invalid or are declared invalid as an application of a law, a prescription order a final competent court decision, all other stipulations stay fully valid.</p>\r\n<h2>\r\n	Disputes</h2>\r\n<p>\r\n	The applicable law for the products sold by the company &ldquo;Company name&rdquo; is the Canadian law. The &ldquo;Company name&rdquo; company cannot be considered as person in charge of the damages of all kinds, so material as immaterial or physical, who could result from a bad functioning or from a misuse of the marketed products. The responsibility of the &ldquo;Company name&rdquo; company will be limited, in any case, to the amount of the order and would not know how to be questioned for simple errors or omissions which would have been able to remain in spite of all the precautions taken in the presentation of products. In case of difficulties in the application of the present contract, the buyer has the possibility, before any action in justice, of looking for an amicable. The complaints or the contestings will always be received with attentive benevolence, the good faith being always presumed at the one who makes the effort to expose his situations. In case of dispute, the customer will address by priority the company to obtain an amicable solution.</p>\r\n<h2>\r\n	Possible taxes</h2>\r\n<p>\r\n	For articles delivered outside Canada, possible taxes and customs duties can be imposed when your parcel reaches destination. Tese customs duties and these possible taxes are at your expense and recover from your responsibility. We are not anxious to verify and to inform you about customs duties and applicable taxes. To know them, we advise you to inquire with the proper authorities of your country.</p>\r\n<p>\r\n	The French version of the present General Terms and Conditions has priority over the English version.</p>\r\n<h2>\r\n	Purchasing by minors</h2>\r\n<p>\r\n	For the protection of our shoppers, it is our policy to not sell to minors. If you are under the legal age of majority and wish to purchase from our online store, please have a parent or legal guardian make the purchase for you.</p>\r\n', '', 0, '', '', 'terms-and-conditions'),
(24, 'fr', 'Conditions', '<p>\r\n	<em><strong>Ce mod&egrave;le vous est offert &agrave; titre d&rsquo;exemple.</strong></em></p>\r\n<p>\r\n	<em><strong>Peu importe le mod&egrave;le que vous choisissez, vous devez vous assurer qu&rsquo;il r&eacute;pond &agrave; la r&eacute;alit&eacute; de votre entreprise et &agrave; votre fa&ccedil;on de faire.&nbsp;</strong></em></p>\r\n<p>\r\n	<em><strong>Vous pouvez consulter diff&eacute;rents mod&egrave;les dans d&rsquo;autres sites de commerce &eacute;lectronique et consulter un avocat sp&eacute;cialis&eacute; dans les contrats pour vous assurer d&rsquo;&ecirc;tre bien prot&eacute;g&eacute;.</strong></em></p>\r\n<p>\r\n	Si&egrave;ge Social</p>\r\n<p>\r\n	<em>Nom de compagnie<br />\r\n	22 rue Oster<br />\r\n	H1B 3Y9<br />\r\n	Quebec, QC<br />\r\n	Canada<br />\r\n	+1 (418) 444-2223<br />\r\n	info@nomdelacompagnie.ca</em></p>\r\n<h2>\r\n	Acceptation des conditions</h2>\r\n<p>\r\n	Les pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente sont applicables &agrave; toutes les commandes pass&eacute;es sur le site internet &laquo;&nbsp;www.nom.ca&nbsp;&raquo;. La validation de la commande d&#39;un produit propos&eacute; sur le site &laquo;&nbsp;www.nom.ca&nbsp;&raquo; implique l&#39;acceptation expresse de votre part des pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente, sans que cette acceptation n&eacute;cessite votre signature manuscrite. Les pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente r&eacute;gissent les relations contractuelles entre la soci&eacute;t&eacute; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; et son client, les deux parties les acceptant sans r&eacute;serve. Ces conditions g&eacute;n&eacute;rales de vente pr&eacute;vaudront sur toutes autres conditions figurant dans tout autre document, sauf d&eacute;rogation pr&eacute;alable, expresse et &eacute;crite. &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; se r&eacute;serve le droit de modifier &agrave; tout moment les pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente.</p>\r\n<h2>\r\n	Commandes</h2>\r\n<p>\r\n	Les commandes sont effectives d&egrave;s la date de r&eacute;ception du r&egrave;glement. Toute commande suppose l&rsquo;adh&eacute;sion sans restriction ni r&eacute;serve aux pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente. A partir du moment o&ugrave; l&rsquo;Acheteur a enregistr&eacute; sa commande il est consid&eacute;r&eacute; comme ayant accept&eacute; en connaissance de cause et sans r&eacute;serve les pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente, les prix, volumes et quantit&eacute;s des produits propos&eacute;s &agrave; la vente et command&eacute;s</p>\r\n<p>\r\n	Une fois votre mode de r&egrave;glement s&eacute;lectionn&eacute;, vous devez proc&eacute;der au paiement de votre commande, qui formalisera de mani&egrave;re ferme et d&eacute;finitive le contrat de vente qui vous lie &agrave; &laquo;&nbsp;www.nom.ca&nbsp;&raquo;. &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; se r&eacute;serve le droit d&#39;annuler toute commande d&#39;un client avec lequel il existerait un litige relatif au paiement d&#39;une commande ant&eacute;rieure.</p>\r\n<h2>\r\n	Prix</h2>\r\n<p>\r\n	Les prix de vente des produits sont indiqu&eacute;s en $CAN hors taxes et sont ceux en vigueur au moment de l&rsquo;enregistrement du bon de commande par l&rsquo;acheteur. Ils ne comprennent pas les frais d&rsquo;exp&eacute;dition factur&eacute;s en suppl&eacute;ment du prix des produits achet&eacute;s.</p>\r\n<p>\r\n	Le montant factur&eacute; des frais d&rsquo;exp&eacute;dition est la seule d&eacute;cision de &laquo;&nbsp;nom de la compagnie&nbsp;&raquo;.</p>\r\n<p>\r\n	Les prix de vente des produits peuvent &ecirc;tre modifi&eacute;s par &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; &agrave; tout moment. Cette modification sera signal&eacute;e &agrave; l&rsquo;Acheteur avant toute commande. Le prix indiqu&eacute; dans la confirmation de commande est le prix d&eacute;finitif.</p>\r\n<h2>\r\n	Produits</h2>\r\n<p>\r\n	Le site &laquo;&nbsp;www.nom.ca&nbsp;&raquo; s&rsquo;engage &agrave; rendre une image fid&egrave;le des produits pr&eacute;sent&eacute;s, cependant les photographies, textes, graphismes, fiches techniques sont &agrave; titre informatif et non contractuels.</p>\r\n<h2>\r\n	Disponibilit&eacute;</h2>\r\n<p>\r\n	La commande sera ex&eacute;cut&eacute;e au plus tard dans un d&eacute;lai de 15 jours ouvrables &agrave; compter du jour suivant celui o&ugrave; le consommateur a pass&eacute; sa commande. En cas d&#39;indisponibilit&eacute; du produit command&eacute;, notamment du fait de nos fournisseurs, le consommateur en sera inform&eacute; au plus t&ocirc;t et aura la possibilit&eacute; d&#39;annuler sa commande. Le consommateur aura alors le choix de demander soit le remboursement des sommes vers&eacute;es dans les 30 jours au plus tard de leur versement, soit l&#39;&eacute;change du produit.</p>\r\n<h2>\r\n	Livraison</h2>\r\n<p>\r\n	&laquo;&nbsp;nom de la compagnie&nbsp;&raquo; s&#39;assure que chaque colis soit soigneusement pr&eacute;par&eacute; et que chaque &oelig;uvre soit prot&eacute;g&eacute;e au mieux afin de limiter toute d&eacute;t&eacute;rioration durant le transport. &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; ne pourra voir sa responsabilit&eacute; engag&eacute;e en cas de retard de livraison. Les produits sont livr&eacute;s &agrave; l&rsquo;adresse indiqu&eacute;e par le client sur le bon de commande. Pas d&#39;exp&eacute;dition du vendredi midi au dimanche soir. La propri&eacute;t&eacute; et la responsabilit&eacute; des marchandises sont transf&eacute;r&eacute;es du site &laquo;&nbsp;adresse web de la compagnie&raquo; au client, au moment de la prise en charge par le transporteur. Les marchandises voyagent aux risques et p&eacute;rils du client. L&rsquo;Acheteur est tenu de v&eacute;rifier l&#39;&eacute;tat de l&#39;emballage de la marchandise &agrave; la livraison et de signaler les dommages dus au transporteur sur le bon de livraison, ainsi qu&#39;&agrave; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo;, dans un d&eacute;lai d&#39;une semaine maximum</p>\r\n<p>\r\n	L&rsquo;Acheteur dispose d&rsquo;un d&eacute;lai de sept jours francs &agrave; compter de la date de r&eacute;ception, pour retourner &agrave; ses frais, les produits command&eacute;s, pour remboursement. Les produits doivent imp&eacute;rativement &ecirc;tre retourn&eacute;s &agrave; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; dans un parfait &eacute;tat de revente, dans leur &eacute;tat d&rsquo;origine, d&ucirc;ment scell&eacute;s, et accompagn&eacute;s de la facture correspondant &agrave; l&#39;achat. Tout produit incomplet, ab&icirc;m&eacute;, endommag&eacute; ou dont l&rsquo;emballage d&rsquo;origine aura &eacute;t&eacute; d&eacute;t&eacute;rior&eacute;, ne sera ni rembours&eacute; ni &eacute;chang&eacute;. L&#39;exercice du droit de r&eacute;tractation donnera lieu au choix de l&#39;acheteur : soit &agrave; un remboursement en num&eacute;raire sous un d&eacute;lai de 30 jours, soit &agrave; l&#39;attribution d&#39;un bon d&#39;achat.</p>\r\n<p>\r\n	Prenez note que les articles suivants ne peuvent &ecirc;tre retourn&eacute;s :</p>\r\n<ul>\r\n	<li>\r\n		<em>Les articles personnalis&eacute;s et faits sur mesure</em></li>\r\n	<li>\r\n		<em>Les aliments p&eacute;rissables et non-p&eacute;rissables</em></li>\r\n</ul>\r\n<p>\r\n	Les frais de transport ne sont pas remboursables, sauf dans le cas o&ugrave; vous retournez un article en raison d&#39;une erreur de notre part ou si nous avons d&eacute;termin&eacute; que le produit &eacute;tait d&eacute;fectueux.</p>\r\n<p>\r\n	Pour les achats effectu&eacute;s avec une carte de cr&eacute;dit, le remboursement sera cr&eacute;dit&eacute; sur la m&ecirc;me carte qui a &eacute;t&eacute; utilis&eacute;e pour effectuer l&#39;achat. Un cr&eacute;dit sera port&eacute; au compte utilis&eacute; pour r&eacute;gler l&#39;achat quelques jours plus tard pour que nous ayons le temps de recevoir et de v&eacute;rifier la marchandise retourn&eacute;e.</p>\r\n<h2>\r\n	Non-validit&eacute; partielle</h2>\r\n<p>\r\n	Si une ou plusieurs stipulations des pr&eacute;sentes conditions g&eacute;n&eacute;rales de vente sont tenues pour non valides ou d&eacute;clar&eacute;es comme telles en application d&rsquo;une loi, d&rsquo;un r&egrave;glement ou &agrave; la suite d&rsquo;une d&eacute;cision d&eacute;finitive d&rsquo;une juridiction comp&eacute;tente, les autres stipulations garderont toute leur force et leur port&eacute;e.</p>\r\n<h2>\r\n	Litiges</h2>\r\n<p>\r\n	Les ventes de produits de la soci&eacute;t&eacute; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; sont soumises &agrave; la loi canadienne. La soci&eacute;t&eacute; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; ne peut &ecirc;tre tenue pour responsable des dommages de toute nature, tant mat&eacute;riels qu&#39;immat&eacute;riels ou corporels, qui pourraient r&eacute;sulter d&#39;un mauvais fonctionnement ou de la mauvaise utilisation des produits commercialis&eacute;s. La responsabilit&eacute; de la soci&eacute;t&eacute; &laquo;&nbsp;nom de la compagnie&nbsp;&raquo; sera, en tout &eacute;tat de cause, limit&eacute;e au montant de la commande et ne saurait &ecirc;tre mise en cause pour de simples erreurs ou omissions qui auraient pu subsister malgr&eacute; toutes les pr&eacute;cautions prises dans la pr&eacute;sentation des produits. En cas de difficult&eacute;s dans l&#39;application du pr&eacute;sent contrat, l&#39;acheteur &agrave; la possibilit&eacute;, avant toute action en justice, de rechercher une solution amiable. Les r&eacute;clamations ou contestations seront toujours re&ccedil;ues avec bienveillance attentive, la bonne foi &eacute;tant toujours pr&eacute;sum&eacute;e chez celui qui prend la peine d&#39;exposer ses situations. En cas de litige, le client s&#39;adressera par priorit&eacute; &agrave; l&#39;entreprise pour obtenir une solution amiable.</p>\r\n<h2>\r\n	Taxes &eacute;ventuelles</h2>\r\n<p>\r\n	Pour les articles livr&eacute;s en dehors du Canada, des taxes &eacute;ventuelles et des droits de douane pourront &ecirc;tre impos&eacute;s lorsque votre colis parvient &agrave; destination. Ces droits de douane et ces taxes &eacute;ventuelles sont &agrave; votre charge et rel&egrave;vent de votre responsabilit&eacute;. Nous ne sommes pas tenus de v&eacute;rifier et de vous informer des droits de douane et taxes applicables. Pour les conna&icirc;tre, nous vous conseillons de vous renseigner aupr&egrave;s des autorit&eacute;s comp&eacute;tentes de votre pays.</p>\r\n<h2>\r\n	Achat par des personnes mineures</h2>\r\n<p>\r\n	Afin de prot&eacute;ger notre client&egrave;le, nous avons pour politique de ne pas vendre d&rsquo;articles aux personnes mineures. Si vous n&rsquo;&ecirc;tes pas majeur et que vous souhaitez effectuer des achats dans notre boutique en ligne, veuillez demander &agrave; un parent ou &agrave; un tuteur d&ucirc;ment nomm&eacute; de faire cet achat pour vous.</p>\r\n', '', 0, '', '', 'conditions'),
(25, 'en', 'Privacy Policy', '<p>\r\n	<em><strong>This model is provided to you as an example.</strong></em></p>\r\n<p>\r\n	<em><strong>Whichever model you choose to use, you must ensure that it corresponds to the reality of your business and your approach.</strong></em></p>\r\n<p>\r\n	<em><strong>You can consult various models from other e-commerce sites and also consult a lawyer specialized in contracts to ensure you are well protected.</strong></em></p>\r\n<p>\r\n	We know how important it is to protect your personal information. That&rsquo;s why we take great care in following these privacy policies:</p>\r\n<h2>\r\n	Usage of personal information</h2>\r\n<p>\r\n	Personal information includes your name, address, phone number, and e-mail address.</p>\r\n<p>\r\n	We may use your personal information for a number of different purposes, such as fulfilling requests for products, services or information; providing customer services; administering contests or promotions; offering new products and services; measuring and improving the effectiveness of our Web Sites or our marketing strategies; and adapting our offers to your preferences.</p>\r\n<p>\r\n	We may also collect and summarize customer information in a format that no longer identifies the individual for statistical purposes.</p>\r\n<h2>\r\n	Usage of non-personal information</h2>\r\n<p>\r\n	Like many other Web sites, we automatically collect certain non-personal information regarding Web site users that does not identify you. Examples include the Internet Protocol (IP) address of your computer, the IP address of your Internet Service Provider, the date and time you access the Web Site, the Internet address of the Web site from which you linked directly to the Web Site, the operating system you are using, the sections of the Web Site you visit, the Web Site pages read and images viewed, and the content you download from the Web Site.</p>\r\n<p>\r\n	This non-personal information is used for Web Site and system administration purposes and to improve the Web Site.</p>\r\n<p>\r\n	We may also use non-personal information to compile tracking information reports regarding Web Site user demographics, Web Site traffic patterns, and Web Site purchases, and then provide those reports to advertisers and others. None of the tracking information in the reports can be connected to the identities or other personal information of individual users. We may also link tracking information with personal information voluntarily provided by Web Site users. Once such a link is made, all of the linked information is treated as personal information and will be used and disclosed only in accordance with this Policy.</p>\r\n<h2>\r\n	Implied Consent</h2>\r\n<p>\r\n	In some cases, your consent is implied if we ask you to provide personal information with a stated purpose. For example, we can only deliver the product you have purchased if you provide us with your address and phone number.</p>\r\n<p>\r\n	We will never sell your personal information. However we may share your information with third parties acting on our behalf, for example to a delivery services, a product repair services, etc., or as permitted or required by law.</p>\r\n<h2>\r\n	Express Consent</h2>\r\n<p>\r\n	In other cases, we will ask you to give us your express consent to use your personal information to advise you of products or services that may be of interest to you. You can always refuse to have your information used for this purpose. For example, when you subscribe to our newsletter, we consider that you gave us your express consent to send you promotional information. You can always decide to unsubscribe. Each e-mail we send you will tell you how to decline further e-mail.</p>\r\n<p>\r\n	To accommodate changes in our service, the technology, and legal developments, this Policy might change over time. Don&rsquo;t hesitate to contact us if you have any question about this policy.</p>\r\n', '', 0, '', '', 'privacy-policy'),
(25, 'fr', 'Politique de confidentialité', '<p>\r\n	<em><strong>Ce mod&egrave;le vous est offert &agrave; titre d&rsquo;exemple.</strong></em></p>\r\n<p>\r\n	<em><strong>Peu importe le mod&egrave;le que vous choisissez, vous devez vous assurer qu&rsquo;il r&eacute;pond &agrave; la r&eacute;alit&eacute; de votre entreprise et &agrave; votre fa&ccedil;on de faire.&nbsp;</strong></em></p>\r\n<p>\r\n	<em><strong>Vous pouvez consulter diff&eacute;rents mod&egrave;les dans d&rsquo;autres sites de commerce &eacute;lectronique et consulter un avocat sp&eacute;cialis&eacute; dans les contrats pour vous assurer d&rsquo;&ecirc;tre bien prot&eacute;g&eacute;.</strong></em></p>\r\n<p>\r\n	Nous savons &agrave; quel point il est important de prot&eacute;ger vos renseignements personnels. Voil&agrave; pourquoi nous prenons grand soin de respecter les politiques suivantes :</p>\r\n<h2>\r\n	Usage des renseignements personnels</h2>\r\n<p>\r\n	Les renseignements personnels comprennent votre nom, votre adresse, votre num&eacute;ro de t&eacute;l&eacute;phone et votre adresse &eacute;lectronique</p>\r\n<p>\r\n	Nous pouvons utiliser vos renseignements personnels pour r&eacute;pondre &agrave; des demandes de produits, de services ou d&#39;information; fournir du service &agrave; la client&egrave;le; administrer des concours ou des promotions; offrir des nouveaux produits et services; &eacute;valuer et am&eacute;liorer l&#39;efficacit&eacute; de notre site Web ou de nos activit&eacute;s de commercialisation; adapter nos offres en fonction de vos pr&eacute;f&eacute;rences.</p>\r\n<p>\r\n	Nous pouvons &eacute;galement recueillir et r&eacute;sumer les coordonn&eacute;es du client dans un format qui ne permet plus d&#39;identifier l&#39;individu &agrave; des fins de statistiques.</p>\r\n<h2>\r\n	Usage des renseignements non personnels</h2>\r\n<p>\r\n	Comme un grand nombre d&#39;autres entreprises en ligne, nous recueillons automatiquement certains renseignements non personnels sur les utilisateurs du site Web. Ces renseignements ne permettent pas de vous identifier. Ils comprennent l&#39;adresse IP de votre ordinateur, l&#39;adresse IP de votre fournisseur de services Internet, la date et l&#39;heure auxquelles vous avez visit&eacute; le site Web, l&#39;adresse Internet du site Web &agrave; partir duquel vous &ecirc;tes parvenu directement &agrave; notre site, le syst&egrave;me d&#39;exploitation que vous utilisez, les sections du site Web que vous visitez, les pages du site Web que vous lisez et les images que vous regardez ainsi que le contenu que vous t&eacute;l&eacute;chargez &agrave; partir du site Web.</p>\r\n<p>\r\n	Ces renseignements non personnels sont utilis&eacute;s pour l&#39;administration du site Web et du syst&egrave;me de m&ecirc;me que pour l&#39;am&eacute;lioration du site.</p>\r\n<p>\r\n	Nous pouvons aussi utiliser les renseignements non personnels pour compiler des rapports sur les donn&eacute;es d&eacute;mographiques, les habitudes de transmission et les achats des utilisateurs du site Web, pour ensuite les remettre aux publicitaires et &agrave; d&#39;autres. Aucun des renseignements de contr&ocirc;le mentionn&eacute;s dans les rapports ne peut &ecirc;tre li&eacute; &agrave; l&#39;identit&eacute; de l&#39;utilisateur ou &agrave; tout autre renseignement personnel &agrave; son sujet. Nous pouvons &eacute;galement relier les renseignements de contr&ocirc;le avec les renseignements personnels fournis volontairement par les utilisateurs du site. Les informations combin&eacute;es ainsi sont trait&eacute;es comme des renseignements personnels et peuvent &ecirc;tre utilis&eacute;es et divulgu&eacute;es seulement en conformit&eacute; avec cette politique.</p>\r\n<h2>\r\n	Consentement tacite</h2>\r\n<p>\r\n	Lorsque nous vous demandons de nous fournir des renseignements personnels dans un but pr&eacute;cis, votre consentement est tacite. Par exemple, nous pouvons livrer le produit que vous avez achet&eacute; seulement si vous nous fournissez votre adresse et votre num&eacute;ro de t&eacute;l&eacute;phone.</p>\r\n<p>\r\n	Jamais nous ne vendons vos renseignements personnels. Toutefois, nous pouvons partager vos renseignements avec des tiers agissant en notre nom, comme par exemple un service de livraison, de r&eacute;paration, etc., selon ce qui est permis ou exig&eacute; par la loi.</p>\r\n<h2>\r\n	Consentement explicite</h2>\r\n<p>\r\n	Lorsque nous vous demandons la permission d&#39;utiliser vos renseignements personnels pour vous recommander des produits ou des services, votre consentement est explicite. Vous avez toujours la possibilit&eacute; de refuser que vos renseignements soient utilis&eacute;s &agrave; cette fin. Par exemple, lorsque vous vous abonnez notre infolettre, nous consid&eacute;rons que vous nous donnez la permission de vous envoyer des informations sur les promotions. Aussi, vous pourrez toujours choisir d&#39;annuler votre abonnement. Dans chaque infolettre que nous vous envoyons, nous vous expliquons comment refuser les prochains courriels.</p>\r\n<p>\r\n	Cette politique pourrait changer au fil du temps afin de s&#39;adapter aux changements dans notre service et &agrave; l&#39;&eacute;volution des technologies et des lois. N&#39;h&eacute;sitez pas &agrave; nous contacter pour toute question relative &agrave; notre politique de confidentialit&eacute;.</p>\r\n', '', 0, '', '', 'politique-de-confidentialite'),
(1, 'en', 'Home Page', '', '', 0, 'Store of product of ...', 'Product 1, Product 2, ....', ''),
(1, 'fr', 'Page d''accueil', '', '', 0, 'Magasin de produit de ...', 'Produit 1, Produit 2, ....', ''),
(33, 'fr', 'PROMOTIONS', '<p><img alt="" src="/images/userfiles/images/BOOTCAMP%20MODIFI%C3%89.jpg" style="width: 576px; height: 360px;" /></p>\r\n', '', 0, 'BOOTCAMP, LEO HARLEY, CENTRE DE MOTOS, PROMOTIONS, FORMATION MOTO', 'BOOTCAMP, LEO HARLEY, CENTRE DE MOTOS, PROMOTIONS, FORMATION MOTO', 'promotions'),
(31, 'fr', 'test', '<p>fasdfasdf</p>\r\n', '', 0, 'test', 'test', 'test'),
(31, 'en', 'test', '', '', 0, 'test', 'tes', 'test'),
(27, 'fr', 'Financement', '<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ultrices purus sem, ac elementum nulla faucibus sed. Mauris elementum semper rhoncus. Nunc varius sapien metus, vitae viverra metus venenatis eget. Nulla a lorem malesuada, tempus lectus non, pulvinar nisl. In congue consequat leo, id consectetur mi dapibus ac. Sed vehicula quam libero, sed varius augue sollicitudin et. Nunc diam ante, dapibus nec arcu et, egestas gravida diam. Duis non neque nec ante fringilla imperdiet nec at nisi. Fusce at suscipit mauris, aliquam commodo nisi. Vivamus tincidunt malesuada lobortis. Sed vulputate semper enim quis ullamcorper. Aliquam hendrerit, lorem non vehicula placerat, quam nunc pretium mi, at hendrerit sapien magna et ipsum. Praesent pharetra mauris id sapien bibendum fringilla. Praesent tristique mollis sapien vehicula convallis.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Etiam eget tristique felis. Phasellus fermentum mi metus, ac vulputate diam dapibus nec. Sed vehicula massa dapibus ante malesuada, vel consequat tellus pretium. Praesent ullamcorper dolor sed risus facilisis, vitae venenatis lacus tincidunt. Duis faucibus fermentum convallis. Duis quis enim ac odio mattis luctus. Ut est leo, vulputate et ante a, iaculis pretium augue. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec et turpis ac neque fringilla euismod. Cras iaculis leo nibh, vel tincidunt felis interdum ornare. Donec arcu est, commodo in egestas a, lacinia non libero.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Donec pellentesque sem lacus, consectetur bibendum lorem accumsan vel. Praesent bibendum dolor sed sodales scelerisque. Aenean a ultricies odio. Nulla accumsan sem vel nibh consequat pretium. Aliquam turpis lorem, placerat nec velit id, pulvinar imperdiet augue. Vestibulum nulla leo, gravida a dictum ac, bibendum sit amet massa. Proin tempor eros varius urna scelerisque bibendum. Donec nec bibendum nisl. Proin vel lectus a tellus rutrum bibendum consectetur et enim. Nullam venenatis orci eu sapien sodales, ut blandit leo pellentesque.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'https://CreditOnline.dealertrack.ca/Web/Default.aspx?Token=be2fdd1f-45ed-4251-aded-1b813399fd4c&Lang=fr', 1, 'Donec pellentesque sem lacus, consectetur bibendum lorem accumsan vel. Praesent bibendum dolor sed sodales scelerisque. Aenean a ultricies odio.', 'Donec pellentesque sem lacus, consectetur bibendum lorem accumsan vel. Praesent bibendum dolor sed sodales scelerisque. Aenean a ultricies odio.', 'financement'),
(27, 'en', 'Financing', '<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ultrices purus sem, ac elementum nulla faucibus sed. Mauris elementum semper rhoncus. Nunc varius sapien metus, vitae viverra metus venenatis eget. Nulla a lorem malesuada, tempus lectus non, pulvinar nisl. In congue consequat leo, id consectetur mi dapibus ac. Sed vehicula quam libero, sed varius augue sollicitudin et. Nunc diam ante, dapibus nec arcu et, egestas gravida diam. Duis non neque nec ante fringilla imperdiet nec at nisi. Fusce at suscipit mauris, aliquam commodo nisi. Vivamus tincidunt malesuada lobortis. Sed vulputate semper enim quis ullamcorper. Aliquam hendrerit, lorem non vehicula placerat, quam nunc pretium mi, at hendrerit sapien magna et ipsum. Praesent pharetra mauris id sapien bibendum fringilla. Praesent tristique mollis sapien vehicula convallis.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Etiam eget tristique felis. Phasellus fermentum mi metus, ac vulputate diam dapibus nec. Sed vehicula massa dapibus ante malesuada, vel consequat tellus pretium. Praesent ullamcorper dolor sed risus facilisis, vitae venenatis lacus tincidunt. Duis faucibus fermentum convallis. Duis quis enim ac odio mattis luctus. Ut est leo, vulputate et ante a, iaculis pretium augue. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec et turpis ac neque fringilla euismod. Cras iaculis leo nibh, vel tincidunt felis interdum ornare. Donec arcu est, commodo in egestas a, lacinia non libero.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Donec pellentesque sem lacus, consectetur bibendum lorem accumsan vel. Praesent bibendum dolor sed sodales scelerisque. Aenean a ultricies odio. Nulla accumsan sem vel nibh consequat pretium. Aliquam turpis lorem, placerat nec velit id, pulvinar imperdiet augue. Vestibulum nulla leo, gravida a dictum ac, bibendum sit amet massa. Proin tempor eros varius urna scelerisque bibendum. Donec nec bibendum nisl. Proin vel lectus a tellus rutrum bibendum consectetur et enim. Nullam venenatis orci eu sapien sodales, ut blandit leo pellentesque.</p>\r\n\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;\r\n<p>&nbsp;</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n</p>\r\n', 'https://www.rds.ca', 0, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ultrices purus sem, ac elementum nulla faucibus sed. Mauris elementum semper rhoncus', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ultrices purus sem, ac elementum nulla faucibus sed. Mauris elementum semper rhoncus', 'financement'),
(28, 'fr', 'Location', '<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Vous voulez profiter de l&rsquo;&eacute;t&eacute; au maximum? Nous vous proposons de louer une moto, et pas n&rsquo;importe laquelle! Nous sommes la r&eacute;f&eacute;rence pour la location de motos. Nous offrons un service complet de location d&#39;Harley-Davidson pour une journ&eacute;e, une fin de semaine ou une semaine compl&egrave;te. Vous avez la possibilit&eacute; de louer les mod&egrave;les les plus populaires de motos Harley-Davidson. Nous avons les prix les plus comp&eacute;titifs pour les locations. N&#39;h&eacute;sitez pas &agrave; communiquer avec nous afin de r&eacute;aliser votre r&ecirc;ve de conduire une Harley-Davidson pour un prix abordable. De plus, si vous d&eacute;cidez d&#39;acheter votre moto neuve dans les sept jours de votre location (&agrave; l&#39;exception d&#39;une location suite &agrave; une r&eacute;clamation d&#39;assurance) on vous rembourse 100% de votre location avant&nbsp; taxes!!!</font></p>\r\n\r\n<ul>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Vaste inventaire de motos usag&eacute;es</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Prix sujets &agrave; changement sans pr&eacute;avis</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">D&eacute;p&ocirc;t de 50% exig&eacute; pour la r&eacute;servation, en cas de mauvaise temp&eacute;rature la location est d&eacute;pla&ccedil;able</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Annulation 24 heures d&#39;avance</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Assurance collision incluse dans le d&eacute;p&ocirc;t de garantie</font></p>\r\n\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>Exigences&nbsp;</b></u></font><font face="Arial, sans-serif">: </font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Avoir 25 ans</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Avoir un permis valide classe 6A</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">D&eacute;p&ocirc;t sur carte de cr&eacute;dit de 50% de la location</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Casque obligatoire (location disponible de 10$)</font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">Usage limit&eacute; au Canada seulement</font></p>\r\n	</li>\r\n</ul>\r\n\r\n<p style="width: 375px;">&nbsp;</p>\r\n', '', 0, 'location de motos, harley davidson, louer moto, ', 'location de motos, harley davidson, louer moto, ', 'location'),
(28, 'en', 'Renting', '<p lang="en" style="margin-bottom: 0cm"><font face="Arial, sans-serif">Want to enjoy summer to the fullest? We suggest renting a motorcycle, and not any! We are the benchmark for motorcycle rental. We offer a full rental service Harley-Davidson for a day, a weekend or a full week. You can rent the most popular models of Harley-Davidson. We have the most competitive prices for rentals. Do not hesitate to contact us to realize your dream of driving a Harley-Davidson for an affordable price. In addition, if you decide to buy your new motorcycle within seven days of your holiday (except a lease due to an insurance claim), we will refund 100% of your rental fee free!</font></p>\r\n\r\n<p lang="en" style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Extensive inventory of used motorcycles </span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Prices subject to change without notice </span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">50% deposit required to reserve, in case of bad weather the rental is moved<br />\r\n	cancellation 24 hours in advance </span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Collision included in the deposit </span></font><br />\r\n	&nbsp;</p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en"><b>Requirements: </b></span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Be 25 years old </span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Have a valid Class 6A license </span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Deposit on credit card 50% of rental</span></font></p>\r\n	</li>\r\n	<li>\r\n	<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><span lang="en">Mandatory helmet (rental available $ 10)</span></font></p>\r\n	</li>\r\n</ul>\r\n', '', 0, 'renting motorcycle, renting, harley davidson', 'renting motorcycle, renting, harley davidson', 'rent'),
(29, 'fr', 'Services', '<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;"><span style="font-size:12px;">Nos techniciens sp&eacute;cialis&eacute;s sauront r&eacute;pondre &agrave; tous vos besoins,&nbsp;<span style="font-family: Helvetica,arial,sans-serif;">du simple changement d&#39;huile &agrave; la modification de votre moteur. &nbsp;Notre personnel saura personnaliser votre moto selon votre go&ucirc;t et votre budget.</span></span></p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px;"><span style="font-size:12px;">L&#39;&eacute;quipe de L&eacute;o Harley-Davidson est une &eacute;quipe de vrai passionn&eacute; d&#39;Harley-Davidson.</span></p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px;"><span style="font-size:12px;">Notre d&eacute;partement du service est ouvert en saison estivale,les jeudis et vendredis soirs ainsi que les samedis selon la demande de nos clients.</span></p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px;"><span style="font-size:12px;">Durant la saison hivernale informez-vous ed nos diff&eacute;rents forfaits offerts pour l&#39;entreposage de votre moto.</span></p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px;"><span style="font-size:12px;">Du nouveau cette ann&eacute;e, venez nous voir sans rendez-vous pour un changement d&#39;huile moteur, transmission et primaire ou pour changer vos pneus.. 1er arriv&eacute; 1er servi!</span></p>\r\n', '', 0, 'entreposage moto, réparation moto, harley davidson, ', 'entreposage moto, réparation moto, harley davidson, ', 'services'),
(29, 'en', 'Services', '<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Donec pellentesque sem lacus, consectetur bibendum lorem accumsan vel. Praesent bibendum dolor sed sodales scelerisque. Aenean a ultricies odio. Nulla accumsan sem vel nibh consequat pretium. Aliquam turpis lorem, placerat nec velit id, pulvinar imperdiet augue. Vestibulum nulla leo, gravida a dictum ac, bibendum sit amet massa. Proin tempor eros varius urna scelerisque bibendum. Donec nec bibendum nisl. Proin vel lectus a tellus rutrum bibendum consectetur et enim. Nullam venenatis orci eu sapien sodales, ut blandit leo pellentesque.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Sed tellus dui, ornare quis risus at, fermentum interdum elit. Morbi neque mauris, aliquet ut scelerisque at, imperdiet eget lacus. Sed libero eros, malesuada et nisi fringilla, consectetur vulputate sapien. Sed ac quam lorem. Nam sem enim, varius quis lacus at, gravida blandit turpis. Integer egestas commodo diam. Aliquam cursus placerat venenatis. Nullam elementum metus nulla, quis iaculis quam vestibulum vitae. Ut blandit fermentum facilisis. Fusce id ante faucibus, rutrum purus id, adipiscing velit. Aenean rutrum sem lorem, ac bibendum mi aliquam ac. Nullam elit est, fringilla eget faucibus eget, tristique in nisi. Proin volutpat elit nec ligula pharetra tristique. Integer tellus massa, viverra at leo ac, adipiscing porta nisl. Donec sit amet ante interdum nisi placerat interdum et eget felis. Sed dui dolor, scelerisque eu malesuada eu, elementum eu lorem.</p>\r\n\r\n<p style="margin: 0px 0px 14px; padding: 0px; text-align: justify; line-height: 14px; font-family: Arial, Helvetica, sans; font-size: 11px;">Curabitur elementum non enim a ullamcorper. Nam imperdiet consectetur erat, in iaculis nunc eleifend ut. Donec tristique velit interdum rutrum imperdiet. Mauris eu lacus lectus. Donec quis aliquet odio. Quisque ultrices euismod leo facilisis tincidunt. Nam pellentesque ipsum a felis molestie ullamcorper. Vestibulum ut placerat ligula. Praesent ornare molestie ipsum, ut dapibus tellus dapibus sed. Vivamus aliquam mattis massa, in imperdiet lacus feugiat vel. Donec varius sem non viverra posuere. Aenean nunc lectus, vulputate non volutpat porttitor, porta et lectus. Nam porttitor massa orci, eu feugiat massa placerat eget.</p>\r\n', '', 0, 'Sed tellus dui, ornare quis risus at, fermentum interdum elit. Morbi neque mauris, aliquet ut scelerisque at, imperdiet eget lacus. Sed libero eros, malesuada et nisi fringilla, consectetur vulputate ', 'Sed tellus dui, ornare quis risus at, fermentum interdum elit. Morbi neque mauris, aliquet ut scelerisque at, imperdiet eget lacus. Sed libero eros, malesuada et nisi fringilla, consectetur vulputate sapien. Sed ac quam lorem. Nam sem enim, varius quis la', 'services');
INSERT INTO `cmspage_description` (`id_cmspage`, `language_code`, `name`, `description`, `external_link_link`, `external_link_target_blank`, `meta_description`, `meta_keywords`, `alias`) VALUES
(30, 'fr', 'Témoignages client', '<ul>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Pellentesque eget velit rhoncus, posuere metus id, consequat nunc. Sed tempor, neque et dapibus lacinia, nisi purus sollicitudin urna, sed ultricies ipsum orci accumsan erat. Pellentesque volutpat facilisis dapibus. Vivamus sit amet purus non mi lobortis suscipit.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Etiam eget nunc a leo rutrum accumsan vel nec nulla. Ut vel velit interdum velit semper lacinia. In non nulla posuere, hendrerit urna eget, aliquam arcu. Quisque sed velit vitae risus adipiscing imperdiet.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Maecenas ornare ante eget enim varius, eget luctus odio auctor. Integer a tortor leo. Morbi consequat nisl non neque vehicula consequat ac id nisl. Fusce pulvinar adipiscing leo ac volutpat. Nam elementum pulvinar nisi id convallis. Vestibulum sit amet orci vestibulum tortor luctus suscipit.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Sed scelerisque urna a iaculis rutrum. Pellentesque aliquam ac tortor et bibendum. Vivamus lacinia sem ut feugiat ornare.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Vestibulum pharetra mauris vel lorem lacinia, ac porttitor sem blandit. Phasellus id justo ac quam rhoncus pulvinar. Vivamus mattis ante quis nulla vestibulum ultrices.</li>\r\n</ul>\r\n', '', 0, ' Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor. ', ' Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor. ', 'temoignage-clients'),
(30, 'en', 'Testimonial', '<ul>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auctor elementum odio ac porttitor.&nbsp;</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Pellentesque eget velit rhoncus, posuere metus id, consequat nunc. Sed tempor, neque et dapibus lacinia, nisi purus sollicitudin urna, sed ultricies ipsum orci accumsan erat. Pellentesque volutpat facilisis dapibus. Vivamus sit amet purus non mi lobortis suscipit.&nbsp;</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Etiam eget nunc a leo rutrum accumsan vel nec nulla. Ut vel velit interdum velit semper lacinia. In non nulla posuere, hendrerit urna eget, aliquam arcu. Quisque sed velit vitae risus adipiscing imperdiet.</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Maecenas ornare ante eget enim varius, eget luctus odio auctor. Integer a tortor leo. Morbi consequat nisl non neque vehicula consequat ac id nisl. Fusce pulvinar adipiscing leo ac volutpat. Nam elementum pulvinar nisi id convallis. Vestibulum sit amet orci vestibulum tortor luctus suscipit.&nbsp;</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Sed scelerisque urna a iaculis rutrum. Pellentesque aliquam ac tortor et bibendum. Vivamus lacinia sem ut feugiat ornare.&nbsp;</li>\r\n	<li style="text-align: justify; font-size: 11px; line-height: 14px; margin: 0px 0px 14px; padding: 0px; font-family: Arial, Helvetica, sans;">Vestibulum pharetra mauris vel lorem lacinia, ac porttitor sem blandit. Phasellus id justo ac quam rhoncus pulvinar. Vivamus mattis ante quis nulla vestibulum ultrices.</li>\r\n</ul>\r\n', '', 0, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla ', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dolor velit, auctor et metus vel, placerat aliquet ipsum. Ut eleifend ultricies dui, fringilla egestas nunc porta ut. Sed posuere nulla pretium, rhoncus lectus id, suscipit dolor. Mauris auct', 'testimonial');

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `comments` varchar(200) NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=152 ;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `name`, `value`, `comments`, `id_user_modified`, `date_modified`) VALUES
(1, 'language', 'fr', '', 113, '2011-03-03 17:47:57'),
(2, 'currency', 'CAD', '', 113, '2011-03-03 17:47:57'),
(3, 'backend_template', 'default', '', 0, '2011-04-05 16:41:06'),
(61, 'price_increment', '1000', 'For the categories', 113, '2011-10-04 11:29:58'),
(5, 'images_orientation', 'portrait', '', 113, '2011-05-06 20:53:29'),
(6, 'portrait_thumb_width', '75', '', 0, '2011-11-21 21:18:14'),
(7, 'portrait_thumb_height', '100', '', 0, '2011-11-07 18:58:06'),
(8, 'portrait_suggest_width', '228', '', 0, '2014-03-26 17:42:57'),
(9, 'portrait_suggest_height', '320', '', 0, '2014-03-26 17:43:01'),
(10, 'portrait_listing_width', '228', '', 0, '2014-03-26 17:43:08'),
(11, 'portrait_listing_height', '230', '', 0, '2014-03-27 18:53:36'),
(12, 'portrait_cover_width', '420', '', 0, '2014-03-26 18:19:34'),
(13, 'portrait_cover_height', '420', '', 0, '2014-03-26 18:19:42'),
(14, 'portrait_zoom_width', '800', '', 0, '2014-03-26 18:20:45'),
(15, 'portrait_zoom_height', '800', '', 0, '2011-02-18 15:57:52'),
(16, 'landscape_thumb_width', '100', '', 0, '2011-11-07 18:57:38'),
(17, 'landscape_thumb_height', '75', '', 0, '2011-11-21 21:18:39'),
(18, 'landscape_suggest_width', '320', '', 0, '2014-03-26 17:43:37'),
(19, 'landscape_suggest_height', '228', '', 0, '2014-03-26 17:43:42'),
(20, 'landscape_listing_width', '360', '', 0, '2014-03-26 17:43:48'),
(21, 'landscape_listing_height', '228', '', 0, '2014-03-26 17:43:53'),
(22, 'landscape_cover_width', '360', '', 0, '2011-02-18 16:14:32'),
(23, 'landscape_cover_height', '270', '', 0, '2011-02-18 16:14:32'),
(24, 'landscape_zoom_width', '800', '', 0, '2011-02-18 16:14:51'),
(25, 'landscape_zoom_height', '600', '', 0, '2011-03-02 15:10:30'),
(26, 'enable_inventory', '1', '', 18, '2011-03-03 17:47:57'),
(27, 'enable_local_pickup', '1', '', 99, '2011-03-03 17:47:57'),
(28, 'enable_shipping', '0', '', 99, '2011-03-03 17:47:57'),
(29, 'enable_specification', '1', '', 0, '2011-03-03 17:47:57'),
(30, 'enable_suggestion', '1', '', 18, '2011-03-03 17:47:57'),
(31, 'enable_related', '1', '', 18, '2011-03-03 17:47:57'),
(32, 'enable_option', '1', '', 18, '2011-03-03 17:47:57'),
(33, 'enable_package', '1', '', 0, '2011-03-03 17:47:57'),
(34, 'enable_rebate', '1', '', 18, '2011-03-03 17:47:57'),
(35, 'enable_mi_rebate', '1', '', 18, '2011-03-03 17:47:57'),
(36, 'enable_gift_certificate', '1', '', 18, '2011-03-03 17:47:57'),
(37, 'enable_coupon', '1', '', 18, '2011-03-03 17:47:57'),
(38, 'enable_customer_type', '1', '', 18, '2011-03-03 17:47:57'),
(39, 'maintenance_mode', '0', '', 113, '2014-04-02 17:05:17'),
(40, 'site_name', 'Léo Harley-Davidson', '', 113, '2011-03-03 17:47:57'),
(41, 'webmaster_email', 'infos@leoharleydavidson.com', '', 113, '2011-10-17 13:29:12'),
(42, 'cf_show_featured_products_menu', '1', '', 113, '2011-05-27 17:08:53'),
(43, 'cf_show_new_products_menu', '1', '', 113, '2011-05-27 17:08:53'),
(44, 'cf_new_products_no_days', '7', '', 113, '2011-05-27 17:08:53'),
(45, 'cf_show_top_sellers_menu', '1', '', 113, '2011-05-27 17:08:53'),
(46, 'cf_show_on_sale_menu', '1', '', 113, '2011-05-27 17:08:53'),
(47, 'cf_show_combo_deals_menu', '1', '', 113, '2011-05-27 17:08:53'),
(49, 'cf_show_price_range', '1', '', 113, '2011-05-27 17:08:53'),
(50, 'cf_show_brands', '1', '', 113, '2011-05-27 17:08:53'),
(51, 'cf_show_ratings', '1', '', 113, '2011-05-27 17:08:53'),
(52, 'weighing_unit', '1', '0=lb, 1=kg', 99, '2011-07-15 19:53:18'),
(53, 'enable_variant', '1', '', 18, '2011-05-27 18:56:22'),
(86, 'default_product_used', '0', '', 113, '2012-02-13 14:37:19'),
(56, 'enable_show_qty_remaining', '0', '', 113, '2011-09-23 17:23:50'),
(57, 'enable_show_qty_remaining_start_at', '100', '', 113, '2011-09-23 17:24:09'),
(58, 'enable_unlimited_coupon_cart', '1', '', 113, '2011-09-24 14:17:25'),
(59, 'enable_free_shipping', '1', '', 113, '2013-05-31 20:33:31'),
(60, 'enable_free_shipping_min_cart_value', '0', '', 113, '2011-09-25 13:10:33'),
(62, 'product_sort_by', '6', 'For the categories', 113, '2011-10-04 11:29:58'),
(75, 'css_main_color', '#F66C00', '', 0, '2014-03-27 19:05:12'),
(63, 'cf_show_bundled_product_menu', '1', '', 113, '2011-10-27 19:49:35'),
(64, 'company_address', '8705, Boul Taschereau', '', 113, '2011-11-01 14:08:31'),
(65, 'company_city', 'Brossard', '', 113, '2011-11-01 14:08:31'),
(66, 'company_country_code', 'CA', '', 113, '2011-11-01 14:08:31'),
(67, 'company_state_code', 'QC', '', 113, '2011-11-01 14:08:31'),
(68, 'company_zip', 'J4Y 1A4', '', 113, '2011-11-01 14:08:31'),
(69, 'company_telephone', '450 443-4488', '', 113, '2011-11-01 14:08:31'),
(70, 'company_fax', '450 678-0375', '', 113, '2011-11-01 14:08:31'),
(72, 'facebook', 'https://www.facebook.com/leo.harley.1?fref=ts', '', 113, '2011-11-01 15:28:33'),
(71, 'company_company', 'Léo Harley-Davidson', '', 113, '2011-11-01 15:24:41'),
(73, 'twitter', '', '', 113, '2011-11-01 15:28:33'),
(74, 'google_analytics', '', '', 113, '2011-11-01 15:28:33'),
(76, 'css_full_content_width', '968', '990px - 2px (border) - 20px (padding)', 0, '2011-11-07 12:44:20'),
(77, 'css_left_column_content_width', '235', '', 0, '2011-11-07 15:16:01'),
(78, 'css_center_column_content_width', '720', '', 0, '2011-11-07 16:57:53'),
(79, 'css_suggest_content_width', '600', '', 0, '2011-11-07 15:16:01'),
(80, 'css_right_column_content_width', '0', '', 0, '2011-11-07 16:58:06'),
(81, 'company_logo_max_width', '250', 'Maximum widht of the uploaded logo in pixel', 0, '2012-02-13 19:51:52'),
(82, 'company_email', 'infos@leoharleydavidson.com', '', 113, '2011-11-16 14:01:06'),
(83, 'twitter_user', '', '', 0, '2011-12-15 14:45:01'),
(84, 'no_reply_email', 'no-reply@leoharleydavidson.com', '', 113, '2012-01-06 15:18:20'),
(87, 'default_product_taxable', '1', '', 113, '2012-02-13 15:17:42'),
(88, 'company_logo_max_height', '80', 'Maximum height of the uploaded logo in pixel', 0, '2012-02-14 13:30:36'),
(89, 'banner_width', '1585', 'Current Width of the Banner on Home Page', 0, '2014-03-03 18:01:28'),
(90, 'banner_height', '399', 'Current Height of the Banner on Home Page', 0, '2014-03-03 18:10:18'),
(91, 'shipping_sender_city', '', '', 99, '2012-04-11 17:15:47'),
(92, 'shipping_sender_state_code', 'QC', '', 99, '2012-04-11 17:15:47'),
(93, 'shipping_sender_country_code', 'CA', '', 99, '2012-04-11 17:15:47'),
(94, 'shipping_sender_zip', '', '', 99, '2012-04-11 17:15:47'),
(95, 'paypal_api_username', '', '', 0, '2013-04-15 13:25:26'),
(96, 'paypal_api_password', '', '', 0, '2013-10-11 22:55:24'),
(97, 'paypal_api_signature', '', '', 0, '2013-10-11 22:55:24'),
(98, 'paypal_api_pdt_token', '', '', 0, '2012-04-27 14:16:15'),
(99, 'enable_paypal', '0', '', 0, '2014-01-18 00:41:10'),
(100, 'company_logo_paypal_max_height', '60', '', 0, '2012-05-01 19:56:14'),
(101, 'ssl_installed', '0', '', 0, '2013-11-06 15:17:15'),
(104, 'google_analytics_email', '', '', 113, '2012-05-17 19:33:19'),
(105, 'google_analytics_password', '', '', 113, '2012-05-17 19:33:19'),
(106, 'display_telephone', '1', 'Display or not the phone number at the top of the page', 113, '2013-11-06 15:17:09'),
(103, 'google_analytics_profile_id', '', '', 113, '2012-05-17 14:38:30'),
(107, 'display_price', '1', '', 113, '2012-06-29 12:58:53'),
(108, 'enable_payment', '0', '', 0, '2014-01-03 12:56:42'),
(109, 'display_menu_featured_products', '1', '', 113, '2012-09-07 14:32:43'),
(110, 'display_menu_new_products', '1', '', 113, '2012-09-07 14:32:43'),
(111, 'display_menu_on_sale', '1', '', 113, '2012-09-07 14:32:43'),
(112, 'display_menu_top_sellers', '0', '', 113, '2012-09-07 14:32:43'),
(113, 'display_menu_add_wishlist', '0', '', 113, '2012-09-07 14:36:00'),
(114, 'display_menu_price_alert', '0', '', 113, '2012-09-07 14:36:00'),
(115, 'display_menu_rate_product', '0', '', 113, '2012-09-07 14:36:00'),
(116, 'display_menu_print_page', '1', '', 113, '2012-09-07 14:36:00'),
(117, 'enable_auto_completed_order', '0', '', 0, '2012-09-14 11:44:55'),
(118, 'scorm', '0', '1 = Show link to the Scorm Report in Admin', 0, '2013-11-14 19:56:14'),
(119, 'stream_file', '0', '1 = Show add file to stream in Manage Product - Downloadable Videos and Files', 0, '2013-05-15 19:36:44'),
(120, 'etalage_effect_image_product_click_zoom', '0', 'In SC Template for the Etalage effect in product page', 0, '2012-10-24 15:52:35'),
(121, 'etalage_effect_image_product_show_icon', '1', 'In SC Template for the Etalage effect in product page', 0, '2012-10-24 15:52:35'),
(122, 'etalage_effect_image_product_remove_zoom', '0', 'In SC Template for the Etalage effect in product page', 0, '2012-10-24 18:57:00'),
(123, 'max_allowed_video', '2048', 'Maximum allowed for all video in mb.', 0, '2012-11-08 18:19:15'),
(124, 'reseller', '', 'Name of the reseller of Simple Commerce to use his logo', 0, '2012-11-14 13:54:31'),
(125, 'reseller_website', '', 'Website of the reseller (http://www.simplecommerce.com)', 0, '2012-11-14 12:48:11'),
(126, 'scorm_certificate', '0', 'If 1 then we can attach certificate to a Scorm File', 0, '2013-11-14 19:56:18'),
(127, 'enable_cash_payments', '0', '', 0, '2014-01-20 16:21:09'),
(128, 'enable_check_payments', '0', '', 0, '2014-02-11 14:53:37'),
(129, 'check_payment_description', '', '', 0, '2014-02-11 14:53:37'),
(130, 'show_short_desc_listing', '0', 'If 1 then we show the short description in product listing', 0, '2013-01-21 14:36:19'),
(131, 'allow_add_to_cart', '0', '', 113, '2013-01-21 21:21:19'),
(132, 'enable_order_email_notification', '0', '', 113, '2013-02-27 04:20:47'),
(133, 'order_email_notification_email', '', '', 113, '2013-02-27 04:20:47'),
(134, 'dhtmlx_path', 'dhtmlx_3.5/', '', 0, '2013-01-24 18:50:54'),
(135, 'dhtmlx_skin', 'dhx_skyblue', '', 0, '2013-01-24 18:50:54'),
(136, 'banner_delay_between', '5000', 'Delay between banner in home page', 0, '2013-01-25 15:40:13'),
(137, 'news_width', '150', '', 0, '2013-01-28 13:03:40'),
(138, 'social_network_share', '2', 'if 0 show nothing, If 1 then show list of social network below pubs in the left column, If 2 then show list of social network below pubs in the right column', 0, '2013-01-29 20:32:19'),
(139, 'display_menu_news', '1', '', 113, '2013-03-08 19:02:31'),
(140, 'etalage_effect_image_product_remove_zoom_only', '1', 'In SC Template for the Etalage effect in product page', 0, '2013-03-19 18:17:03'),
(142, 'store_locations_default_lat', '45.4420601', '', 113, '2013-05-15 19:35:57'),
(143, 'store_locations_default_lng', '-73.47209279999998', '', 113, '2013-05-15 19:35:54'),
(144, 'display_menu_store_locations', '0', '', 113, '2013-05-15 19:35:38'),
(145, 'store_locations_logo_width', '120', '', 0, '2013-05-02 19:02:15'),
(146, 'display_multiple_variants_form', '0', '', 113, '2013-09-04 18:07:51'),
(147, 'company_logo_file', 'logo.jpg', '', 0, '2013-10-11 22:56:09'),
(148, 'company_logo_print_file', 'logo_print.jpg', '', 0, '2013-10-11 22:56:09'),
(149, 'company_logo_paypal_file', 'logo_paypal.jpg', '', 0, '2013-10-11 22:56:09'),
(150, 'show_newsletter_form', '1', '', 0, '2013-11-07 14:22:19'),
(151, 'white_label', '0', 'To hide all SC Logo', 0, '2014-02-11 19:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `config_address_pickup`
--

CREATE TABLE IF NOT EXISTS `config_address_pickup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_allow_add_to_cart_exceptions`
--

CREATE TABLE IF NOT EXISTS `config_allow_add_to_cart_exceptions` (
  `id_product` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_product` (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config_backup`
--

CREATE TABLE IF NOT EXISTS `config_backup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `comments` varchar(200) NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_credit_card`
--

CREATE TABLE IF NOT EXISTS `config_credit_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `config_credit_card`
--

INSERT INTO `config_credit_card` (`id`, `name`, `image`, `active`) VALUES
(1, 'Visa', 'cc/v.png', 0),
(2, 'MasterCard', 'cc/mc.png', 0),
(3, 'Amex', 'cc/ax.png', 0),
(4, 'Verified by Visa', 'cc/vbv.png', 0),
(5, 'MasterCard SecureCode', 'cc/mcsc.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `config_display_price_exceptions`
--

CREATE TABLE IF NOT EXISTS `config_display_price_exceptions` (
  `id_product` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_product` (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config_do_not_ship_region`
--

CREATE TABLE IF NOT EXISTS `config_do_not_ship_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `country_code_2` (`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_fixed_shipping_price`
--

CREATE TABLE IF NOT EXISTS `config_fixed_shipping_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `price` decimal(13,2) unsigned NOT NULL,
  `max_cart_price` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_free_shipping_product_exceptions`
--

CREATE TABLE IF NOT EXISTS `config_free_shipping_product_exceptions` (
  `id_product` int(10) unsigned NOT NULL,
  KEY `id_product` (`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config_free_shipping_region`
--

CREATE TABLE IF NOT EXISTS `config_free_shipping_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `country_code_2` (`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_ship_only_region`
--

CREATE TABLE IF NOT EXISTS `config_ship_only_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `country_code_2` (`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`code`) VALUES
('AD'),
('AE'),
('AF'),
('AG'),
('AI'),
('AL'),
('AM'),
('AN'),
('AO'),
('AQ'),
('AR'),
('AS'),
('AT'),
('AU'),
('AW'),
('AX'),
('AZ'),
('BA'),
('BB'),
('BD'),
('BE'),
('BF'),
('BG'),
('BH'),
('BI'),
('BJ'),
('BL'),
('BM'),
('BN'),
('BO'),
('BR'),
('BS'),
('BT'),
('BV'),
('BW'),
('BY'),
('BZ'),
('CA'),
('CC'),
('CD'),
('CF'),
('CG'),
('CH'),
('CI'),
('CK'),
('CL'),
('CM'),
('CN'),
('CO'),
('CR'),
('CU'),
('CV'),
('CX'),
('CY'),
('CZ'),
('DE'),
('DJ'),
('DK'),
('DM'),
('DO'),
('DZ'),
('EC'),
('EE'),
('EG'),
('EH'),
('ER'),
('ES'),
('ET'),
('FI'),
('FJ'),
('FK'),
('FM'),
('FO'),
('FR'),
('GA'),
('GB'),
('GD'),
('GE'),
('GF'),
('GG'),
('GH'),
('GI'),
('GL'),
('GM'),
('GN'),
('GP'),
('GQ'),
('GR'),
('GS'),
('GT'),
('GU'),
('GW'),
('GY'),
('HK'),
('HM'),
('HN'),
('HR'),
('HT'),
('HU'),
('ID'),
('IE'),
('IL'),
('IM'),
('IN'),
('IO'),
('IQ'),
('IR'),
('IS'),
('IT'),
('JE'),
('JM'),
('JO'),
('JP'),
('KE'),
('KG'),
('KH'),
('KI'),
('KM'),
('KN'),
('KP'),
('KR'),
('KW'),
('KY'),
('KZ'),
('LA'),
('LB'),
('LC'),
('LI'),
('LK'),
('LR'),
('LS'),
('LT'),
('LU'),
('LV'),
('LY'),
('MA'),
('MC'),
('MD'),
('ME'),
('MF'),
('MG'),
('MH'),
('MK'),
('ML'),
('MM'),
('MN'),
('MO'),
('MP'),
('MQ'),
('MR'),
('MS'),
('MT'),
('MU'),
('MV'),
('MW'),
('MX'),
('MY'),
('MZ'),
('NA'),
('NC'),
('NE'),
('NF'),
('NG'),
('NI'),
('NL'),
('NO'),
('NP'),
('NR'),
('NU'),
('NZ'),
('OM'),
('PA'),
('PE'),
('PF'),
('PG'),
('PH'),
('PK'),
('PL'),
('PM'),
('PN'),
('PR'),
('PS'),
('PT'),
('PW'),
('PY'),
('QA'),
('RE'),
('RO'),
('RS'),
('RU'),
('RW'),
('SA'),
('SB'),
('SC'),
('SD'),
('SE'),
('SG'),
('SH'),
('SI'),
('SJ'),
('SK'),
('SL'),
('SM'),
('SN'),
('SO'),
('SR'),
('ST'),
('SV'),
('SY'),
('SZ'),
('TC'),
('TD'),
('TF'),
('TG'),
('TH'),
('TJ'),
('TK'),
('TL'),
('TM'),
('TN'),
('TO'),
('TR'),
('TT'),
('TV'),
('TW'),
('TZ'),
('UA'),
('UG'),
('UM'),
('US'),
('UY'),
('UZ'),
('VA'),
('VC'),
('VE'),
('VG'),
('VI'),
('VN'),
('VU'),
('WF'),
('WS'),
('YE'),
('YT'),
('ZA'),
('ZM'),
('ZW');

-- --------------------------------------------------------

--
-- Table structure for table `country_description`
--

CREATE TABLE IF NOT EXISTS `country_description` (
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `name` (`name`),
  KEY `language_code` (`language_code`),
  KEY `country_code` (`country_code`),
  KEY `country_code_2` (`country_code`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `country_description`
--

INSERT INTO `country_description` (`country_code`, `language_code`, `name`) VALUES
('AF', 'en', 'AFGHANISTAN'),
('AX', 'en', 'ÅLAND ISLANDS'),
('AL', 'en', 'ALBANIA'),
('DZ', 'en', 'ALGERIA'),
('AS', 'en', 'AMERICAN SAMOA'),
('AD', 'en', 'ANDORRA'),
('AO', 'en', 'ANGOLA'),
('AI', 'en', 'ANGUILLA'),
('AQ', 'en', 'ANTARCTICA'),
('AG', 'en', 'ANTIGUA AND BARBUDA'),
('AR', 'en', 'ARGENTINA'),
('AM', 'en', 'ARMENIA'),
('AW', 'en', 'ARUBA'),
('AU', 'en', 'AUSTRALIA'),
('AT', 'en', 'AUSTRIA'),
('AZ', 'en', 'AZERBAIJAN'),
('BS', 'en', 'BAHAMAS'),
('BH', 'en', 'BAHRAIN'),
('BD', 'en', 'BANGLADESH'),
('BB', 'en', 'BARBADOS'),
('BY', 'en', 'BELARUS'),
('BE', 'en', 'BELGIUM'),
('BZ', 'en', 'BELIZE'),
('BJ', 'en', 'BENIN'),
('BM', 'en', 'BERMUDA'),
('BT', 'en', 'BHUTAN'),
('BO', 'en', 'BOLIVIA, PLURINATIONAL STATE OF'),
('BA', 'en', 'BOSNIA AND HERZEGOVINA'),
('BW', 'en', 'BOTSWANA'),
('BV', 'en', 'BOUVET ISLAND'),
('BR', 'en', 'BRAZIL'),
('IO', 'en', 'BRITISH INDIAN OCEAN TERRITORY'),
('BN', 'en', 'BRUNEI DARUSSALAM'),
('BG', 'en', 'BULGARIA'),
('BF', 'en', 'BURKINA FASO'),
('BI', 'en', 'BURUNDI'),
('KH', 'en', 'CAMBODIA'),
('CM', 'en', 'CAMEROON'),
('CA', 'en', 'CANADA'),
('CV', 'en', 'CAPE VERDE'),
('KY', 'en', 'CAYMAN ISLANDS'),
('CF', 'en', 'CENTRAL AFRICAN REPUBLIC'),
('TD', 'en', 'CHAD'),
('CL', 'en', 'CHILE'),
('CN', 'en', 'CHINA'),
('CX', 'en', 'CHRISTMAS ISLAND'),
('CC', 'en', 'COCOS (KEELING) ISLANDS'),
('CO', 'en', 'COLOMBIA'),
('KM', 'en', 'COMOROS'),
('CG', 'en', 'CONGO'),
('CD', 'en', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE'),
('CK', 'en', 'COOK ISLANDS'),
('CR', 'en', 'COSTA RICA'),
('CI', 'en', 'CÔTE D''IVOIRE'),
('HR', 'en', 'CROATIA'),
('CU', 'en', 'CUBA'),
('CY', 'en', 'CYPRUS'),
('CZ', 'en', 'CZECH REPUBLIC'),
('DK', 'en', 'DENMARK'),
('DJ', 'en', 'DJIBOUTI'),
('DM', 'en', 'DOMINICA'),
('DO', 'en', 'DOMINICAN REPUBLIC'),
('EC', 'en', 'ECUADOR'),
('EG', 'en', 'EGYPT'),
('SV', 'en', 'EL SALVADOR'),
('GQ', 'en', 'EQUATORIAL GUINEA'),
('ER', 'en', 'ERITREA'),
('EE', 'en', 'ESTONIA'),
('ET', 'en', 'ETHIOPIA'),
('FK', 'en', 'FALKLAND ISLANDS (MALVINAS)'),
('FO', 'en', 'FAROE ISLANDS'),
('FJ', 'en', 'FIJI'),
('FI', 'en', 'FINLAND'),
('FR', 'en', 'FRANCE'),
('GF', 'en', 'FRENCH GUIANA'),
('PF', 'en', 'FRENCH POLYNESIA'),
('TF', 'en', 'FRENCH SOUTHERN TERRITORIES'),
('GA', 'en', 'GABON'),
('GM', 'en', 'GAMBIA'),
('GE', 'en', 'GEORGIA'),
('DE', 'en', 'GERMANY'),
('GH', 'en', 'GHANA'),
('GI', 'en', 'GIBRALTAR'),
('GR', 'en', 'GREECE'),
('GL', 'en', 'GREENLAND'),
('GD', 'en', 'GRENADA'),
('GP', 'en', 'GUADELOUPE'),
('GU', 'en', 'GUAM'),
('GT', 'en', 'GUATEMALA'),
('GG', 'en', 'GUERNSEY'),
('GN', 'en', 'GUINEA'),
('GW', 'en', 'GUINEA-BISSAU'),
('GY', 'en', 'GUYANA'),
('HT', 'en', 'HAITI'),
('HM', 'en', 'HEARD ISLAND AND MCDONALD ISLANDS'),
('VA', 'en', 'HOLY SEE (VATICAN CITY STATE)'),
('HN', 'en', 'HONDURAS'),
('HK', 'en', 'HONG KONG'),
('HU', 'en', 'HUNGARY'),
('IS', 'en', 'ICELAND'),
('IN', 'en', 'INDIA'),
('ID', 'en', 'INDONESIA'),
('IR', 'en', 'IRAN, ISLAMIC REPUBLIC OF'),
('IQ', 'en', 'IRAQ'),
('IE', 'en', 'IRELAND'),
('IM', 'en', 'ISLE OF MAN'),
('IL', 'en', 'ISRAEL'),
('IT', 'en', 'ITALY'),
('JM', 'en', 'JAMAICA'),
('JP', 'en', 'JAPAN'),
('JE', 'en', 'JERSEY'),
('JO', 'en', 'JORDAN'),
('KZ', 'en', 'KAZAKHSTAN'),
('KE', 'en', 'KENYA'),
('KI', 'en', 'KIRIBATI'),
('KP', 'en', 'KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF'),
('KR', 'en', 'KOREA, REPUBLIC OF'),
('KW', 'en', 'KUWAIT'),
('KG', 'en', 'KYRGYZSTAN'),
('LA', 'en', 'LAO PEOPLE''S DEMOCRATIC REPUBLIC'),
('LV', 'en', 'LATVIA'),
('LB', 'en', 'LEBANON'),
('LS', 'en', 'LESOTHO'),
('LR', 'en', 'LIBERIA'),
('LY', 'en', 'LIBYAN ARAB JAMAHIRIYA'),
('LI', 'en', 'LIECHTENSTEIN'),
('LT', 'en', 'LITHUANIA'),
('LU', 'en', 'LUXEMBOURG'),
('MO', 'en', 'MACAO'),
('MK', 'en', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF'),
('MG', 'en', 'MADAGASCAR'),
('MW', 'en', 'MALAWI'),
('MY', 'en', 'MALAYSIA'),
('MV', 'en', 'MALDIVES'),
('ML', 'en', 'MALI'),
('MT', 'en', 'MALTA'),
('MH', 'en', 'MARSHALL ISLANDS'),
('MQ', 'en', 'MARTINIQUE'),
('MR', 'en', 'MAURITANIA'),
('MU', 'en', 'MAURITIUS'),
('YT', 'en', 'MAYOTTE'),
('MX', 'en', 'MEXICO'),
('FM', 'en', 'MICRONESIA, FEDERATED STATES OF'),
('MD', 'en', 'MOLDOVA, REPUBLIC OF'),
('MC', 'en', 'MONACO'),
('MN', 'en', 'MONGOLIA'),
('ME', 'en', 'MONTENEGRO'),
('MS', 'en', 'MONTSERRAT'),
('MA', 'en', 'MOROCCO'),
('MZ', 'en', 'MOZAMBIQUE'),
('MM', 'en', 'MYANMAR'),
('NA', 'en', 'NAMIBIA'),
('NR', 'en', 'NAURU'),
('NP', 'en', 'NEPAL'),
('NL', 'en', 'NETHERLANDS'),
('AN', 'en', 'NETHERLANDS ANTILLES'),
('NC', 'en', 'NEW CALEDONIA'),
('NZ', 'en', 'NEW ZEALAND'),
('NI', 'en', 'NICARAGUA'),
('NE', 'en', 'NIGER'),
('NG', 'en', 'NIGERIA'),
('NU', 'en', 'NIUE'),
('NF', 'en', 'NORFOLK ISLAND'),
('MP', 'en', 'NORTHERN MARIANA ISLANDS'),
('NO', 'en', 'NORWAY'),
('OM', 'en', 'OMAN'),
('PK', 'en', 'PAKISTAN'),
('PW', 'en', 'PALAU'),
('PS', 'en', 'PALESTINIAN TERRITORY, OCCUPIED'),
('PA', 'en', 'PANAMA'),
('PG', 'en', 'PAPUA NEW GUINEA'),
('PY', 'en', 'PARAGUAY'),
('PE', 'en', 'PERU'),
('PH', 'en', 'PHILIPPINES'),
('PN', 'en', 'PITCAIRN'),
('PL', 'en', 'POLAND'),
('PT', 'en', 'PORTUGAL'),
('PR', 'en', 'PUERTO RICO'),
('QA', 'en', 'QATAR'),
('RE', 'en', 'RÉUNION'),
('RO', 'en', 'ROMANIA'),
('RU', 'en', 'RUSSIAN FEDERATION'),
('RW', 'en', 'RWANDA'),
('BL', 'en', 'SAINT BARTHÉLEMY'),
('SH', 'en', 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA'),
('KN', 'en', 'SAINT KITTS AND NEVIS'),
('LC', 'en', 'SAINT LUCIA'),
('MF', 'en', 'SAINT MARTIN'),
('PM', 'en', 'SAINT PIERRE AND MIQUELON'),
('VC', 'en', 'SAINT VINCENT AND THE GRENADINES'),
('WS', 'en', 'SAMOA'),
('SM', 'en', 'SAN MARINO'),
('ST', 'en', 'SAO TOME AND PRINCIPE'),
('SA', 'en', 'SAUDI ARABIA'),
('SN', 'en', 'SENEGAL'),
('RS', 'en', 'SERBIA'),
('SC', 'en', 'SEYCHELLES'),
('SL', 'en', 'SIERRA LEONE'),
('SG', 'en', 'SINGAPORE'),
('SK', 'en', 'SLOVAKIA'),
('SI', 'en', 'SLOVENIA'),
('SB', 'en', 'SOLOMON ISLANDS'),
('SO', 'en', 'SOMALIA'),
('ZA', 'en', 'SOUTH AFRICA'),
('GS', 'en', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS'),
('ES', 'en', 'SPAIN'),
('LK', 'en', 'SRI LANKA'),
('SD', 'en', 'SUDAN'),
('SR', 'en', 'SURINAME'),
('SJ', 'en', 'SVALBARD AND JAN MAYEN'),
('SZ', 'en', 'SWAZILAND'),
('SE', 'en', 'SWEDEN'),
('CH', 'en', 'SWITZERLAND'),
('SY', 'en', 'SYRIAN ARAB REPUBLIC'),
('TW', 'en', 'TAIWAN, PROVINCE OF CHINA'),
('TJ', 'en', 'TAJIKISTAN'),
('TZ', 'en', 'TANZANIA, UNITED REPUBLIC OF'),
('TH', 'en', 'THAILAND'),
('TL', 'en', 'TIMOR-LESTE'),
('TG', 'en', 'TOGO'),
('TK', 'en', 'TOKELAU'),
('TO', 'en', 'TONGA'),
('TT', 'en', 'TRINIDAD AND TOBAGO'),
('TN', 'en', 'TUNISIA'),
('TR', 'en', 'TURKEY'),
('TM', 'en', 'TURKMENISTAN'),
('TC', 'en', 'TURKS AND CAICOS ISLANDS'),
('TV', 'en', 'TUVALU'),
('UG', 'en', 'UGANDA'),
('UA', 'en', 'UKRAINE'),
('AE', 'en', 'UNITED ARAB EMIRATES'),
('GB', 'en', 'UNITED KINGDOM'),
('US', 'en', 'UNITED STATES'),
('UM', 'en', 'UNITED STATES MINOR OUTLYING ISLANDS'),
('UY', 'en', 'URUGUAY'),
('UZ', 'en', 'UZBEKISTAN'),
('VU', 'en', 'VANUATU'),
('VE', 'en', 'VENEZUELA, BOLIVARIAN REPUBLIC OF'),
('VN', 'en', 'VIET NAM'),
('VG', 'en', 'VIRGIN ISLANDS, BRITISH'),
('VI', 'en', 'VIRGIN ISLANDS, U.S.'),
('WF', 'en', 'WALLIS AND FUTUNA'),
('EH', 'en', 'WESTERN SAHARA'),
('YE', 'en', 'YEMEN'),
('ZM', 'en', 'ZAMBIA'),
('ZW', 'en', 'ZIMBABWE'),
('AF', 'fr', 'AFGHANISTAN'),
('ZA', 'fr', 'AFRIQUE DU SUD'),
('AX', 'fr', 'ÅLAND, ÎLES'),
('AL', 'fr', 'ALBANIE'),
('DZ', 'fr', 'ALGÉRIE'),
('DE', 'fr', 'ALLEMAGNE'),
('AD', 'fr', 'ANDORRE'),
('AO', 'fr', 'ANGOLA'),
('AI', 'fr', 'ANGUILLA'),
('AQ', 'fr', 'ANTARCTIQUE'),
('AG', 'fr', 'ANTIGUA-ET-BARBUDA'),
('AN', 'fr', 'ANTILLES NÉERLANDAISES'),
('SA', 'fr', 'ARABIE SAOUDITE'),
('AR', 'fr', 'ARGENTINE'),
('AM', 'fr', 'ARMÉNIE'),
('AW', 'fr', 'ARUBA'),
('AU', 'fr', 'AUSTRALIE'),
('AT', 'fr', 'AUTRICHE'),
('AZ', 'fr', 'AZERBAÏDJAN'),
('BS', 'fr', 'BAHAMAS'),
('BH', 'fr', 'BAHREÏN'),
('BD', 'fr', 'BANGLADESH'),
('BB', 'fr', 'BARBADE'),
('BY', 'fr', 'BÉLARUS'),
('BE', 'fr', 'BELGIQUE'),
('BZ', 'fr', 'BELIZE'),
('BJ', 'fr', 'BÉNIN'),
('BM', 'fr', 'BERMUDES'),
('BT', 'fr', 'BHOUTAN'),
('BO', 'fr', 'BOLIVIE, l''ÉTAT PLURINATIONAL DE'),
('BA', 'fr', 'BOSNIE-HERZÉGOVINE'),
('BW', 'fr', 'BOTSWANA'),
('BV', 'fr', 'BOUVET, ÎLE'),
('BR', 'fr', 'BRÉSIL'),
('BN', 'fr', 'BRUNÉI DARUSSALAM'),
('BG', 'fr', 'BULGARIE'),
('BF', 'fr', 'BURKINA FASO'),
('BI', 'fr', 'BURUNDI'),
('KY', 'fr', 'CAÏMANES, ÎLES'),
('KH', 'fr', 'CAMBODGE'),
('CM', 'fr', 'CAMEROUN'),
('CA', 'fr', 'CANADA'),
('CV', 'fr', 'CAP-VERT'),
('CF', 'fr', 'CENTRAFRICAINE, RÉPUBLIQUE'),
('CL', 'fr', 'CHILI'),
('CN', 'fr', 'CHINE'),
('CX', 'fr', 'CHRISTMAS, ÎLE'),
('CY', 'fr', 'CHYPRE'),
('CC', 'fr', 'COCOS (KEELING), ÎLES'),
('CO', 'fr', 'COLOMBIE'),
('KM', 'fr', 'COMORES'),
('CG', 'fr', 'CONGO'),
('CD', 'fr', 'CONGO, LA RÉPUBLIQUE DÉMOCRATIQUE DU'),
('CK', 'fr', 'COOK, ÎLES'),
('KR', 'fr', 'CORÉE, RÉPUBLIQUE DE'),
('KP', 'fr', 'CORÉE, RÉPUBLIQUE POPULAIRE DÉMOCRATIQUE DE'),
('CR', 'fr', 'COSTA RICA'),
('CI', 'fr', 'CÔTE D''IVOIRE'),
('HR', 'fr', 'CROATIE'),
('CU', 'fr', 'CUBA'),
('DK', 'fr', 'DANEMARK'),
('DJ', 'fr', 'DJIBOUTI'),
('DO', 'fr', 'DOMINICAINE, RÉPUBLIQUE'),
('DM', 'fr', 'DOMINIQUE'),
('EG', 'fr', 'ÉGYPTE'),
('SV', 'fr', 'EL SALVADOR'),
('AE', 'fr', 'ÉMIRATS ARABES UNIS'),
('EC', 'fr', 'ÉQUATEUR'),
('ER', 'fr', 'ÉRYTHRÉE'),
('ES', 'fr', 'ESPAGNE'),
('EE', 'fr', 'ESTONIE'),
('US', 'fr', 'ÉTATS-UNIS'),
('ET', 'fr', 'ÉTHIOPIE'),
('FK', 'fr', 'FALKLAND, ÎLES (MALVINAS)'),
('FO', 'fr', 'FÉROÉ, ÎLES'),
('FJ', 'fr', 'FIDJI'),
('FI', 'fr', 'FINLANDE'),
('FR', 'fr', 'FRANCE'),
('GA', 'fr', 'GABON'),
('GM', 'fr', 'GAMBIE'),
('GE', 'fr', 'GÉORGIE'),
('GS', 'fr', 'GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD'),
('GH', 'fr', 'GHANA'),
('GI', 'fr', 'GIBRALTAR'),
('GR', 'fr', 'GRÈCE'),
('GD', 'fr', 'GRENADE'),
('GL', 'fr', 'GROENLAND'),
('GP', 'fr', 'GUADELOUPE'),
('GU', 'fr', 'GUAM'),
('GT', 'fr', 'GUATEMALA'),
('GG', 'fr', 'GUERNESEY'),
('GN', 'fr', 'GUINÉE'),
('GW', 'fr', 'GUINÉE-BISSAU'),
('GQ', 'fr', 'GUINÉE ÉQUATORIALE'),
('GY', 'fr', 'GUYANA'),
('GF', 'fr', 'GUYANE FRANÇAISE'),
('HT', 'fr', 'HAÏTI'),
('HM', 'fr', 'HEARD, ÎLE ET MCDONALD, ÎLES'),
('HN', 'fr', 'HONDURAS'),
('HK', 'fr', 'HONG-KONG'),
('HU', 'fr', 'HONGRIE'),
('IM', 'fr', 'ÎLE DE MAN'),
('UM', 'fr', 'ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS'),
('VG', 'fr', 'ÎLES VIERGES BRITANNIQUES'),
('VI', 'fr', 'ÎLES VIERGES DES ÉTATS-UNIS'),
('IN', 'fr', 'INDE'),
('ID', 'fr', 'INDONÉSIE'),
('IR', 'fr', 'IRAN, RÉPUBLIQUE ISLAMIQUE D'''),
('IQ', 'fr', 'IRAQ'),
('IE', 'fr', 'IRLANDE'),
('IS', 'fr', 'ISLANDE'),
('IL', 'fr', 'ISRAËL'),
('IT', 'fr', 'ITALIE'),
('JM', 'fr', 'JAMAÏQUE'),
('JP', 'fr', 'JAPON'),
('JE', 'fr', 'JERSEY'),
('JO', 'fr', 'JORDANIE'),
('KZ', 'fr', 'KAZAKHSTAN'),
('KE', 'fr', 'KENYA'),
('KG', 'fr', 'KIRGHIZISTAN'),
('KI', 'fr', 'KIRIBATI'),
('KW', 'fr', 'KOWEÏT'),
('LA', 'fr', 'LAO, RÉPUBLIQUE DÉMOCRATIQUE POPULAIRE'),
('LS', 'fr', 'LESOTHO'),
('LV', 'fr', 'LETTONIE'),
('LB', 'fr', 'LIBAN'),
('LR', 'fr', 'LIBÉRIA'),
('LY', 'fr', 'LIBYENNE, JAMAHIRIYA ARABE'),
('LI', 'fr', 'LIECHTENSTEIN'),
('LT', 'fr', 'LITUANIE'),
('LU', 'fr', 'LUXEMBOURG'),
('MO', 'fr', 'MACAO'),
('MK', 'fr', 'MACÉDOINE, L''EX-RÉPUBLIQUE YOUGOSLAVE DE'),
('MG', 'fr', 'MADAGASCAR'),
('MY', 'fr', 'MALAISIE'),
('MW', 'fr', 'MALAWI'),
('MV', 'fr', 'MALDIVES'),
('ML', 'fr', 'MALI'),
('MT', 'fr', 'MALTE'),
('MP', 'fr', 'MARIANNES DU NORD, ÎLES'),
('MA', 'fr', 'MAROC'),
('MH', 'fr', 'MARSHALL, ÎLES'),
('MQ', 'fr', 'MARTINIQUE'),
('MU', 'fr', 'MAURICE'),
('MR', 'fr', 'MAURITANIE'),
('YT', 'fr', 'MAYOTTE'),
('MX', 'fr', 'MEXIQUE'),
('FM', 'fr', 'MICRONÉSIE, ÉTATS FÉDÉRÉS DE'),
('MD', 'fr', 'MOLDOVA, RÉPUBLIQUE DE'),
('MC', 'fr', 'MONACO'),
('MN', 'fr', 'MONGOLIE'),
('ME', 'fr', 'MONTÉNÉGRO'),
('MS', 'fr', 'MONTSERRAT'),
('MZ', 'fr', 'MOZAMBIQUE'),
('MM', 'fr', 'MYANMAR'),
('NA', 'fr', 'NAMIBIE'),
('NR', 'fr', 'NAURU'),
('NP', 'fr', 'NÉPAL'),
('NI', 'fr', 'NICARAGUA'),
('NE', 'fr', 'NIGER'),
('NG', 'fr', 'NIGÉRIA'),
('NU', 'fr', 'NIUÉ'),
('NF', 'fr', 'NORFOLK, ÎLE'),
('NO', 'fr', 'NORVÈGE'),
('NC', 'fr', 'NOUVELLE-CALÉDONIE'),
('NZ', 'fr', 'NOUVELLE-ZÉLANDE'),
('IO', 'fr', 'OCÉAN INDIEN, TERRITOIRE BRITANNIQUE DE L'''),
('OM', 'fr', 'OMAN'),
('UG', 'fr', 'OUGANDA'),
('UZ', 'fr', 'OUZBÉKISTAN'),
('PK', 'fr', 'PAKISTAN'),
('PW', 'fr', 'PALAOS'),
('PS', 'fr', 'PALESTINIEN OCCUPÉ, TERRITOIRE'),
('PA', 'fr', 'PANAMA'),
('PG', 'fr', 'PAPOUASIE-NOUVELLE-GUINÉE'),
('PY', 'fr', 'PARAGUAY'),
('NL', 'fr', 'PAYS-BAS'),
('PE', 'fr', 'PÉROU'),
('PH', 'fr', 'PHILIPPINES'),
('PN', 'fr', 'PITCAIRN'),
('PL', 'fr', 'POLOGNE'),
('PF', 'fr', 'POLYNÉSIE FRANÇAISE'),
('PR', 'fr', 'PORTO RICO'),
('PT', 'fr', 'PORTUGAL'),
('QA', 'fr', 'QATAR'),
('RE', 'fr', 'RÉUNION'),
('RO', 'fr', 'ROUMANIE'),
('GB', 'fr', 'ROYAUME-UNI'),
('RU', 'fr', 'RUSSIE, FÉDÉRATION DE'),
('RW', 'fr', 'RWANDA'),
('EH', 'fr', 'SAHARA OCCIDENTAL'),
('BL', 'fr', 'SAINT-BARTHÉLEMY'),
('SH', 'fr', 'SAINTE-HÉLÈNE, ASCENSION ET TRISTAN DA CUNHA'),
('LC', 'fr', 'SAINTE-LUCIE'),
('KN', 'fr', 'SAINT-KITTS-ET-NEVIS'),
('SM', 'fr', 'SAINT-MARIN'),
('MF', 'fr', 'SAINT-MARTIN'),
('PM', 'fr', 'SAINT-PIERRE-ET-MIQUELON'),
('VA', 'fr', 'SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)'),
('VC', 'fr', 'SAINT-VINCENT-ET-LES GRENADINES'),
('SB', 'fr', 'SALOMON, ÎLES'),
('WS', 'fr', 'SAMOA'),
('AS', 'fr', 'SAMOA AMÉRICAINES'),
('ST', 'fr', 'SAO TOMÉ-ET-PRINCIPE'),
('SN', 'fr', 'SÉNÉGAL'),
('RS', 'fr', 'SERBIE'),
('SC', 'fr', 'SEYCHELLES'),
('SL', 'fr', 'SIERRA LEONE'),
('SG', 'fr', 'SINGAPOUR'),
('SK', 'fr', 'SLOVAQUIE'),
('SI', 'fr', 'SLOVÉNIE'),
('SO', 'fr', 'SOMALIE'),
('SD', 'fr', 'SOUDAN'),
('LK', 'fr', 'SRI LANKA'),
('SE', 'fr', 'SUÈDE'),
('CH', 'fr', 'SUISSE'),
('SR', 'fr', 'SURINAME'),
('SJ', 'fr', 'SVALBARD ET ÎLE JAN MAYEN'),
('SZ', 'fr', 'SWAZILAND'),
('SY', 'fr', 'SYRIENNE, RÉPUBLIQUE ARABE'),
('TJ', 'fr', 'TADJIKISTAN'),
('TW', 'fr', 'TAÏWAN, PROVINCE DE CHINE'),
('TZ', 'fr', 'TANZANIE, RÉPUBLIQUE-UNIE DE'),
('TD', 'fr', 'TCHAD'),
('CZ', 'fr', 'TCHÈQUE, RÉPUBLIQUE'),
('TF', 'fr', 'TERRES AUSTRALES FRANÇAISES'),
('TH', 'fr', 'THAÏLANDE'),
('TL', 'fr', 'TIMOR-LESTE'),
('TG', 'fr', 'TOGO'),
('TK', 'fr', 'TOKELAU'),
('TO', 'fr', 'TONGA'),
('TT', 'fr', 'TRINITÉ-ET-TOBAGO'),
('TN', 'fr', 'TUNISIE'),
('TM', 'fr', 'TURKMÉNISTAN'),
('TC', 'fr', 'TURKS ET CAÏQUES, ÎLES'),
('TR', 'fr', 'TURQUIE'),
('TV', 'fr', 'TUVALU'),
('UA', 'fr', 'UKRAINE'),
('UY', 'fr', 'URUGUAY'),
('VU', 'fr', 'VANUATU'),
('VE', 'fr', 'VENEZUELA, RÉPUBLIQUE BOLIVARIENNE DU'),
('VN', 'fr', 'VIET NAM'),
('WF', 'fr', 'WALLIS ET FUTUNA'),
('YE', 'fr', 'YÉMEN'),
('ZM', 'fr', 'ZAMBIE'),
('ZW', 'fr', 'ZIMBABWE');

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`code`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`code`, `name`, `active`) VALUES
('AED', 'United Arab Emirates, Dirhams', 0),
('AFN', 'Afghanistan, Afghanis', 0),
('ALL', 'Albania, Leke', 0),
('AMD', 'Armenia, Drams', 0),
('ANG', 'Netherlands Antilles, Guilders (also called Florins)', 0),
('AOA', 'Angola, Kwanza', 0),
('ARS', 'Argentina, Pesos', 0),
('AUD', 'Australia, Dollars', 0),
('AWG', 'Aruba, Guilders (also called Florins)', 0),
('AZN', 'Azerbaijan, New Manats', 0),
('BAM', 'Bosnia and Herzegovina, Convertible Marka', 0),
('BBD', 'Barbados, Dollars', 0),
('BDT', 'Bangladesh, Taka', 0),
('BGN', 'Bulgaria, Leva', 0),
('BHD', 'Bahrain, Dinars', 0),
('BIF', 'Burundi, Francs', 0),
('BMD', 'Bermuda, Dollars', 0),
('BND', 'Brunei Darussalam, Dollars', 0),
('BOB', 'Bolivia, Bolivianos', 0),
('BRL', 'Brazil, Brazil Real', 0),
('BSD', 'Bahamas, Dollars', 0),
('BTN', 'Bhutan, Ngultrum', 0),
('BWP', 'Botswana, Pulas', 0),
('BYR', 'Belarus, Rubles', 0),
('BZD', 'Belize, Dollars', 0),
('CAD', 'Canada, Dollars', 1),
('CDF', 'Congo/Kinshasa, Congolese Francs', 0),
('CHF', 'Switzerland, Francs', 0),
('CLP', 'Chile, Pesos', 0),
('CNY', 'China, Yuan Renminbi', 0),
('COP', 'Colombia, Pesos', 0),
('CRC', 'Costa Rica, Colones', 0),
('CUP', 'Cuba, Pesos', 0),
('CVE', 'Cape Verde, Escudos', 0),
('CZK', 'Czech Republic, Koruny', 0),
('DJF', 'Djibouti, Francs', 0),
('DKK', 'Denmark, Kroner', 0),
('DOP', 'Dominican Republic, Pesos', 0),
('DZD', 'Algeria, Algeria Dinars', 0),
('EEK', 'Estonia, Krooni', 0),
('EGP', 'Egypt, Pounds', 0),
('ERN', 'Eritrea, Nakfa', 0),
('ETB', 'Ethiopia, Birr', 0),
('EUR', 'Euro Member Countries, Euro', 0),
('FJD', 'Fiji, Dollars', 0),
('FKP', 'Falkland Islands (Malvinas), Pounds', 0),
('GBP', 'United Kingdom, Pounds', 0),
('GEL', 'Georgia, Lari', 0),
('GGP', 'Guernsey, Pounds', 0),
('GHS', 'Ghana, Cedis', 0),
('GIP', 'Gibraltar, Pounds', 0),
('GMD', 'Gambia, Dalasi', 0),
('GNF', 'Guinea, Francs', 0),
('GTQ', 'Guatemala, Quetzales', 0),
('GYD', 'Guyana, Dollars', 0),
('HKD', 'Hong Kong, Dollars', 0),
('HNL', 'Honduras, Lempiras', 0),
('HRK', 'Croatia, Kuna', 0),
('HTG', 'Haiti, Gourdes', 0),
('HUF', 'Hungary, Forint', 0),
('IDR', 'Indonesia, Rupiahs', 0),
('ILS', 'Israel, New Shekels', 0),
('IMP', 'Isle of Man, Pounds', 0),
('INR', 'India, Rupees', 0),
('IQD', 'Iraq, Dinars', 0),
('IRR', 'Iran, Rials', 0),
('ISK', 'Iceland, Kronur', 0),
('JEP', 'Jersey, Pounds', 0),
('JMD', 'Jamaica, Dollars', 0),
('JOD', 'Jordan, Dinars', 0),
('JPY', 'Japan, Yen', 0),
('KES', 'Kenya, Shillings', 0),
('KGS', 'Kyrgyzstan, Soms', 0),
('KHR', 'Cambodia, Riels', 0),
('KMF', 'Comoros, Francs', 0),
('KPW', 'Korea (North), Won', 0),
('KRW', 'Korea (South), Won', 0),
('KWD', 'Kuwait, Dinars', 0),
('KYD', 'Cayman Islands, Dollars', 0),
('KZT', 'Kazakhstan, Tenge', 0),
('LAK', 'Laos, Kips', 0),
('LBP', 'Lebanon, Pounds', 0),
('LKR', 'Sri Lanka, Rupees', 0),
('LRD', 'Liberia, Dollars', 0),
('LSL', 'Lesotho, Maloti', 0),
('LTL', 'Lithuania, Litai', 0),
('LVL', 'Latvia, Lati', 0),
('LYD', 'Libya, Dinars', 0),
('MAD', 'Morocco, Dirhams', 0),
('MDL', 'Moldova, Lei', 0),
('MGA', 'Madagascar, Ariary', 0),
('MKD', 'Macedonia, Denars', 0),
('MMK', 'Myanmar (Burma), Kyats', 0),
('MNT', 'Mongolia, Tugriks', 0),
('MOP', 'Macau, Patacas', 0),
('MRO', 'Mauritania, Ouguiyas', 0),
('MUR', 'Mauritius, Rupees', 0),
('MVR', 'Maldives (Maldive Islands), Rufiyaa', 0),
('MWK', 'Malawi, Kwachas', 0),
('MXN', 'Mexico, Pesos', 0),
('MYR', 'Malaysia, Ringgits', 0),
('MZN', 'Mozambique, Meticais', 0),
('NAD', 'Namibia, Dollars', 0),
('NGN', 'Nigeria, Nairas', 0),
('NIO', 'Nicaragua, Cordobas', 0),
('NOK', 'Norway, Krone', 0),
('NPR', 'Nepal, Nepal Rupees', 0),
('NZD', 'New Zealand, Dollars', 0),
('OMR', 'Oman, Rials', 0),
('PAB', 'Panama, Balboa', 0),
('PEN', 'Peru, Nuevos Soles', 0),
('PGK', 'Papua New Guinea, Kina', 0),
('PHP', 'Philippines, Pesos', 0),
('PKR', 'Pakistan, Rupees', 0),
('PLN', 'Poland, Zlotych', 0),
('PYG', 'Paraguay, Guarani', 0),
('QAR', 'Qatar, Rials', 0),
('RON', 'Romania, New Lei', 0),
('RSD', 'Serbia, Dinars', 0),
('RUB', 'Russia, Rubles', 0),
('RWF', 'Rwanda, Rwanda Francs', 0),
('SAR', 'Saudi Arabia, Riyals', 0),
('SBD', 'Solomon Islands, Dollars', 0),
('SCR', 'Seychelles, Rupees', 0),
('SDG', 'Sudan, Pounds', 0),
('SEK', 'Sweden, Kronor', 0),
('SGD', 'Singapore, Dollars', 0),
('SHP', 'Saint Helena, Pounds', 0),
('SLL', 'Sierra Leone, Leones', 0),
('SOS', 'Somalia, Shillings', 0),
('SPL', 'Seborga, Luigini', 0),
('SRD', 'Suriname, Dollars', 0),
('STD', 'S', 0),
('SVC', 'El Salvador, Colones', 0),
('SYP', 'Syria, Pounds', 0),
('SZL', 'Swaziland, Emalangeni', 0),
('THB', 'Thailand, Baht', 0),
('TJS', 'Tajikistan, Somoni', 0),
('TMM', 'Turkmenistan, Manats', 0),
('TND', 'Tunisia, Dinars', 0),
('TOP', 'Tonga, Pa''anga', 0),
('TRY', 'Turkey, New Lira', 0),
('TTD', 'Trinidad and Tobago, Dollars', 0),
('TVD', 'Tuvalu, Tuvalu Dollars', 0),
('TWD', 'Taiwan, New Dollars', 0),
('TZS', 'Tanzania, Shillings', 0),
('UAH', 'Ukraine, Hryvnia', 0),
('UGX', 'Uganda, Shillings', 0),
('USD', 'United States of America, Dollars', 1),
('UYU', 'Uruguay, Pesos', 0),
('UZS', 'Uzbekistan, Sums', 0),
('VEF', 'Venezuela, Bolivares Fuertes', 0),
('VND', 'Viet Nam, Dong', 0),
('VUV', 'Vanuatu, Vatu', 0),
('WST', 'Samoa, Tala', 0),
('XAF', 'Communaut', 0),
('XAG', 'Silver, Ounces', 0),
('XAU', 'Gold, Ounces', 0),
('XCD', 'East Caribbean Dollars', 0),
('XDR', 'International Monetary Fund (IMF) Special Drawing Rights', 0),
('XOF', 'Communaut', 0),
('XPD', 'Palladium Ounces', 0),
('XPF', 'Comptoirs Fran', 0),
('XPT', 'Platinum, Ounces', 0),
('YER', 'Yemen, Rials', 0),
('ZAR', 'South Africa, Rand', 0),
('ZMK', 'Zambia, Kwacha', 0),
('ZWD', 'Zimbabwe, Zimbabwe Dollars', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer_type` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `gender` tinyint(1) unsigned NOT NULL,
  `tax_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `activation_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_confirmed` datetime NOT NULL,
  `remember_me_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `reset_password_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `sendmail_failed` tinyint(1) unsigned NOT NULL,
  `lastlogin` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_customer_type` (`id_customer_type`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `email` (`email`),
  KEY `dob` (`dob`),
  KEY `gender` (`gender`),
  KEY `activation_key` (`activation_key`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `active` (`active`),
  KEY `reset_password_key` (`reset_password_key`),
  KEY `remember_me_key` (`remember_me_key`),
  KEY `id_user_created` (`id_user_created`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--

CREATE TABLE IF NOT EXISTS `customer_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'billing or shipping',
  `use_in` tinyint(1) unsigned NOT NULL COMMENT '0 = All, 1= Billing Only, 2 = Shipping Only',
  `id_customer` int(10) unsigned NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `lat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lng` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `default_billing` tinyint(1) unsigned NOT NULL,
  `default_shipping` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_customer` (`id_customer`),
  KEY `default_shipping` (`default_shipping`),
  KEY `default_billing` (`default_billing`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `company` (`company`),
  KEY `address` (`address`),
  KEY `city` (`city`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `zip` (`zip`),
  KEY `telephone` (`telephone`),
  KEY `fax` (`fax`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `use_in` (`use_in`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_courses_scorm`
--

CREATE TABLE IF NOT EXISTS `customer_courses_scorm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `id_course` int(10) unsigned NOT NULL,
  `score` smallint(5) unsigned NOT NULL,
  `lesson_status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_customer` (`id_customer`,`id_course`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_custom_fields_value`
--

CREATE TABLE IF NOT EXISTS `customer_custom_fields_value` (
  `id_customer` int(10) unsigned NOT NULL,
  `id_custom_fields` int(10) unsigned NOT NULL,
  `id_custom_fields_option` int(10) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_custom_fields` (`id_custom_fields`,`id_custom_fields_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_price_alert`
--

CREATE TABLE IF NOT EXISTS `customer_price_alert` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL COMMENT '0 = any price reduction, 1 - price range',
  `original_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `last_updated_price` decimal(13,2) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_customer` (`id_customer`),
  KEY `id_product` (`id_product`),
  KEY `id_product_variant` (`id_product_variant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_type`
--

CREATE TABLE IF NOT EXISTS `customer_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `percent_discount` tinyint(1) unsigned NOT NULL,
  `taxable` tinyint(1) unsigned NOT NULL,
  `apply_on_rebate` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `taxable` (`taxable`),
  KEY `percent_discount` (`percent_discount`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_user_created` (`id_user_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wishlist`
--

CREATE TABLE IF NOT EXISTS `customer_wishlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `public` tinyint(1) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`),
  KEY `id_customer` (`id_customer`),
  KEY `id_customer_2` (`id_customer`,`public`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wishlist_product`
--

CREATE TABLE IF NOT EXISTS `customer_wishlist_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer_wishlist` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `sort_order` smallint(1) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_customer_wishlist` (`id_customer_wishlist`),
  KEY `id_product` (`id_product`),
  KEY `id_product_variant` (`id_product_variant`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE IF NOT EXISTS `custom_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form` tinyint(1) unsigned NOT NULL COMMENT '0 = account creation, 1 = contact',
  `type` tinyint(1) unsigned NOT NULL COMMENT '0 = checkbox, 1 = multiple checkbox, 2 = dropdown, 3 = textinput, 4 = textarea, 5 = radio button',
  `required` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `form` (`form`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `custom_fields`
--

INSERT INTO `custom_fields` (`id`, `form`, `type`, `required`, `sort_order`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 1, 2, 1, 1, 113, 0, '2014-03-21 15:50:48', '2014-03-21 19:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_description`
--

CREATE TABLE IF NOT EXISTS `custom_fields_description` (
  `id_custom_fields` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_custom_fields` (`id_custom_fields`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `custom_fields_description`
--

INSERT INTO `custom_fields_description` (`id_custom_fields`, `language_code`, `name`, `description`) VALUES
(1, 'fr', 'Département', ''),
(1, 'en', 'Department', '');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_option`
--

CREATE TABLE IF NOT EXISTS `custom_fields_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_custom_fields` int(10) unsigned NOT NULL,
  `add_extra` tinyint(1) unsigned NOT NULL,
  `extra_required` tinyint(1) unsigned NOT NULL,
  `selected` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `custom_fields_option`
--

INSERT INTO `custom_fields_option` (`id`, `id_custom_fields`, `add_extra`, `extra_required`, `selected`, `sort_order`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 1, 0, 0, 0, 1, 113, 0, '2014-03-21 15:51:21', '2014-03-21 19:51:21'),
(2, 1, 0, 0, 0, 2, 113, 0, '2014-03-21 15:51:34', '2014-03-21 19:51:34'),
(3, 1, 0, 0, 0, 3, 113, 0, '2014-03-21 15:51:52', '2014-03-21 19:51:52'),
(4, 1, 0, 0, 0, 4, 113, 0, '2014-03-21 15:52:08', '2014-03-21 19:52:08'),
(5, 1, 0, 0, 0, 5, 113, 0, '2014-03-21 15:52:24', '2014-03-21 19:52:24'),
(6, 1, 0, 0, 0, 6, 113, 0, '2014-03-21 15:52:33', '2014-03-21 19:52:33'),
(7, 1, 0, 0, 0, 7, 113, 0, '2014-03-21 15:53:05', '2014-03-21 19:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_option_description`
--

CREATE TABLE IF NOT EXISTS `custom_fields_option_description` (
  `id_custom_fields_option` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_custom_fields_option` (`id_custom_fields_option`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `custom_fields_option_description`
--

INSERT INTO `custom_fields_option_description` (`id_custom_fields_option`, `language_code`, `name`) VALUES
(1, 'fr', 'Location'),
(1, 'en', 'Location'),
(2, 'fr', 'Pièces'),
(2, 'en', 'Parts'),
(3, 'fr', 'Boutique'),
(3, 'en', 'Store'),
(4, 'fr', 'Moto neuve'),
(4, 'en', 'New Bike'),
(5, 'fr', 'Moto usagée'),
(5, 'en', 'Used Bike'),
(6, 'fr', 'Service'),
(6, 'en', 'Service'),
(7, 'fr', 'Information générale'),
(7, 'en', 'General Information');

-- --------------------------------------------------------

--
-- Table structure for table `export_tpl`
--

CREATE TABLE IF NOT EXISTS `export_tpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `export_tpl_columns`
--

CREATE TABLE IF NOT EXISTS `export_tpl_columns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_export_tpl` int(10) unsigned NOT NULL,
  `id_export_columns` int(10) unsigned NOT NULL,
  `extra` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `export_tpl_files`
--

CREATE TABLE IF NOT EXISTS `export_tpl_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_export_tpl` int(10) unsigned NOT NULL,
  `filters` text COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gift_certificate`
--

CREATE TABLE IF NOT EXISTS `gift_certificate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `person_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `person_address` text COLLATE utf8_unicode_ci NOT NULL,
  `person_email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `person_message` text COLLATE utf8_unicode_ci NOT NULL,
  `shipping_method` tinyint(1) unsigned NOT NULL COMMENT '0 = Email, 1 = Post Office',
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `sent` tinyint(1) unsigned NOT NULL,
  `date_sent` datetime NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `price` (`price`),
  KEY `date_created` (`date_created`),
  KEY `date_modified` (`date_modified`),
  KEY `id_customer` (`id_customer`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `import_tpl`
--

CREATE TABLE IF NOT EXISTS `import_tpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0 = Add Product, 1 = Add and Update Product, 2 = Update Product',
  `subtract_qty` tinyint(1) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `import_tpl_columns`
--

CREATE TABLE IF NOT EXISTS `import_tpl_columns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_import_tpl` int(10) unsigned NOT NULL,
  `id_import_columns` int(10) unsigned NOT NULL,
  `extra` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_import_tpl` (`id_import_tpl`),
  KEY `id_import_columns` (`id_import_columns`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `import_tpl_files`
--

CREATE TABLE IF NOT EXISTS `import_tpl_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_import_tpl` int(10) unsigned NOT NULL,
  `type` tinyint(3) NOT NULL COMMENT '0 = Add Product, 1 = Add and Update Product, 2 = Update Product',
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pid` mediumint(8) unsigned NOT NULL,
  `columns_separated_with` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `columns_enclosed_with` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `columns_escaped_with` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `skip_first_row` tinyint(1) unsigned NOT NULL,
  `set_active` tinyint(1) NOT NULL COMMENT 'If 1 then all new product will be set to Active',
  `progress` text COLLATE utf8_unicode_ci NOT NULL,
  `errors` longtext COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) unsigned NOT NULL COMMENT '0 = idle / 1 = validating / 2 = importing / 3 = completed',
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_imported` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `default_language` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`code`),
  KEY `active` (`active`),
  KEY `default_language` (`default_language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`code`, `name`, `active`, `default_language`) VALUES
('en', 'English', 0, 0),
('fr', 'Français', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `linked_store`
--

CREATE TABLE IF NOT EXISTS `linked_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `database` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`),
  KEY `database` (`database`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_news` date NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `date_news`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, '2014-03-03', 1, 113, 113, '2014-03-03 19:34:06', '2014-03-04 00:34:06'),
(2, '2014-03-03', 1, 113, 113, '2014-03-03 19:34:53', '2014-03-04 00:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscription`
--

CREATE TABLE IF NOT EXISTS `newsletter_subscription` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language_code` (`language_code`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `newsletter_subscription`
--

INSERT INTO `newsletter_subscription` (`id`, `email`, `language_code`, `date_created`) VALUES
(1, 'gamusta@gmail.com', 'fr', '2014-03-02 14:07:45'),
(2, 'pierre@simplecommerce.com', 'fr', '2014-03-03 08:10:24'),
(3, 'dds@gmail.com', 'fr', '2014-03-06 06:57:32');

-- --------------------------------------------------------

--
-- Table structure for table `news_description`
--

CREATE TABLE IF NOT EXISTS `news_description` (
  `id_news` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `short_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_news` (`id_news`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `news_description`
--

INSERT INTO `news_description` (`id_news`, `language_code`, `name`, `short_desc`, `description`, `filename`) VALUES
(1, 'fr', 'La nouvelle Forty Eight', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n', ''),
(1, 'en', 'La nouvelle Forty Eight', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n', ''),
(2, 'fr', 'www.roughcrafts.com', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n', ''),
(2, 'en', 'www.roughcrafts.com', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sed arcu gravida, viverra ante eget, accumsan massa. Cras iaculis sapien id leo commodo venenatis...</p>\r\n', '');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_options_group` int(10) unsigned NOT NULL,
  `sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `swatch_type` tinyint(1) unsigned NOT NULL COMMENT '0 = one color, 1 = two colors, 2 = three colors, 3 = file',
  `color` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color2` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color3` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `maxlength` tinyint(1) unsigned NOT NULL,
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price_type` tinyint(1) unsigned NOT NULL COMMENT '0 = fixed, 1 = percentage',
  `price` decimal(13,2) unsigned NOT NULL,
  `special_price` decimal(13,2) unsigned NOT NULL,
  `special_price_from_date` datetime NOT NULL,
  `special_price_to_date` datetime NOT NULL,
  `track_inventory` tinyint(1) unsigned NOT NULL,
  `qty` smallint(1) NOT NULL DEFAULT '1' COMMENT 'qty in stock',
  `out_of_stock` tinyint(1) NOT NULL COMMENT 'out of stock when qty reaches',
  `notify` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `notify_qty` tinyint(1) unsigned NOT NULL COMMENT 'notify when qty reaches',
  `allow_backorders` tinyint(1) unsigned NOT NULL COMMENT 'allow customers to backorder this option, 0 = no, 1 = yes',
  `use_shipping_price` tinyint(1) unsigned NOT NULL COMMENT 'skip shipping calculation and use specified price in product_price_shipping_region, 0 = no, 1 = yes',
  `weight` decimal(10,1) unsigned NOT NULL,
  `length` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `extra_care` tinyint(1) NOT NULL COMMENT 'Add a shipping fees calculated by the shipping provider (Canpar), if the option is non-standard (dimensions or very fragile)',
  `id_tax_group` int(10) unsigned NOT NULL,
  `taxable` tinyint(1) unsigned NOT NULL,
  `in_stock` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archive` tinyint(1) unsigned NOT NULL COMMENT 'When archive, means that the option was sold so we cannot delete it so we archive it.',
  PRIMARY KEY (`id`),
  KEY `id_tax_group` (`id_tax_group`),
  KEY `id_options_group` (`id_options_group`),
  KEY `sku` (`sku`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `active` (`active`),
  KEY `archive` (`archive`),
  KEY `sort_order` (`sort_order`),
  KEY `track_inventory` (`track_inventory`),
  KEY `use_shipping_price` (`use_shipping_price`),
  KEY `notify` (`notify`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `id_options_group`, `sku`, `swatch_type`, `color`, `color2`, `color3`, `filename`, `maxlength`, `cost_price`, `price_type`, `price`, `special_price`, `special_price_from_date`, `special_price_to_date`, `track_inventory`, `qty`, `out_of_stock`, `notify`, `notify_qty`, `allow_backorders`, `use_shipping_price`, `weight`, `length`, `width`, `height`, `extra_care`, `id_tax_group`, `taxable`, `in_stock`, `sort_order`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`, `archive`) VALUES
(1, 1, '11', 0, '', '', '', '', 0, '0.00', 0, '0.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 1, 0, 0, 0, 0, 0, '0.0', 0, 0, 0, 0, 0, 0, 0, 0, 1, 113, 113, '2014-03-19 12:10:01', '2014-03-19 16:10:01', 0),
(2, 1, '11111', 0, '', '', '', '', 0, '0.00', 0, '0.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 1, 0, 0, 0, 0, 0, '0.0', 0, 0, 0, 0, 0, 0, 0, 0, 1, 113, 113, '2014-03-19 12:10:13', '2014-03-19 16:10:13', 0);

-- --------------------------------------------------------

--
-- Table structure for table `options_description`
--

CREATE TABLE IF NOT EXISTS `options_description` (
  `id_options` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_options` (`id_options`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `options_description`
--

INSERT INTO `options_description` (`id_options`, `language_code`, `name`, `description`) VALUES
(1, 'fr', 'Garantie prolongée', ''),
(1, 'en', 'Garantie prolongée', ''),
(2, 'fr', 'Garantie prolongée', ''),
(2, 'en', 'Garantie prolongée', '');

-- --------------------------------------------------------

--
-- Table structure for table `options_do_not_ship_region`
--

CREATE TABLE IF NOT EXISTS `options_do_not_ship_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_options` int(10) unsigned NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_options` (`id_options`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_options_2` (`id_options`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `options_group`
--

CREATE TABLE IF NOT EXISTS `options_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `input_type` tinyint(1) unsigned NOT NULL COMMENT '0 = dropdown, 1 = radio, 2 = swatch, 3 = checkbox, 4 = multi-select,  5 = textfield, 6 = textarea, 7 = file, 8 = date, 9 = date & time, 10 = time',
  `from_to` tinyint(1) unsigned NOT NULL,
  `maxlength` tinyint(1) unsigned NOT NULL,
  `user_defined_qty` tinyint(1) unsigned NOT NULL,
  `max_qty` smallint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archive` tinyint(1) unsigned NOT NULL COMMENT 'When archive, means that the option was sold so we cannot delete it so we archive it.',
  PRIMARY KEY (`id`),
  KEY `input_type` (`input_type`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `archive` (`archive`),
  KEY `user_defined_qty` (`user_defined_qty`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `options_group`
--

INSERT INTO `options_group` (`id`, `input_type`, `from_to`, `maxlength`, `user_defined_qty`, `max_qty`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`, `archive`) VALUES
(1, 3, 0, 0, 1, 1, 113, 113, '2014-03-19 12:08:43', '2014-03-19 16:08:43', 0);

-- --------------------------------------------------------

--
-- Table structure for table `options_group_description`
--

CREATE TABLE IF NOT EXISTS `options_group_description` (
  `id_options_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_options_group` (`id_options_group`,`language_code`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `options_group_description`
--

INSERT INTO `options_group_description` (`id_options_group`, `language_code`, `name`, `description`) VALUES
(1, 'fr', 'Assurances', ''),
(1, 'en', 'Insurance', '');

-- --------------------------------------------------------

--
-- Table structure for table `options_price_shipping_region`
--

CREATE TABLE IF NOT EXISTS `options_price_shipping_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_options` int(10) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_options` (`id_options`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_options_2` (`id_options`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `options_ship_only_region`
--

CREATE TABLE IF NOT EXISTS `options_ship_only_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_options` int(10) unsigned NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_options` (`id_options`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_options_2` (`id_options`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `id_customer_type` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `date_order` datetime NOT NULL,
  `date_payment` date NOT NULL COMMENT 'If payment method = 2(check), or payment method = 5(cash) we give to the admin the possibility to enter a date else the date is enter automatically. ',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_tax_rule` int(10) unsigned NOT NULL,
  `billing_id` int(10) unsigned NOT NULL,
  `billing_firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `billing_city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `billing_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `billing_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `billing_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `billing_telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `billing_fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_id` int(10) unsigned NOT NULL,
  `shipping_firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `subtotal` decimal(13,2) unsigned NOT NULL,
  `local_pickup` tinyint(1) unsigned NOT NULL,
  `local_pickup_id` int(11) NOT NULL,
  `local_pickup_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `local_pickup_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `free_shipping` tinyint(1) unsigned NOT NULL,
  `shipping_gateway_company` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_service` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shipping` decimal(13,2) NOT NULL COMMENT 'Combine Shipping Gateway + Shipping by product',
  `shipping_estimated` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `taxes` decimal(13,2) unsigned NOT NULL,
  `total` decimal(13,2) unsigned NOT NULL,
  `gift_certificates` decimal(13,2) unsigned NOT NULL,
  `grand_total` decimal(13,2) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '-1 cancelled,  0 incomplete,  1 pending,  2 payment review,  3 suspected fraud,  4 declined,  5 processing,  6 on hold, 7 completed',
  `priority` tinyint(1) unsigned NOT NULL COMMENT 'Normal = 0, Attention = 1, Urgent = 2',
  `payment_method` tinyint(1) unsigned NOT NULL COMMENT '0=Credit Card, 1=Interact, 2=Check 3= gratuit 4= paypal 5=cash',
  `transaction_details` longtext COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_customer` (`id_customer`),
  KEY `id_customer_type` (`id_customer_type`),
  KEY `language_code` (`language_code`),
  KEY `date_order` (`date_order`),
  KEY `id_tax_rule` (`id_tax_rule`),
  KEY `billing_id` (`billing_id`),
  KEY `shipping_id` (`shipping_id`),
  KEY `id_user_created` (`id_user_created`),
  KEY `status` (`status`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_comment`
--

CREATE TABLE IF NOT EXISTS `orders_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `hidden_from_customer` tinyint(1) unsigned NOT NULL,
  `read_comment` tinyint(1) unsigned NOT NULL COMMENT '0 = not read, 1 = read',
  `id_user_read` int(10) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_orders_2` (`id_orders`,`hidden_from_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_discount`
--

CREATE TABLE IF NOT EXISTS `orders_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `id_rebate_coupon` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `coupon` tinyint(1) unsigned NOT NULL COMMENT '1=Yes, 0=No',
  `coupon_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `min_cart_value` decimal(13,2) unsigned NOT NULL,
  `discount_type` tinyint(1) unsigned NOT NULL,
  `discount` decimal(13,2) unsigned NOT NULL,
  `min_qty_required` tinyint(1) unsigned NOT NULL,
  `buy_x_qty` tinyint(1) unsigned NOT NULL,
  `get_y_qty` tinyint(1) unsigned NOT NULL,
  `max_qty_allowed` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_rebate_coupon` (`id_rebate_coupon`),
  KEY `id_orders_2` (`id_orders`,`id_rebate_coupon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_discount_description`
--

CREATE TABLE IF NOT EXISTS `orders_discount_description` (
  `id_orders_discount` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_orders_discount` (`id_orders_discount`),
  KEY `language_code` (`language_code`),
  KEY `id_orders_discount_2` (`id_orders_discount`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_discount_item_option`
--

CREATE TABLE IF NOT EXISTS `orders_discount_item_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_option` int(10) unsigned NOT NULL,
  `id_orders_discount` int(10) unsigned NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders_item_option` (`id_orders_item_option`),
  KEY `id_orders_discount` (`id_orders_discount`),
  KEY `id_orders_item_option_2` (`id_orders_item_option`,`id_orders_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_discount_item_product`
--

CREATE TABLE IF NOT EXISTS `orders_discount_item_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_orders_discount` int(10) unsigned NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders_item_product` (`id_orders_item_product`),
  KEY `id_orders_discount` (`id_orders_discount`),
  KEY `id_orders_item_product_2` (`id_orders_item_product`,`id_orders_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_gift_certificate`
--

CREATE TABLE IF NOT EXISTS `orders_gift_certificate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(13,2) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_orders_2` (`id_orders`,`code`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item`
--

CREATE TABLE IF NOT EXISTS `orders_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `id_orders_discount` int(10) unsigned NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_orders_discount` (`id_orders_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_option`
--

CREATE TABLE IF NOT EXISTS `orders_item_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item` int(10) unsigned NOT NULL,
  `id_product_options_group` int(10) unsigned NOT NULL,
  `id_options_group` int(10) unsigned NOT NULL,
  `id_options` int(10) unsigned NOT NULL,
  `sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `sell_price` decimal(13,2) unsigned NOT NULL,
  `textfield` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `textarea` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `datetime_start` datetime NOT NULL,
  `datetime_end` datetime NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `subtotal` decimal(13,2) unsigned NOT NULL,
  `taxes` decimal(26,10) unsigned NOT NULL,
  `tax_exception` tinyint(1) unsigned NOT NULL COMMENT 'If this item use and exception for this tax. 0 = No, 1 = Yes',
  PRIMARY KEY (`id`),
  KEY `id_orders_item` (`id_orders_item`),
  KEY `id_product_options_group` (`id_product_options_group`),
  KEY `id_options_group` (`id_options_group`),
  KEY `id_options` (`id_options`),
  KEY `sku` (`sku`),
  KEY `id_orders_item_2` (`id_orders_item`,`id_options_group`,`id_options`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_option_description`
--

CREATE TABLE IF NOT EXISTS `orders_item_option_description` (
  `id_orders_item_option` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_orders_item_option` (`id_orders_item_option`),
  KEY `language_code` (`language_code`),
  KEY `id_orders_item_option_2` (`id_orders_item_option`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_option_tax`
--

CREATE TABLE IF NOT EXISTS `orders_item_option_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_option` int(10) unsigned NOT NULL,
  `id_orders_tax` int(10) unsigned NOT NULL,
  `rate` decimal(6,5) unsigned NOT NULL,
  `amount` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders_item_option` (`id_orders_item_option`),
  KEY `id_orders_tax` (`id_orders_tax`),
  KEY `id_orders_item_option_2` (`id_orders_item_option`,`id_orders_tax`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product`
--

CREATE TABLE IF NOT EXISTS `orders_item_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item` int(10) unsigned NOT NULL,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_related` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `id_product_combo_product` int(10) unsigned NOT NULL,
  `id_product_bundled_product_group` int(10) unsigned NOT NULL,
  `id_product_bundled_product_group_product` int(10) unsigned NOT NULL,
  `product_type` tinyint(1) unsigned NOT NULL,
  `used` tinyint(1) unsigned NOT NULL,
  `sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `variant_sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `sell_price` decimal(13,2) unsigned NOT NULL,
  `special_price_start_date` datetime NOT NULL,
  `special_price_end_date` datetime NOT NULL,
  `subtotal` decimal(13,2) unsigned NOT NULL,
  `taxes` decimal(13,2) unsigned NOT NULL,
  `tax_exception` tinyint(1) unsigned NOT NULL COMMENT 'If this item use and exception for this tax. 0 = No, 1 = Yes',
  `heavy_weight` tinyint(1) NOT NULL COMMENT 'If 1, then when click on add to cart, must alert a message who say that because this product is too heavy, you must contact us.',
  PRIMARY KEY (`id`),
  KEY `id_orders_item` (`id_orders_item`),
  KEY `id_orders_item_product` (`id_orders_item_product`),
  KEY `id_product` (`id_product`),
  KEY `id_product_related` (`id_product_related`),
  KEY `id_product_variant` (`id_product_variant`),
  KEY `id_product_combo_product` (`id_product_combo_product`),
  KEY `id_product_bundled_product_group` (`id_product_bundled_product_group`),
  KEY `id_product_bundled_product_group_product` (`id_product_bundled_product_group_product`),
  KEY `product_type` (`product_type`),
  KEY `sku` (`sku`),
  KEY `variant_sku` (`variant_sku`),
  KEY `id_orders_item_2` (`id_orders_item`,`id_product`,`id_product_variant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_description`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_description` (
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `variant_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_orders_item_product` (`id_orders_item_product`),
  KEY `language_code` (`language_code`),
  KEY `id_orders_item_product_2` (`id_orders_item_product`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_downloadable_files`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_downloadable_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_product_downloadable_files` int(10) unsigned NOT NULL,
  `no_days_expire` smallint(5) unsigned NOT NULL,
  `no_downloads` tinyint(3) unsigned NOT NULL,
  `current_no_downloads` smallint(5) unsigned NOT NULL,
  `sort_order` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_downloadable_files_description`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_downloadable_files_description` (
  `id_orders_item_product_downloadable_files` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_orders_item_product_downloadable_files` (`id_orders_item_product_downloadable_files`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_downloadable_videos`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_downloadable_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_product_downloadable_videos` int(10) unsigned NOT NULL,
  `embed_code` text COLLATE utf8_unicode_ci NOT NULL,
  `no_days_expire` smallint(5) unsigned NOT NULL,
  `no_downloads` tinyint(1) unsigned NOT NULL,
  `current_no_downloads` smallint(5) unsigned NOT NULL,
  `sort_order` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_downloadable_videos_description`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_downloadable_videos_description` (
  `id_orders_item_product_downloadable_videos` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_orders_item_product_downloadable_videos` (`id_orders_item_product_downloadable_videos`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_item_product_tax`
--

CREATE TABLE IF NOT EXISTS `orders_item_product_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_orders_tax` int(10) unsigned NOT NULL,
  `rate` decimal(6,5) unsigned NOT NULL,
  `amount` decimal(26,10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders_item_product` (`id_orders_item_product`),
  KEY `id_orders_tax` (`id_orders_tax`),
  KEY `id_orders_item_product_2` (`id_orders_item_product`,`id_orders_tax`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_options_group`
--

CREATE TABLE IF NOT EXISTS `orders_options_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `id_options_group` int(10) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `input_type` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_options_group` (`id_options_group`),
  KEY `input_type` (`input_type`),
  KEY `sort_order` (`sort_order`),
  KEY `id_orders_2` (`id_orders`,`id_options_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_options_group_description`
--

CREATE TABLE IF NOT EXISTS `orders_options_group_description` (
  `id_orders_options_group` int(10) unsigned NOT NULL,
  `id_options_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_orders_options_group` (`id_orders_options_group`),
  KEY `id_options_group` (`id_options_group`),
  KEY `language_code` (`language_code`),
  KEY `id_orders_options_group_2` (`id_orders_options_group`,`id_options_group`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_shipment`
--

CREATE TABLE IF NOT EXISTS `orders_shipment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `shipment_no` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `date_shipment` date NOT NULL,
  `tracking_no` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tracking_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `shipment_no` (`shipment_no`),
  KEY `tracking_no` (`tracking_no`),
  KEY `id_user_created` (`id_user_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_shipment_item`
--

CREATE TABLE IF NOT EXISTS `orders_shipment_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders_shipment` int(10) unsigned NOT NULL,
  `id_orders_item_product` int(10) unsigned NOT NULL,
  `id_orders_item_option` int(10) unsigned NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders_shipment` (`id_orders_shipment`),
  KEY `id_orders_item_product` (`id_orders_item_product`),
  KEY `id_orders_item_option` (`id_orders_item_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_shipping_tax`
--

CREATE TABLE IF NOT EXISTS `orders_shipping_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `id_orders_tax` int(10) unsigned NOT NULL,
  `amount` decimal(26,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_orders_tax` (`id_orders_tax`),
  KEY `id_orders_2` (`id_orders`,`id_orders_tax`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_tax`
--

CREATE TABLE IF NOT EXISTS `orders_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_orders` int(10) unsigned NOT NULL,
  `id_tax` int(10) unsigned NOT NULL COMMENT 'Uset to create orders for the table: orders_item_option_tax AND orders_item_product_tax',
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `tax_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `rate` decimal(6,5) unsigned NOT NULL,
  `stacked` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_orders` (`id_orders`),
  KEY `id_tax` (`id_tax`),
  KEY `sort_order` (`sort_order`),
  KEY `stacked` (`stacked`),
  KEY `id_orders_2` (`id_orders`,`id_tax`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders_tax_description`
--

CREATE TABLE IF NOT EXISTS `orders_tax_description` (
  `id_orders_tax` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_orders_tax` (`id_orders_tax`),
  KEY `language_code` (`language_code`),
  KEY `id_orders_tax_2` (`id_orders_tax`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway`
--

CREATE TABLE IF NOT EXISTS `payment_gateway` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `pin` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `page` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Page to call to do the transaction',
  `format_to_send` tinyint(1) unsigned NOT NULL COMMENT '0 = Normal (10.99), 1 = Cents (1099)',
  `hosted_checkout_button_include` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'form to include for the payment button for hosted checkout',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `format_to_send` (`format_to_send`),
  KEY `merchant_id` (`merchant_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `payment_gateway`
--

INSERT INTO `payment_gateway` (`id`, `name`, `merchant_id`, `user_id`, `pin`, `active`, `page`, `format_to_send`, `hosted_checkout_button_include`) VALUES
(1, 'Beanstream', '23423234432', '', '', 0, 'beanstream.php', 0, ''),
(2, 'Desjardins', '89989578', 'test', 'test', 0, 'desjardins.php', 0, ''),
(3, 'Virtual Merchant', '89989578', 'test', 'test', 0, 'virtualmerchant.php', 0, ''),
(4, 'Federated Payment Gateway', '', 'demo', 'password', 0, 'federatedgateway.php', 0, ''),
(5, 'E-xact Transactions Hosted Checkout', 'adfdafssfd', '', '', 0, 'exact_hosted_checkout.php', 0, 'exact_hosted_checkout_button.php');

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway_extra`
--

CREATE TABLE IF NOT EXISTS `payment_gateway_extra` (
  `id_payment_gateway` int(10) unsigned NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_payment_gateway` (`id_payment_gateway`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_type` tinyint(1) unsigned NOT NULL COMMENT '0 = product, 1 = combo deal, 2 = bundled products',
  `sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'unique product inventory code',
  `taxable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 = no, 1 = yes',
  `id_tax_group` int(10) unsigned NOT NULL,
  `brand` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `model` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `track_inventory` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `in_stock` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 = no, 1 = yes',
  `qty` smallint(1) NOT NULL COMMENT 'qty in stock for this product, not used it track_inventory_by is set to product variants',
  `max_qty` smallint(1) unsigned NOT NULL COMMENT 'max qty customer can request on a single purchase',
  `out_of_stock` tinyint(1) NOT NULL COMMENT 'product is out of stock when qty reaches',
  `out_of_stock_enabled` tinyint(1) unsigned NOT NULL COMMENT '1 = Disabled if Out of stock',
  `notify` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 = no, 1 = yes',
  `notify_qty` tinyint(1) unsigned NOT NULL COMMENT 'notify when product qty reaches below',
  `allow_backorders` tinyint(1) unsigned NOT NULL COMMENT 'allow product to be backordered if out of stock, 0 = no, 1 = yes',
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `special_price` decimal(13,2) unsigned NOT NULL,
  `special_price_from_date` datetime NOT NULL,
  `special_price_to_date` datetime NOT NULL,
  `sell_price` decimal(13,2) unsigned NOT NULL,
  `on_sale_end_date` datetime NOT NULL,
  `id_rebate_coupon` tinyint(1) unsigned NOT NULL,
  `discount_type` tinyint(1) unsigned NOT NULL COMMENT 'For product type : Combo',
  `discount` decimal(13,2) unsigned NOT NULL COMMENT 'For product type : Combo',
  `use_product_current_price` tinyint(1) unsigned NOT NULL,
  `use_product_special_price` tinyint(1) unsigned NOT NULL,
  `user_defined_qty` tinyint(1) unsigned NOT NULL,
  `weight` decimal(10,4) unsigned NOT NULL,
  `length` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `extra_care` tinyint(1) NOT NULL COMMENT 'Add a shipping fees calculated by the shipping provider (Canpar), if the product is non-standard (dimensions or very fragile)',
  `use_shipping_price` tinyint(1) unsigned NOT NULL COMMENT 'skip shipping calculation and use specified price in product_price_shipping_region, 0 = no, 1 = yes',
  `enable_local_pickup` tinyint(1) NOT NULL DEFAULT '-1',
  `date_displayed` datetime NOT NULL COMMENT 'product is not displayed until date is met',
  `used` tinyint(1) unsigned NOT NULL,
  `featured` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `display_in_catalog` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0 then do not appear in catalog but appear in bundled and combo',
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archive` tinyint(1) unsigned NOT NULL COMMENT 'When archive, means that the product was sold so we cannot delete it so we archive it.',
  `has_variants` tinyint(1) unsigned NOT NULL COMMENT '0 = no variants, 1 = has variants',
  `downloadable` tinyint(1) unsigned NOT NULL,
  `min_qty` smallint(1) unsigned NOT NULL,
  `display_multiple_variants_form` tinyint(1) unsigned NOT NULL COMMENT '0 = global config, 1 = yes, 2 = no',
  `heavy_weight` tinyint(1) NOT NULL COMMENT 'If 1, then when click on add to cart, must alert a message who say that because this product is too heavy, you must contact us.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `id_rebate_coupon` (`id_rebate_coupon`),
  KEY `special_price_from_date` (`special_price_from_date`,`special_price_to_date`),
  KEY `featured` (`featured`),
  KEY `active` (`active`),
  KEY `display_in_catalog` (`display_in_catalog`),
  KEY `date_displayed` (`date_displayed`),
  KEY `in_stock` (`in_stock`),
  KEY `track_inventory` (`track_inventory`),
  KEY `product_type` (`product_type`),
  KEY `id_tax_group` (`id_tax_group`),
  KEY `taxable` (`taxable`),
  KEY `brand` (`brand`),
  KEY `model` (`model`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `has_variants` (`has_variants`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `product_type`, `sku`, `taxable`, `id_tax_group`, `brand`, `model`, `track_inventory`, `in_stock`, `qty`, `max_qty`, `out_of_stock`, `out_of_stock_enabled`, `notify`, `notify_qty`, `allow_backorders`, `cost_price`, `price`, `special_price`, `special_price_from_date`, `special_price_to_date`, `sell_price`, `on_sale_end_date`, `id_rebate_coupon`, `discount_type`, `discount`, `use_product_current_price`, `use_product_special_price`, `user_defined_qty`, `weight`, `length`, `width`, `height`, `extra_care`, `use_shipping_price`, `enable_local_pickup`, `date_displayed`, `used`, `featured`, `active`, `display_in_catalog`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`, `archive`, `has_variants`, `downloadable`, `min_qty`, `display_multiple_variants_form`, `heavy_weight`) VALUES
(5, 0, '11948', 1, 0, 'HARLEY-DAVIDSON', 'FLTRSEI', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '12999.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '12999.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-25 21:03:00', 1, 1, 1, 1, 113, 113, '2014-03-25 21:03:16', '2014-03-26 01:04:03', 0, 0, 0, 0, 0, 0),
(6, 0, '15040', 1, 0, 'HARLEY-DAVIDSON', 'FXDL', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '9999.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '9999.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 10:52:00', 1, 1, 1, 1, 113, 113, '2014-03-27 10:52:32', '2014-03-27 14:52:32', 0, 0, 0, 0, 0, 0),
(4, 0, '15338 ', 1, 0, 'HARLEY-DAVIDSON', 'FLSTF', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '10495.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '10495.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-25 20:46:00', 1, 1, 1, 1, 113, 113, '2014-03-25 20:46:30', '2014-03-26 00:47:02', 0, 0, 0, 0, 0, 0),
(7, 0, '153444', 1, 0, 'HARLEY-DAVIDSON', 'FXST', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '11995.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '11995.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 13:28:00', 1, 1, 1, 1, 113, 113, '2014-03-27 13:28:33', '2014-03-27 17:28:33', 0, 0, 0, 0, 0, 0),
(8, 0, 'CONSIGNATION', 1, 0, 'HARLEY-DAVIDSON', 'VRSCA', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '10995.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '10995.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 13:44:00', 1, 0, 1, 1, 113, 113, '2014-03-27 13:44:32', '2014-03-27 18:51:03', 0, 0, 0, 0, 0, 0),
(9, 0, '15553', 1, 0, 'HARLEY-DAVIDSON', 'FLHRCI', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '11499.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '11499.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 13:55:00', 1, 0, 1, 1, 113, 113, '2014-03-27 13:55:57', '2014-03-27 17:55:57', 0, 0, 0, 0, 0, 0),
(10, 0, '15365', 1, 0, 'HARLEY-DAVIDSON', 'FXSTD', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '13495.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '13495.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 14:22:00', 1, 0, 1, 1, 113, 113, '2014-03-27 14:22:23', '2014-03-27 18:22:23', 0, 0, 0, 0, 0, 0),
(11, 0, '15585', 1, 0, 'HARLEY-DAVIDSON', 'FLSTF', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '12499.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '12499.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 15:09:00', 1, 0, 1, 1, 113, 113, '2014-03-27 15:09:29', '2014-03-27 19:09:29', 0, 0, 0, 0, 0, 0),
(12, 0, '13438', 1, 0, 'HARLEY-DAVIDSON', '', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '21995.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '21995.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 15:21:00', 1, 0, 1, 1, 113, 113, '2014-03-27 15:21:37', '2014-03-27 19:21:37', 0, 0, 0, 0, 0, 0),
(13, 0, '15290', 1, 0, 'HARLEY-DAVIDSON', '', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '10895.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '10895.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 16:38:00', 1, 0, 1, 1, 113, 113, '2014-03-27 16:38:13', '2014-03-27 20:39:03', 0, 0, 0, 0, 0, 0),
(14, 0, '15342', 1, 0, 'HARLEY-DAVIDSON', '', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '7395.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '7395.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 16:47:00', 1, 0, 1, 1, 113, 113, '2014-03-27 16:47:03', '2014-03-27 20:47:04', 0, 0, 0, 0, 0, 0),
(15, 0, '15407', 1, 0, 'HARLEY-DAVIDSON', 'XL', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '6895.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '6895.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 17:36:00', 1, 0, 1, 1, 113, 113, '2014-03-27 17:36:29', '2014-03-27 21:37:04', 0, 0, 0, 0, 0, 0),
(16, 0, '15261', 1, 0, 'HARLEY-DAVIDSON', '', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '13495.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '13495.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 18:19:00', 1, 0, 1, 1, 113, 113, '2014-03-27 18:19:13', '2014-03-27 22:20:04', 0, 0, 0, 0, 0, 0),
(17, 0, '15110', 1, 0, 'HARLEY-DAVIDSON', 'FLSTF', 0, 1, 0, 0, 0, 0, 1, 0, 0, '0.00', '15495.00', '0.00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '15495.00', '0000-00-00 00:00:00', 0, 0, '0.00', 0, 0, 0, '0.0000', 0, 0, 0, 0, 0, -1, '2014-03-27 18:26:00', 1, 0, 1, 1, 113, 113, '2014-03-27 18:26:35', '2014-03-27 22:27:02', 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_bundled_product_group`
--

CREATE TABLE IF NOT EXISTS `product_bundled_product_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `input_type` tinyint(1) unsigned NOT NULL COMMENT '0 - dropdown, 1 - radio, 2 - checkbox, 3 - multi select',
  `required` tinyint(1) unsigned NOT NULL COMMENT '0 - no, 1 - yes',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `input_type` (`input_type`),
  KEY `required` (`required`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_bundled_product_group_description`
--

CREATE TABLE IF NOT EXISTS `product_bundled_product_group_description` (
  `id_product_bundled_product_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_bundled_product_group` (`id_product_bundled_product_group`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_bundled_product_group_product`
--

CREATE TABLE IF NOT EXISTS `product_bundled_product_group_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_bundled_product_group` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `price_type` tinyint(1) unsigned NOT NULL COMMENT '(0 - fixed, 1 - percent) - price is calculated on product base price',
  `price` decimal(13,2) unsigned NOT NULL,
  `qty` tinyint(1) unsigned NOT NULL,
  `user_defined_qty` tinyint(1) unsigned NOT NULL COMMENT 'allow customer to change qty on the front end',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `selected` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product_bundled_product_group` (`id_product_bundled_product_group`),
  KEY `id_product` (`id_product`),
  KEY `id_product_variant` (`id_product_variant`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `sort_order` (`sort_order`),
  KEY `user_defined_qty` (`user_defined_qty`),
  KEY `price_type` (`price_type`),
  KEY `selected` (`selected`),
  KEY `id_product_bundled_product_gro_2` (`id_product_bundled_product_group`,`id_product`,`id_product_variant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

CREATE TABLE IF NOT EXISTS `product_category` (
  `id_product` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_product`,`id_category`),
  KEY `id_category` (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_category`
--

INSERT INTO `product_category` (`id_product`, `id_category`) VALUES
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_combo`
--

CREATE TABLE IF NOT EXISTS `product_combo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_combo_product` int(10) unsigned NOT NULL COMMENT 'Product ID in the combo',
  `qty` smallint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `displayed` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`),
  KEY `id_product` (`id_product`),
  KEY `id_combo_product` (`id_combo_product`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `displayed` (`displayed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_combo_variant`
--

CREATE TABLE IF NOT EXISTS `product_combo_variant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_combo` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `default_variant` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product_combo` (`id_product_combo`),
  KEY `id_product_variant` (`id_product_variant`),
  KEY `default_variant` (`default_variant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_description`
--

CREATE TABLE IF NOT EXISTS `product_description` (
  `id_product` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'max char for seo in title bar is 65',
  `short_desc` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `meta_description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product` (`id_product`,`language_code`),
  KEY `name` (`name`),
  KEY `alias` (`alias`),
  KEY `id_product_2` (`id_product`,`language_code`,`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_description`
--

INSERT INTO `product_description` (`id_product`, `language_code`, `name`, `short_desc`, `description`, `meta_description`, `meta_keywords`, `alias`) VALUES
(5, 'fr', 'HARLEY-DAVIDSON FLTRSEI 2000/ 78 401 KM/ COULEUR ROUGE-CVO', 'HARLEY-DAVIDSON FLTRSEI 2000\r\n78 401 KM\r\nCOULEUR ROUGE / CVO\r\n1550 CC\r\n95PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E: </strong>2000</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>78 401 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR: </strong>ROUGE -CVO</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p align="LEFT" style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Tachym&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Phares</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Valises &ndash; rigides</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Radio</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><br />\r\n<font face="Arial, sans-serif">MISE AU POINT FAITE</font></p>\r\n', 'moto usagée à vendre, harley-davidson, FLTRSEI. 2000, CVO, FLTRSEI A VENDRE, CVO RAOD GLIDE A VENDRE SCREAMING EAGLE', 'moto usagée à vendre, harley-davidson, FLTRSEI. 2000, CVO, FLTRSEI A VENDRE, CVO RAOD GLIDE A VENDRE SCREAMING EAGLE', 'harley-davidson-fltrsei-2000'),
(6, 'fr', 'HARLEY-DAVIDSON FXDL 2003 / 29 835 KM / NOIR 100TH', 'HARLEY-DAVIDSON FXDL 2003 / 29 835 KM / NOIR 100TH\r\n1450 CC\r\n88PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:&nbsp;</strong> 2003</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>29 835 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:&nbsp;</strong> NOIR 100E ANNIVERSAIRE</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p align="LEFT" style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Tachym&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">MISE AU POINT FAITE</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p>ALARME HD</p>\r\n\r\n<p>WINDSHIELD 100TH CLIPS</p>\r\n\r\n<p>BAS DE FOURCHE CHROM&Eacute;</p>\r\n\r\n<p>CONTROL HANDLEBAR CHROM&Eacute; ET CABLE BRAIDED</p>\r\n\r\n<p>TOP CARBURATEUR CHROM&Eacute;</p>\r\n\r\n<p>EN BONNE CONDITION</p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n', 'MOTO USAGÉE A VENDRE, FXDL, 2003, HARLEY-DAVIDSON, FXDL A VENDRE, DYNA LOW RIDE A VENDRE ', 'MOTO USAGÉE A VENDRE, FXDL, 2003, HARLEY-DAVIDSON, FXDL A VENDRE, DYNA, LOW RIDER A VENDRE', 'harley-davidson-fxdl-2003'),
(4, 'fr', 'HARLEY-DAVIDSON FLSTF 2000/ 37 785 KM / BLANC', 'HARLEY-DAVIDSON FLSTF 2000\r\n37 785 KM\r\nCOULEUR BLANC\r\n1450 CC\r\nCOULEUR: BLANC\r\n88 PC\r\n\r\n', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E: </strong>2000</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>37 785 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR: </strong>BLANC</div>\r\n\r\n<div style="margin-bottom: 0cm;">&nbsp;</div>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Phares</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Dossier</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">Dossier d&eacute;tachable</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">GRIPS</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;"><font face="Arial, sans-serif">MISE AU POINT FAITE</font><br />\r\n&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">WINDSHIELD CLIPS</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">PASSING LAMP KIT</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">ENGINE GUARD</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">GRIPS CUSTOM HD</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">AIR FILTER STAGE 1</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">EXHAUST THUNDER HEADERS</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">PROT&Egrave;GE TALON SUR FOOT AVANT DROIT</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">DOSSIER ET RACK CLIPS</font></p>\r\n\r\n<p style="margin-bottom: 0cm;"><font face="Arial, sans-serif">SUPPORT POUR SACOCHE</font></p>\r\n\r\n<p style="font-weight: normal; text-decoration: none; margin-bottom: 0cm;">&nbsp;</p>\r\n', 'MOTO USAGÉE A VENDRE, HARLEY DAVIDSON, FLSTF, 2000, FXSTF A VENDRE, SOFTAIL A VENDRE, FAT BOY A VENDRE', 'MOTO USAGÉE A VENDRE, HARLEY DAVIDSON, FLSTF, 2000, FXSTF A VENDRE, SOFTAIL A VENDRE, FAT BOY A VENDRE ', 'harley-davidson-flstf-2000'),
(7, 'fr', 'HARLEY-DAVIDSON FXST 2003 / 21 366KM / NOIR 100TH', 'HARLEY-DAVIDSON FSXT 2003\r\n21 366 KM \r\nNOIR 100E ANNIVERSAIRE\r\n1450 CC\r\n88PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong> 2003</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>15 344 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR: </strong>NOIR 100E ANNIVERSAIRE</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">GARANTIE PROLONG&Eacute;E (HD) DISPONIBLE</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">Pegs</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p>ALARME HD</p>\r\n\r\n<p>WINDSHIELD CLIPS</p>\r\n\r\n<p>VISOR AIGLE CHROM&Eacute;</p>\r\n\r\n<p>DRAGS BA</p>\r\n\r\n<p>ENSEMBLE PEG+GRIPS+SHIFTER+BRAKE DIAMOND PLATE</p>\r\n\r\n<p>ROUE AVANT LOW BOY 16 POUCE + FENDER</p>\r\n\r\n<p>FOURCHE AVANT CHROM&Eacute;E</p>\r\n\r\n<p>AIR FILTER INSERT 100TH</p>\r\n\r\n<p>CAM COVER + SIDE TRANS + PRIMAIRE CHROM&Eacute;S</p>\r\n\r\n<p>AXLE COVER REAR</p>\r\n\r\n<p>RACK FENDER ETC...</p>\r\n\r\n<p>MOTO BAS KILOM&Eacute;TRAGE&nbsp; 21 366KM</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'MOTO USAGÉE À VENDRE, HARLEY DAVIDSON ,FXST, 2003, FXST A VENDRE, SOFTAIL A VENDRE USAGÉ, SOFTAIL STANDARD A VENDRE USAGÉ', 'MOTO USAGÉE À VENDRE, HARLEY DAVIDSON ,FXST, 2003, FXST A VENDRE, SOFTAIL A VENDRE USAGÉ, SOFTAIL STANDARD A VENDRE USAGE', 'harley-davidson-fxst-2003--15-344-km--noir-100th'),
(8, 'fr', 'HARLEY-DAVIDSON VRSCA 2003 / 46 963 KM / GRIS ET NOIR', 'HARLEY-DAVIDSON VRSCA 2003\r\n46 963 KM\r\nCOULEUR GRIS ET NOIR \r\n1130 CC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong> 2003</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>46 963 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:&nbsp;</strong> NOIR ET GRIS</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p align="LEFT" style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Tachym&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Dossier</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">WINDSHIELD CLIPS</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">PASSING LAMP KIT</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ENGINE GUARD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">GRIPS CUSTOM HD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">AIR FILTER STAGE 1</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">EXHAUST THUNDER HEADERS</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">PROT&Egrave;GE TALON SUR FOOT AVANT DROIT</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">DOSSIER ET RACK CLIPS</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">SUPPORT POUR SACOCHE</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n', 'moto usagée a vendre, harley-davisdon, vrsca, v-rod, vrsca usagé a vendre, 2003, V-ROD USAGE A VENDRE, 100E ANNIVERSAIRE', 'moto usagée a vendre, harley-davisdon, vrsca, v-rod, vrsca usagé a vendre, 2003, V-ROD USAGE A VENDRE, 100E ANNIVERSAIRE', 'vrsca-2003--46-963-km--gris-et-noir'),
(9, 'fr', 'HARLEY-DAVISDON FLHRCI 2004 / 77 867 KM / BLANC', 'HARLAY-DAVIDSON FLHRCI 2004\r\n77 867 KM\r\nCOULEUR BLANC\r\n1450 CC\r\n88PC', '<div><strong>ANN&Eacute;E: </strong>2004</div>\r\n\r\n<div><strong>77 867 KM</strong></div>\r\n\r\n<div><strong>COULEUR:</strong> BLANC</div>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<div>\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Dossier d&eacute;tachable</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p>ALARME HD</p>\r\n\r\n<p>DOSSIER CLIPS + RACK ARRI&Egrave;RE</p>\r\n\r\n<p>AXLE CAP FRONT</p>\r\n\r\n<p>BAS AILE CHROM&Eacute; BAR/SHIELD</p>\r\n\r\n<p>AIR FILTER STAGE 1 + BACK PLATE CHROM&Eacute;E</p>\r\n</div>\r\n', 'moto usagée a vendre, flhrci, 2004, harley-davidson usagé, road king classic a vendre, blanc, 1450cc', 'moto usagée a vendre, flhrci, 2004, harley-davidson usagé, road king classic a vendre, blanc, 1450cc', 'harley-davisdon-flhrci-2004--77-867-km--blanc'),
(10, 'fr', 'HARLEY-DAVIDSON FXSTD 2004 / 27 930 KM / BLANC GLACIER', 'HARLEY-DAVIDSON FXSTD 2004\r\n27 930 KM\r\nCOULEUR BLANC GLACIER\r\n1450 CC\r\n88PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:&nbsp;</strong> 2004</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>27 930 KM </strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR: </strong>BLANC GLACIER</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">WINDSHIELD AMOVIBLE</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">LENS FLASHER CLEAR</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ALARME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">FRONT WHEEL CHROM&Eacute;E CUSTOM HD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">50/50 EXHAUST SCREAMING EAGLE 2</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">FILTRE A AIR STAGE 1</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">MOTO BAS KILOM&Eacute;TRAGE TR&Egrave;S PROPRE</font></p>\r\n', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON, FXSTD, HARLEY USAGÉ A VENDRE, 2004, SOFTAIL USAGE A VENDRE, DEUCE USAGE A VENDRE, ', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON, FXSTD, HARLEY USAGÉ A VENDRE, 2004, SOFTAIL USAGE A VENDRE, DEUCE USAGE A VENDRE, ', 'harley-davidson-fxstd-2004--27-930-km--blanc-glacier'),
(11, 'fr', 'HARLEY-DAVIDSON FLSTF 2005 / 79 279 KM / BLACK PEARL', 'HARLEY-DAVIDON FLSTF 2005\r\n79 279 KM \r\nCOULEUR BLACK PEARL\r\n1450 CC\r\n88PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:&nbsp;</strong> 2005</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>79 279 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:</strong>&nbsp; BLACK PEARL</div>\r\n\r\n<div style="margin-bottom: 0cm;">&nbsp;</div>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Selle sp&eacute;ciale</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p>ALARME HD</p>\r\n\r\n<p>MIROIR TRIBAL CHROME + HANDLEVER FLAME</p>\r\n\r\n<p>ENSEMBLE GRIPS + PEGS + BRAKE+ SHIFTER FLAME</p>\r\n\r\n<p>KIT RISER PULLBACK FLSTN</p>\r\n\r\n<p>PLAQUE TANK CHROM&Eacute; FAT BOY</p>\r\n\r\n<p>ENSEMBLE AIR FILTER COVER + TIMMING + DERBY FLAME CHROME</p>\r\n\r\n<p>LAME FLAME CHROM&Eacute; + PIVOT CAP COVER</p>\r\n\r\n<p>SEAT SOLO MUSTANG + PARTIE ARRIERE</p>\r\n\r\n<p>50/50 SCREAMING EAGLE + FILTRE STAGE 1</p>\r\n\r\n<p>LENS FLASHER SMOKE + RING HEADLAMP</p>\r\n\r\n<p>RACK LICENSE COUCHE CHROME</p>\r\n\r\n<p>DOCKING KIT DOSSIER + DOCKING POUR WINDSHIELD</p>\r\n\r\n<p>REAR AXLE COVER</p>\r\n\r\n<p>LOWER BELT GUARD CHROME + SPROCKET COVER</p>\r\n\r\n<p>ROD SHIFTER FAT BOY + CAP BOUGIE CHROME</p>\r\n\r\n<p>MOTO COMME NEUVE!!</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'MOTO USAGEE A VENDRE, HARLEY-DAVIDSON, 2005, FLSTF, SOFTAIL FAT BOY A VENDRE, ', 'MOTO USAGEE A VENDRE, HARLEY-DAVIDSON, 2005, FLSTF, SOFTAIL FAT BOY A VENDRE, ', 'harley-davidson-flstf-2005--79-279-km--black-pearl'),
(12, 'fr', 'HARLEY-DAVIDSON FLHTCUI 2006 / 41 250 KM / BLEU ET GRIS', 'HARLEY-DAVIDSON FLHTCUI 2006\r\n41 250 KM \r\nCOULEUR BLEU ET GRIS\r\n1450 CC ', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong>&nbsp; 2006</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>41 250 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:</strong>&nbsp; BLEU ET GRIS</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p align="LEFT" style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Tachym&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Phares</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Valises &ndash; rigides</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Radio</font> + C/D</p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Crash Bar</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p>STAGE 1 MUFFLER 50/50 HD</p>\r\n\r\n<p>AIR FILTER ET DOWNLOAD FAIT</p>\r\n\r\n<p>FAN POUR REFROIDIR LE MOTEUR</p>\r\n\r\n<p>RENVERSE SUR TRANY</p>\r\n\r\n<p>PEDALES CHROMEES</p>\r\n\r\n<p>GPS INCLUS</p>\r\n\r\n<p>SIDE-CAR HD ULTRA</p>\r\n\r\n<p>POSSIBILIT&Eacute; D&#39;ACHETER LE SIDE-CAR S&Eacute;PAR&Eacute;MENT AVEC TOUTES LES ATTACHES POUR 5500.00$ COMPLET</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON USAGÉ A VENDRE, FLHTCUI, 2006, ELECTRA GLIDE ULTRA CLASSIC', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON USAGÉ A VENDRE, FLHTCUI, 2006, ELECTRA GLIDE ULTRA CLASSIC', 'harley-davidson-flhtcui-2006--41-250-km--bleu-et-gris'),
(13, 'fr', 'HARLEY-DAVIDSON FXDLI 2006 / 43 097 KM / NOIR LUSTRE', 'HARLEY-DAVIDSON FXDLI 2006\r\n43 097 KM \r\nCOULEUR NOIRE LUSTRE\r\n1450 CC\r\n88PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong>&nbsp; 2006</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>43 097 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:&nbsp;</strong> NOIR LUSTRE</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p align="LEFT" style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Tachym&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Dossier d&eacute;tachable</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ALARME HD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">HANDLEBAR 10 POUCE APE</font></p>\r\n\r\n<p style="margin-bottom: 0cm">LENS FLASHER SMOKE + TAIL LAMP LED HD</p>\r\n\r\n<p style="margin-bottom: 0cm">MIROIR CUSTOM HD</p>\r\n\r\n<p style="margin-bottom: 0cm">SWITCH BUTTON CHROME</p>\r\n\r\n<p style="margin-bottom: 0cm">GRIPS + PEGS + SHIFTER + BRAKE PEDAL CUSTOM</p>\r\n\r\n<p style="margin-bottom: 0cm">REGULATEUR COVER&nbsp; CHROME</p>\r\n\r\n<p style="margin-bottom: 0cm">AIR FILTER STAGE 1 + EXHAUST VANCE/HINES</p>\r\n\r\n<p style="margin-bottom: 0cm">DOSSIER DETACHABLE</p>\r\n\r\n<p style="margin-bottom: 0cm">FIL BOUGIE ROUGE</p>\r\n\r\n<p style="margin-bottom: 0cm">EXTENSION DE PIED</p>\r\n\r\n<p style="margin-bottom: 0cm">TRES PROPRE</p>\r\n', 'moto usagee a vendre, harley-davidson usage, fxdli a vendre, 2006 a vendre, dyna low rider usage a vendre', 'moto usagee a vendre, harley-davidson usage, fxdli a vendre, 2006 a vendre, dyna low rider usage a vendre', 'harley-davidson-fxdli-2006--43-097-km--noir-lustre'),
(14, 'fr', 'HARLEY-DAVIDSON XL 1200C 2006 / 22 950 KM / YELLOW PEARL', 'HARLEY-DAVIDSON XL 1200C 2006\r\n22 950 KM \r\nCOULEUR: YELLOW PEARL\r\n1200 CC\r\n74PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:&nbsp;</strong> 2006</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>22 950 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR: </strong>YELLOW PEARL</div>\r\n\r\n<div style="margin-bottom: 0cm;">&nbsp;</div>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Selle sp&eacute;ciale</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">DRAGS BAR + GRIPS CHROME + BOUTON CHROME + BADLANDER SEAT + CAP BOUGIE CHROME + MASTER REAR CHROME </font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">FIL BATTERIE TENDER </font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">DOCKING DOSSIER AMOVIBLE </font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">EXHAUSTE VANCE/HINES</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">FILTRE A AIR STAGE 1</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">COVER SWITCH IGNITION CHROME</font> + CAP COVER TIMMING ETC..</p>\r\n\r\n<p style="margin-bottom: 0cm">MOTO TRES PROPRE BAS KILOMETRAGE!!</p>\r\n', 'MOTO USAGEE A VENDRE, HARLEY DAVIDSON, XL 1200C USAGE A VENDRE, 2006 ', 'MOTO USAGEE A VENDRE, HARLEY DAVIDSON, XL 1200C USAGE A VENDRE, 2006 ', 'harley-davidson-xl-1200c-2006--22-950-km--yellow-pearl'),
(15, 'fr', 'HARLEY-DAVIDSON XL 1200C 2006 / 44 403 KM / NOIR LUSTRE', 'HARLEY-DAVIDSON XL 1200C 2006\r\n44 403 KM \r\nCOULEUR NOIR LUSTRE\r\n1200 CC\r\n74PC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong> 2006</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>44 403 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:</strong> NOIR LUSTRE</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Contr&ocirc;les avanc&eacute;s</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ALARME HD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">DRAGS BAR + GRIPS FLAME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">BAS DE FOURCHE NOIR LUSTRE A VENIR COMPRIS DANS LE PRIX</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">LENS FLASHER SMOKE</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">REGULATEUR COVER CHROME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">AIR FILTREUR STAGE 1</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">KIT PEG FLAME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">TIMMING COVER + DERBY + GAZ CAP FLAME GOLD</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">UPPER BELT GUARD CHROME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">INSERT OIL TANK + COVER BATTERY CHROME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">LICENCE PLATE ANGLE CHROME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">KNOB COVER CHROME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">GARANTIE 2 ANS INCLUSE</font> **CONCESSIONNAIRE</p>\r\n', 'MOTO USAGÉE A VENDRE, XL 1200C A VENDRE USAGE, 2006, HARLEY-DAVIDSON USAGE A VENDRE', 'MOTO USAGÉE A VENDRE, XL 1200C A VENDRE USAGE, 2006, HARLEY-DAVIDSON USAGE A VENDRE', 'harley-davidson-xl-1200c-2006--44-403-km--nor'),
(16, 'fr', 'HARLEY-DAVIDSON FLSTCI 2007 / 85 478 KM / NOIR ET ROUGE', 'HARLEY-DAVIDSON FLSTCI 2007\r\n85 478 KM\r\nCOULEUR: NOIR ET ROUGE', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E:</strong> 2007</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>85 478 KM</strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:</strong> NOIR ET ROUGE</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Phares</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Sacoches souples</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pare-brise</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ROUE PROFILE + FLANC BLANC</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">CRASH BAR MOUSTACHE </font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">AIR FILTER STAGE 1 + EXHAUST MODIFIE</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">ALARME</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">RACK LICENCE CHROME ANGLE</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">KIT RENFORT DASN SACOCHE CUIR</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">INSPECTION FAIT EN PARFAITE CONDITION</font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">FACTURE 1459$</font></p>\r\n', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON USAGE A VENDRE, FLSTCI, 2006, SOFTAIL HERITAGE CLASSIC USAGE A VENDRE', 'MOTO USAGÉE A VENDRE, HARLEY-DAVIDSON USAGE A VENDRE, FLSTCI, 2006, SOFTAIL HERITAGE CLASSIC USAGE A VENDRE', 'harley-davidson-flstci-2007--85-478-km--noir-et-rouge'),
(17, 'fr', 'HARLEY-DAVIDON FLSTF 2007 / 42 471 KM / NOIR DENIM', 'HARLEY-DAVIDSON FLSTF 2007\r\n42 471 KM \r\nCOULEUR: NOIR DENIM\r\n1584 CC', '<div style="margin-bottom: 0cm;"><strong>ANN&Eacute;E: </strong>2007</div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>42 471 KM </strong></div>\r\n\r\n<div style="margin-bottom: 0cm;"><strong>COULEUR:</strong> NOIR DENIM</div>\r\n\r\n<p style="margin-bottom: 0cm">&nbsp;</p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif"><u><b>OPTIONS&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Compteur de vitesse</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Odom&egrave;tre</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">&Eacute;chappement 50/50</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Indicateur de niveau d&#39;essence</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Mise au point faite</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Guidon sp&eacute;cial</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Alarme</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Garantie prolong&eacute;e (HD) disponible</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Pegs</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Selle sp&eacute;ciale</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Grips</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none"><font face="Arial, sans-serif">Contr&ocirc;les avanc&eacute;s</font></p>\r\n\r\n<p style="margin-bottom: 0cm; font-weight: normal; text-decoration: none">&nbsp;</p>\r\n\r\n<p><font face="Arial, sans-serif"><u><b>NOTES&nbsp;:</b></u></font></p>\r\n\r\n<p style="margin-bottom: 0cm"><font face="Arial, sans-serif">W</font></p>\r\n', 'moto usagee a vendre, harley-davidson usage a vendre, FLSTF usage a vendre, 2007, softail fat boy usage a vendre\r\n', 'moto usagee a vendre, harley-davidson usage a vendre, FLSTF usage a vendre, 2007, softail fat boy usage a vendre', 'harley-davidon-flstf-2007--42-471-km--noir-denim');

-- --------------------------------------------------------

--
-- Table structure for table `product_downloadable_files`
--

CREATE TABLE IF NOT EXISTS `product_downloadable_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `no_days_expire` smallint(5) unsigned NOT NULL,
  `no_downloads` tinyint(1) unsigned NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_downloadable_files_description`
--

CREATE TABLE IF NOT EXISTS `product_downloadable_files_description` (
  `id_product_downloadable_files` int(11) NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_downloadable_files_description` (`id_product_downloadable_files`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_downloadable_videos`
--

CREATE TABLE IF NOT EXISTS `product_downloadable_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_variant` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `embed_code` text COLLATE utf8_unicode_ci NOT NULL,
  `no_days_expire` smallint(5) unsigned NOT NULL,
  `no_downloads` tinyint(1) unsigned NOT NULL,
  `stream` tinyint(1) NOT NULL COMMENT 'This video Stream or Not?',
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_downloadable_videos_description`
--

CREATE TABLE IF NOT EXISTS `product_downloadable_videos_description` (
  `id_product_downloadable_videos` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_downloaded_videos` (`id_product_downloadable_videos`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_do_not_ship_region`
--

CREATE TABLE IF NOT EXISTS `product_do_not_ship_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_product_2` (`id_product`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_image`
--

CREATE TABLE IF NOT EXISTS `product_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `original` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `force_crop` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `cover` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `sort_order` (`sort_order`),
  KEY `cover` (`cover`),
  KEY `id_product_2` (`id_product`,`cover`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=137 ;

--
-- Dumping data for table `product_image`
--

INSERT INTO `product_image` (`id`, `id_product`, `original`, `filename`, `force_crop`, `sort_order`, `cover`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(93, 4, '9583117279f7aeef9add983dbcfa4a4c.jpg', 'f6f44c48e05a81dac30c8763a4c107b2.jpg', 1, 5, 0, 113, 113, '2014-03-27 15:31:17', '2014-03-27 19:31:18'),
(94, 4, '1173d6de9c8c2a07e4c0c789c5c2fbd9.jpg', 'bc02c41216124c512e3d19fe979bfaeb.jpg', 1, 6, 0, 113, 113, '2014-03-27 15:31:20', '2014-03-27 19:31:20'),
(99, 9, 'e6b0e65a56dfe9232b29e6ce46d5844a.jpg', 'e7672193609a2d1fcef05865203d3cba.jpg', 1, 5, 0, 113, 113, '2014-03-27 15:40:12', '2014-03-27 19:40:13'),
(100, 9, '3f6ffc6ab7e7ba661587bcbe35675470.jpg', '38a314c2a51014abb608c22e2b533c82.jpg', 1, 6, 0, 113, 113, '2014-03-27 15:40:14', '2014-03-27 19:40:15'),
(101, 9, '1ba8ba8c44274ce95d24cd2997af8a85.jpg', '5336faf2c7231a803445307bc023ef4d.jpg', 1, 7, 0, 113, 113, '2014-03-27 15:40:16', '2014-03-27 19:40:17'),
(102, 9, '16fbb1a15c5bad95d00cdae58b0085d2.jpg', 'c5e912638d111228f869a0f5a1c1852f.jpg', 1, 8, 0, 113, 113, '2014-03-27 15:40:19', '2014-03-27 19:40:19'),
(103, 13, '35b8b13eeb445cb7baa01fe3a0020e8e.jpg', '4393df1ecb07f7b781313a9d7fc8770c.jpg', 1, 1, 1, 113, 113, '2014-03-27 16:38:49', '2014-03-27 20:38:49'),
(15, 5, '4d4a043767ebe9248e6b4260ffae61b6.jpg', '095030526179a05e3d77ecafd952d8dd.jpg', 1, 1, 1, 113, 113, '2014-03-26 16:36:25', '2014-03-26 20:36:26'),
(16, 5, '4b79898ba3ef693fd7f4b1399edd1635.jpg', 'd4764717ed8fada151b5d7d83cd3ba18.jpg', 1, 2, 0, 113, 113, '2014-03-26 16:36:28', '2014-03-26 20:36:28'),
(17, 5, '3716b2bc16f24f18f2d82bc8b002fb14.jpg', '1162eb014777647426e39d80e5f3b30a.jpg', 1, 3, 0, 113, 113, '2014-03-26 16:36:30', '2014-03-26 20:36:31'),
(18, 5, '1a7890a0363daa4d524f5a1006b57114.jpg', '2365d5137a41e8ce82de9f6428789ec6.jpg', 1, 4, 0, 113, 113, '2014-03-26 16:36:33', '2014-03-26 20:36:33'),
(19, 5, '58bfaff6eca098b31b76d9a295ebf1c6.jpg', '4af388368c95bab95495b0fa4e2e7bea.jpg', 1, 5, 0, 113, 113, '2014-03-26 16:36:35', '2014-03-26 20:36:36'),
(20, 5, '93ea3209e7f2dccd6f191670c1ce4295.jpg', '7a2c36f186890ca884e15730fc422c97.jpg', 1, 6, 0, 113, 113, '2014-03-26 16:36:39', '2014-03-26 20:36:40'),
(21, 5, '9cbad77a9618ed01a0fb94f1a1dc4d80.jpg', '14b27d5c7b97549c360492373ad7e8a7.jpg', 1, 7, 0, 113, 113, '2014-03-26 16:36:42', '2014-03-26 20:36:42'),
(22, 5, '40c3f485f64ea4ae7ff526a4ceb3d281.jpg', '070cc93147d9729ace1d2d455b15bf8c.jpg', 1, 8, 0, 113, 113, '2014-03-26 16:36:45', '2014-03-26 20:36:45'),
(23, 6, 'ba133428cc1c402191eb2f129f35c8dc.jpg', '7344811d1a674584002ed0ffa9ed3f82.jpg', 1, 1, 1, 113, 113, '2014-03-27 10:53:04', '2014-03-27 14:53:04'),
(24, 6, '548614362a5d1de0388cee7b6c608291.jpg', '96a6af81d5bf2122addf8b4fb4a9b6d9.jpg', 1, 2, 0, 113, 113, '2014-03-27 10:53:06', '2014-03-27 14:53:07'),
(25, 6, '092a1f6b7a48456a6991e4e805473a3d.jpg', '0191e1d9820f84b8af44d8cff8466814.jpg', 1, 3, 0, 113, 113, '2014-03-27 10:53:09', '2014-03-27 14:53:09'),
(26, 6, '23e9991ccb518c61c143f2c1cacd8116.jpg', '1e0d0333eae39e6f4d9a1b1cdf515a8d.jpg', 1, 4, 0, 113, 113, '2014-03-27 10:53:11', '2014-03-27 14:53:12'),
(27, 6, '5b90bb70c25e5c9479b4b79b1a47212d.jpg', 'cef55faeddf16075522e9bf3c462455f.jpg', 1, 5, 0, 113, 113, '2014-03-27 10:53:13', '2014-03-27 14:53:13'),
(28, 6, 'efef6f2637cffea513ef2abcc7e49d45.jpg', '327cf182dc6fcac282ef9723cf1802a3.jpg', 1, 6, 0, 113, 113, '2014-03-27 10:53:15', '2014-03-27 14:53:15'),
(29, 7, 'ddbb42885be3f16be678ee4f92146a88.jpg', 'e5ce98605e0eebd0d5b13bd568642e26.jpg', 1, 1, 1, 113, 113, '2014-03-27 13:30:50', '2014-03-27 17:30:51'),
(30, 7, 'd204e1abcb83c45144abeb07104446e0.jpg', '0e99498d02d0f96392197a1f02551319.jpg', 1, 2, 0, 113, 113, '2014-03-27 13:30:52', '2014-03-27 17:30:53'),
(31, 7, '0bca0ef3d6b32527aa7215259e187619.jpg', 'f2ede3723eb6cd8d3fcb5d5f2c2963ac.jpg', 1, 3, 0, 113, 113, '2014-03-27 13:30:55', '2014-03-27 17:30:55'),
(32, 7, '91d95c481c5f48cb117fc66a53ddd893.jpg', 'c14a8c5769f0b595fddd8b8d83950673.jpg', 1, 4, 0, 113, 113, '2014-03-27 13:30:57', '2014-03-27 17:30:57'),
(33, 7, 'e7055a9eaa816370a707bed90292d405.jpg', '96ccb678cc2975b6c15e35a2f45186e3.jpg', 1, 5, 0, 113, 113, '2014-03-27 13:30:59', '2014-03-27 17:30:59'),
(34, 7, 'aac0ff9bf5bf1ffc8f4561df8994647a.jpg', 'c4bc7d58a0443f6fe1c5d21bf441ddd7.jpg', 1, 6, 0, 113, 113, '2014-03-27 13:31:01', '2014-03-27 17:31:02'),
(35, 7, '0241e94fa966f03a0b34b5d7c27363c5.jpg', 'e6cc3e684e19837f4158232af8d9f5f6.jpg', 1, 7, 0, 113, 113, '2014-03-27 13:31:04', '2014-03-27 17:31:04'),
(36, 7, '1da0c45d18073372ef7491ea911bc321.jpg', '57196d607ce3e1c9c3688119137d547b.jpg', 1, 8, 0, 113, 113, '2014-03-27 13:31:06', '2014-03-27 17:31:06'),
(37, 7, 'ad569a12327a9ed943d6e3318fe2dcc4.jpg', '379b24a5e7ed4e727e4c9af8094b3024.jpg', 1, 9, 0, 113, 113, '2014-03-27 13:31:09', '2014-03-27 17:31:09'),
(38, 7, '59a912891f13016b6f8435853c3b0a3d.jpg', '5b5cf60526b28809f1e1ab82a05fd643.jpg', 1, 10, 0, 113, 113, '2014-03-27 13:31:12', '2014-03-27 17:31:12'),
(39, 7, '8ae38588650f8531d28757556da8d8a8.jpg', 'a95700195f6926f99891d51c72d63cc0.jpg', 1, 11, 0, 113, 113, '2014-03-27 13:31:14', '2014-03-27 17:31:14'),
(40, 8, '7882e833a1b93d8912591b81f3863b40.jpg', 'a07f95ae2191ab0756a8edc9c6cc6fb5.jpg', 1, 1, 1, 113, 113, '2014-03-27 13:46:19', '2014-03-27 17:46:20'),
(41, 8, '15c905f79e819e67df34650083e3f914.jpg', 'af7bbd112530f069fc731edd43217c25.jpg', 1, 2, 0, 113, 113, '2014-03-27 13:46:21', '2014-03-27 17:46:22'),
(42, 8, '0b1a08535a72fee0be33f40141c2d4eb.jpg', '55ec3a7ba80e1cd6e9011d68697d9330.jpg', 1, 3, 0, 113, 113, '2014-03-27 13:46:25', '2014-03-27 17:46:26'),
(43, 8, '8f5f7e6612828701f1e3e65b76229335.jpg', 'de633ff7774ff5a7becf9ef6baf41570.jpg', 1, 4, 0, 113, 113, '2014-03-27 13:46:27', '2014-03-27 17:46:28'),
(44, 8, '6727a85dca926cb9a3c0d912eca1c3b4.jpg', 'aaeda81460e25896fdd01b8763c4ec3c.jpg', 1, 5, 0, 113, 113, '2014-03-27 13:46:30', '2014-03-27 17:46:30'),
(98, 9, 'a0efc9eee00568585cb7ed6d8eadffa9.jpg', '49b51d3107da45c5f4cc83fd03b64ebf.jpg', 1, 4, 0, 113, 113, '2014-03-27 15:40:08', '2014-03-27 19:40:08'),
(97, 9, 'f45c0d52b51fd0b86219b8af17e94f98.jpg', 'b81d0335e69ab0c27e32fe5a7c3a467f.jpg', 1, 3, 0, 113, 113, '2014-03-27 15:40:06', '2014-03-27 19:40:06'),
(96, 9, 'df5bc6ecab7b9353575eaccd47414cd1.jpg', 'f08240e99fd44a55098708cc1cd4049a.jpg', 1, 2, 0, 113, 113, '2014-03-27 15:40:03', '2014-03-27 19:40:04'),
(95, 9, 'd819fce2946dddbbc46319db87938fd9.jpg', '02f859951665e6e5c52d42681ee0390d.jpg', 1, 1, 1, 113, 113, '2014-03-27 15:40:01', '2014-03-27 19:40:01'),
(50, 10, '7edbcfd843eb9ed1ada5480ecd746132.jpg', '4eb40abc52a4bd03cfc0a60fe7b7d187.jpg', 1, 1, 1, 113, 113, '2014-03-27 14:23:05', '2014-03-27 18:23:05'),
(51, 10, '2fb30209f79fd916623a50f6cf0761f4.jpg', '9674ef3ed22d9910edee83f47c5be852.jpg', 1, 2, 0, 113, 113, '2014-03-27 14:23:07', '2014-03-27 18:23:08'),
(52, 10, 'b15591efb8215239af30988c779859a0.jpg', '4b9902c66f97ec6a6de8374aab0b7bf2.jpg', 1, 3, 0, 113, 113, '2014-03-27 14:23:10', '2014-03-27 18:23:10'),
(53, 10, '98ee1b3586c621a0106a6cc66cf351de.jpg', 'ac3a09d1cd2888285f53c552c4b82d3f.jpg', 1, 4, 0, 113, 113, '2014-03-27 14:23:12', '2014-03-27 18:23:12'),
(54, 10, '2a3a56559d12380d7859292f8155c4a3.jpg', 'a741b9a3158eab7efd2b7b2fcb094196.jpg', 1, 5, 0, 113, 113, '2014-03-27 14:23:15', '2014-03-27 18:23:16'),
(55, 10, '6a37a5a72a9954045196e58e93cb754e.jpg', '6e1cfa0ecb85fcfe8142126b6c096ecb.jpg', 1, 6, 0, 113, 113, '2014-03-27 14:23:18', '2014-03-27 18:23:19'),
(56, 10, '1edf133853c485c4335a0228415436f0.jpg', 'd086194031d0d50f7a4c4b957a40fb04.jpg', 1, 7, 0, 113, 113, '2014-03-27 14:23:21', '2014-03-27 18:23:21'),
(57, 10, 'e93c2f5c0057b0e5792f298112edff44.jpg', '11fa7aa4dedd6efbb4b6e7b06cf942be.jpg', 1, 8, 0, 113, 113, '2014-03-27 14:23:24', '2014-03-27 18:23:24'),
(58, 11, 'f1c9e5a711119c50d3e7ddb5c5f35d2d.jpg', 'dcb020c237894297bf25f4a209d512ed.jpg', 1, 1, 1, 113, 113, '2014-03-27 15:10:04', '2014-03-27 19:10:05'),
(59, 11, 'dbad0af3797e4acb9cc021867da9408b.jpg', '5363132c98f3c67c5cb1df36b42dba6f.jpg', 1, 2, 0, 113, 113, '2014-03-27 15:10:07', '2014-03-27 19:10:07'),
(60, 11, 'a48f8f517f5414ce69f51f422098e1f6.jpg', '1ae8b4be9f775f03b189466c7322eb66.jpg', 1, 3, 0, 113, 113, '2014-03-27 15:10:09', '2014-03-27 19:10:09'),
(61, 11, '35cd225f6b380d0fe3b13d7807b463c0.jpg', '7844518ad69364ae26e4af7a2bb072dc.jpg', 1, 4, 0, 113, 113, '2014-03-27 15:10:12', '2014-03-27 19:10:13'),
(62, 11, 'a68559b783e44d17531395311a1b1a35.jpg', '0cd578c61684367c772ece8607b0de15.jpg', 1, 5, 0, 113, 113, '2014-03-27 15:10:15', '2014-03-27 19:10:15'),
(63, 11, '09ddd6d3c45d82986c31b20c84d0cea2.jpg', 'ae670f86348676c689acfb57024c94bc.jpg', 1, 6, 0, 113, 113, '2014-03-27 15:10:17', '2014-03-27 19:10:18'),
(64, 11, '12fa4d6f1d88c3dc7226f4b0a2033ef9.jpg', '79a442b2836abfd97c7f98039137fe61.jpg', 1, 7, 0, 113, 113, '2014-03-27 15:10:20', '2014-03-27 19:10:21'),
(65, 11, '057f7bfc035af67f734267d15b1c1af8.jpg', 'bb6483c0db1be94979e1530d3abc073f.jpg', 1, 8, 0, 113, 113, '2014-03-27 15:10:25', '2014-03-27 19:10:25'),
(66, 11, 'caf04851f59a854bfed6c2374a163171.jpg', '420abc1f69bb157e8fe6a3f332eceea3.jpg', 1, 9, 0, 113, 113, '2014-03-27 15:10:27', '2014-03-27 19:10:28'),
(67, 11, '6a824a01c43ef4818975b08fb426322a.jpg', '92a2670dc51861e9cf4e5408ee2e741e.jpg', 1, 10, 0, 113, 113, '2014-03-27 15:10:31', '2014-03-27 19:10:31'),
(68, 11, '388c1c983efdcba6b5ae1108fc119f95.jpg', '2bc76ed1956385c61f080feadf8ed1df.jpg', 1, 11, 0, 113, 113, '2014-03-27 15:10:33', '2014-03-27 19:10:34'),
(69, 11, '5734f9974f156d248ef0dd2b73a1fcb2.jpg', 'ef8a86c5a89755db964af6c5d4cf9ae5.jpg', 1, 12, 0, 113, 113, '2014-03-27 15:10:35', '2014-03-27 19:10:35'),
(70, 12, 'eb30594936225b63a0c2609760c662e6.jpg', '31a74442ec159dfa4b81f68b4afe7b28.jpg', 1, 1, 1, 113, 113, '2014-03-27 15:22:16', '2014-03-27 19:22:16'),
(71, 12, '2d187299bf451fe942706038620609b3.jpg', 'eba0431cd0f5935f47d9378c6f58b26c.jpg', 1, 2, 0, 113, 113, '2014-03-27 15:22:19', '2014-03-27 19:22:20'),
(72, 12, '0667ddefc76957928ad7bfe3dd76703c.jpg', 'bda564ff4c1dea5dcf9674180fc534c4.jpg', 1, 3, 0, 113, 113, '2014-03-27 15:22:22', '2014-03-27 19:22:23'),
(73, 12, '6a0aa742c1d75ec70881a069ac1e86cd.jpg', '5f478a0da65fbd6157ea8af35fed2f0c.jpg', 1, 4, 0, 113, 113, '2014-03-27 15:22:26', '2014-03-27 19:22:26'),
(74, 12, '633444110e866f7799ecc568e8000771.jpg', 'a50f472fa822f79cd4efdeef16c9be20.jpg', 1, 5, 0, 113, 113, '2014-03-27 15:22:30', '2014-03-27 19:22:30'),
(75, 12, 'e2dbcce5943d4c7d119a0ebe5f8792f2.jpg', '232d39f082e56b84383ac5f6a1b80652.jpg', 1, 6, 0, 113, 113, '2014-03-27 15:22:32', '2014-03-27 19:22:33'),
(76, 12, '9290071196373628761b83110d5e15c5.jpg', 'da2b26b66ad2207db23ef322dc646606.jpg', 1, 7, 0, 113, 113, '2014-03-27 15:22:35', '2014-03-27 19:22:35'),
(92, 4, '96c39a9b3f0b2004f15fddae4ba16912.jpg', '02c91ee06d23df1da47ee0548ec63f15.jpg', 1, 4, 0, 113, 113, '2014-03-27 15:31:14', '2014-03-27 19:31:14'),
(91, 4, '99d45347ef81f664b17cc108ee231e24.jpg', 'b1290a8d1399379ad24fc37138b221f0.jpg', 1, 3, 0, 113, 113, '2014-03-27 15:31:12', '2014-03-27 19:31:12'),
(90, 4, 'c72bc2380a2dec72fa04394b0158c00a.jpg', '2d17805127c847ff767f2d179e2e34a7.jpg', 1, 2, 0, 113, 113, '2014-03-27 15:31:10', '2014-03-27 19:31:10'),
(89, 4, '020b9544bb6917a03d33cb081f4cd4cd.jpg', 'df6f9143c22edbbefe0ba15cf868b340.jpg', 1, 1, 1, 113, 113, '2014-03-27 15:31:08', '2014-03-27 19:31:08'),
(104, 13, '55a064178a114e7507fe18809af9a1d3.jpg', '974735681df2809b9f75bc2755c6bf2e.jpg', 1, 2, 0, 113, 113, '2014-03-27 16:38:51', '2014-03-27 20:38:51'),
(105, 13, 'd18cc795351e396f1e344e4319059a9f.jpg', '9011d67c9ed00d74c3e3549ccddd4334.jpg', 1, 3, 0, 113, 113, '2014-03-27 16:38:52', '2014-03-27 20:38:53'),
(106, 13, '9447b3a3b66b948d1b9f60b683799caf.jpg', '15d6c8c016ba1047bbc0c2a856ff7921.jpg', 1, 4, 0, 113, 113, '2014-03-27 16:38:55', '2014-03-27 20:38:55'),
(107, 13, 'ef357f2277642c29f2cf3924e8b651a9.jpg', '56d8779c0e636a5b2d4c70e500cedd92.jpg', 1, 5, 0, 113, 113, '2014-03-27 16:38:57', '2014-03-27 20:38:57'),
(108, 13, '158c2dcd88388484a1ab8e146bc5bb55.jpg', 'fb04c54543c25322ce74a6ee505549e8.jpg', 1, 6, 0, 113, 113, '2014-03-27 16:39:00', '2014-03-27 20:39:01'),
(109, 13, 'fa99b74c5dc8bfa3443cfb69f915487f.jpg', 'aa3a7a2f378fdf6087f4caefb4d2d9df.jpg', 1, 7, 0, 113, 113, '2014-03-27 16:39:03', '2014-03-27 20:39:03'),
(110, 13, 'a2bfcd4c894f9c85aca81e4912c5e90f.jpg', '3c8895802aedaadb775201cfcc295df5.jpg', 1, 8, 0, 113, 113, '2014-03-27 16:39:04', '2014-03-27 20:39:05'),
(111, 13, '342f0ca2119b4e116c284fbc9e7e35ec.jpg', '109a1c4c3a10fc556dd2d53e91c996cb.jpg', 1, 9, 0, 113, 113, '2014-03-27 16:39:07', '2014-03-27 20:39:07'),
(112, 13, '29e7ed6fd11db0226b02be3a679570d7.jpg', 'c9ce5c925f2d27a544a015ba6a3c49ba.jpg', 1, 10, 0, 113, 113, '2014-03-27 16:39:09', '2014-03-27 20:39:09'),
(113, 13, '9a246f9e8e87847c638cd0eba8e58e11.jpg', 'fa23ff67bdc858398718866085e9a500.jpg', 1, 11, 0, 113, 113, '2014-03-27 16:39:12', '2014-03-27 20:39:12'),
(114, 14, '8c69dbb4fd6f722f9273ca088946eb4d.jpg', '12164adccf7285f2418c62655142bf8c.jpg', 1, 1, 1, 113, 113, '2014-03-27 16:47:43', '2014-03-27 20:47:43'),
(115, 14, '18ec23e532239b5077e32c6646271c6c.jpg', 'fa5b11c00b7dfedc0b960d9ca6f97a45.jpg', 1, 2, 0, 113, 113, '2014-03-27 16:47:46', '2014-03-27 20:47:46'),
(116, 14, 'd41054be94082924b637cc202844b7a6.jpg', '2ca1ba8490d2cd1c4f8a738e68c9fdc6.jpg', 1, 3, 0, 113, 113, '2014-03-27 16:47:48', '2014-03-27 20:47:48'),
(117, 14, 'ac90e21f88ab8a07972eae36dd421a13.jpg', '92839bd31323029140b5db2fcb4b764a.jpg', 1, 4, 0, 113, 113, '2014-03-27 16:47:51', '2014-03-27 20:47:51'),
(118, 14, '8d4b69af92bab8e813707e8b1b7f1369.jpg', 'ced3e02edba19ca1dc4092b96f91c50b.jpg', 1, 5, 0, 113, 113, '2014-03-27 16:47:53', '2014-03-27 20:47:53'),
(119, 14, '49f37b53c4a0b0441b3cad1f048fe6d9.jpg', 'af17abbdd35c15a41f6930667232b8c1.jpg', 1, 6, 0, 113, 113, '2014-03-27 16:47:55', '2014-03-27 20:47:55'),
(120, 14, 'c3f5630994db79874256a3244dc87400.jpg', 'e7cf0185ccbfe8d661e88e4684001874.jpg', 1, 7, 0, 113, 113, '2014-03-27 16:47:58', '2014-03-27 20:47:58'),
(121, 14, '7f9d2ea8e6b5c19dad20a44af2f00e0c.jpg', 'cd4dc8c0f0bbfb3da78d1fe1f70e85c9.jpg', 1, 8, 0, 113, 113, '2014-03-27 16:48:01', '2014-03-27 20:48:01'),
(122, 15, 'dc28439f7e70e0c334d110cace5394af.jpg', '6a6d104e384facb39517e25d14175ff5.jpg', 1, 1, 1, 113, 113, '2014-03-27 17:37:04', '2014-03-27 21:37:05'),
(123, 15, 'd0f8ba7665c414536e0a5797c7b96dba.jpg', '7924002b87aa74aea3bf9bb75baf40ef.jpg', 1, 2, 0, 113, 113, '2014-03-27 17:37:06', '2014-03-27 21:37:07'),
(124, 15, '966728ab21c9809b1cb84b4e7563d0b5.jpg', '2987c426706285ac2092cb67cded226d.jpg', 1, 3, 0, 113, 113, '2014-03-27 17:37:08', '2014-03-27 21:37:09'),
(125, 15, '3ba27f30ae235895cd26b28074d0ecb8.jpg', '900594cdaedd39cfe14be3a37857e8be.jpg', 1, 4, 0, 113, 113, '2014-03-27 17:37:11', '2014-03-27 21:37:11'),
(126, 15, 'c09f89f62dc65b9924210ef892cab482.jpg', '00a6a99e3fab79dc0b571ad182824c7b.jpg', 1, 5, 0, 113, 113, '2014-03-27 17:37:13', '2014-03-27 21:37:13'),
(127, 15, 'e82ef7e1d4b52acb0d63fa2a8d23da9f.jpg', '6acae6403d91096acc3d71cdfae697a4.jpg', 1, 6, 0, 113, 113, '2014-03-27 17:37:16', '2014-03-27 21:37:16'),
(128, 15, '060b4fe409c768e2824e4e33e05f473e.jpg', 'aef003d5f3103a13877fa2cbf53ae0cc.jpg', 1, 7, 0, 113, 113, '2014-03-27 17:37:19', '2014-03-27 21:37:19'),
(129, 15, 'e85d85f5fc6ea47802bf60a6b8c8db89.jpg', '3ed9367594b87292af375ff3042f882a.jpg', 1, 8, 0, 113, 113, '2014-03-27 17:37:21', '2014-03-27 21:37:21'),
(130, 16, 'f50439863490c34b740f5b8c331a4fdb.jpg', 'be253cb356f82640bd7f82d59bbb933b.jpg', 1, 1, 1, 113, 113, '2014-03-27 18:20:24', '2014-03-27 22:20:24'),
(131, 16, '945c7cb5f0d22188b85f81ea81d4d584.jpg', 'c9924a16ffd5db4d60ef8fdac90d5d0e.jpg', 1, 2, 0, 113, 113, '2014-03-27 18:20:26', '2014-03-27 22:20:26'),
(132, 16, '1a25bf856a781eacf79352f77d262d4b.jpg', '4d1106b15bf75e6734a3302f4b0ef25b.jpg', 1, 3, 0, 113, 113, '2014-03-27 18:20:28', '2014-03-27 22:20:29'),
(133, 16, '7375527ffa44588dfe743b378bf76d3e.jpg', 'e9ae4777eb9b927e6bfb8fc0d8c611d6.jpg', 1, 4, 0, 113, 113, '2014-03-27 18:20:31', '2014-03-27 22:20:31'),
(134, 17, '8b63d650caf7abd3e192bb8c0d1eb610.jpg', 'b1b8917ab44d58c8fd442153a3421078.jpg', 1, 1, 1, 113, 113, '2014-03-27 18:27:11', '2014-03-27 22:27:11'),
(135, 17, '381a9a6d7fb370d3f62382280ccf74c7.jpg', '035619b6fac6c6db04bb0d622bc4f1bd.jpg', 1, 2, 0, 113, 113, '2014-03-27 18:27:14', '2014-03-27 22:27:15'),
(136, 17, '8927e625b7873f3458c28d78487fb71a.jpg', '79aa41f002fead14f5046e2cef7a5992.jpg', 1, 3, 0, 113, 113, '2014-03-27 18:27:18', '2014-03-27 22:27:19');

-- --------------------------------------------------------

--
-- Table structure for table `product_image_variant`
--

CREATE TABLE IF NOT EXISTS `product_image_variant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `variant_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `displayed_in_listing` tinyint(1) unsigned NOT NULL,
  `sort_order` smallint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `displayed_in_listing` (`displayed_in_listing`),
  KEY `id_product_2` (`id_product`,`displayed_in_listing`),
  KEY `id_user_created` (`id_user_created`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_image_variant_description`
--

CREATE TABLE IF NOT EXISTS `product_image_variant_description` (
  `id_product_image_variant` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'max char for seo in title bar is 65',
  UNIQUE KEY `id_product` (`id_product_image_variant`,`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_image_variant_image`
--

CREATE TABLE IF NOT EXISTS `product_image_variant_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_image_variant` int(10) unsigned NOT NULL,
  `original` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `force_crop` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `cover` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product_image_variant` (`id_product_image_variant`),
  KEY `id_product_image_variant_2` (`id_product_image_variant`,`cover`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_image_variant_option`
--

CREATE TABLE IF NOT EXISTS `product_image_variant_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_image_variant` int(10) unsigned NOT NULL,
  `id_product_variant_group` int(10) unsigned NOT NULL,
  `id_product_variant_group_option` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product_image_variant` (`id_product_image_variant`),
  KEY `id_product_variant_group_2` (`id_product_variant_group`),
  KEY `id_product_variant_group_option` (`id_product_variant_group_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_options_group`
--

CREATE TABLE IF NOT EXISTS `product_options_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_options_group` int(10) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_options_group` (`id_options_group`),
  KEY `id_product` (`id_product`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_product_2` (`id_product`,`id_options_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_price_shipping_region`
--

CREATE TABLE IF NOT EXISTS `product_price_shipping_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `price` decimal(13,2) NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_product_2` (`id_product`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_price_tier`
--

CREATE TABLE IF NOT EXISTS `product_price_tier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_customer_type` int(10) unsigned NOT NULL,
  `qty` smallint(1) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `id_customer_type` (`id_customer_type`),
  KEY `qty` (`qty`),
  KEY `price` (`price`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_product_2` (`id_product`,`id_customer_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_rating_count`
--

CREATE TABLE IF NOT EXISTS `product_rating_count` (
  `id_product` int(10) unsigned NOT NULL,
  `avg_rating` tinyint(1) unsigned NOT NULL,
  `total_rating` int(10) unsigned NOT NULL COMMENT 'Number of times this product was rated.',
  PRIMARY KEY (`id_product`),
  KEY `id_product` (`id_product`,`avg_rating`),
  KEY `total_rating` (`total_rating`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_related`
--

CREATE TABLE IF NOT EXISTS `product_related` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_related` int(10) unsigned NOT NULL,
  `discount_type` tinyint(1) unsigned NOT NULL COMMENT '0=price, 1=percentage',
  `discount` decimal(10,2) unsigned NOT NULL,
  `apply_discount` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product` (`id_product`,`id_product_related`),
  KEY `sort_order` (`sort_order`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `apply_discount` (`apply_discount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_review`
--

CREATE TABLE IF NOT EXISTS `product_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Title of review',
  `review` text COLLATE utf8_unicode_ci NOT NULL,
  `anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Wich site',
  `rated` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Rated 1 to 5 and 5 is the best',
  `approved` tinyint(1) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_approved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_user_modified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `id_customer` (`id_customer`),
  KEY `anonymous` (`anonymous`),
  KEY `language_code` (`language_code`),
  KEY `rated` (`rated`),
  KEY `approved` (`approved`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_ship_only_region`
--

CREATE TABLE IF NOT EXISTS `product_ship_only_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `id_product_2` (`id_product`,`country_code`,`state_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_suggestion`
--

CREATE TABLE IF NOT EXISTS `product_suggestion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_suggestion` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product` (`id_product`,`id_product_suggestion`),
  KEY `sort_order` (`sort_order`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_tag`
--

CREATE TABLE IF NOT EXISTS `product_tag` (
  `id_product` int(10) unsigned NOT NULL,
  `id_tag` int(10) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  UNIQUE KEY `id_product` (`id_product`,`id_tag`),
  KEY `id_user_created` (`id_user_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE IF NOT EXISTS `product_variant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `variant_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sku` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `price_type` tinyint(1) unsigned NOT NULL COMMENT '0 = fixed, 1 = percentage',
  `cost_price` decimal(13,2) unsigned NOT NULL,
  `price` decimal(13,2) unsigned NOT NULL,
  `qty` smallint(1) NOT NULL COMMENT 'qty in stock',
  `notify_qty` tinyint(1) unsigned NOT NULL COMMENT 'notify if qty is below or equal to',
  `weight` decimal(10,1) unsigned NOT NULL,
  `length` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `in_stock` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `sort_order` smallint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `sku` (`sku`),
  KEY `price_type` (`price_type`),
  KEY `notify_qty` (`notify_qty`),
  KEY `in_stock` (`in_stock`),
  KEY `active` (`active`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_description`
--

CREATE TABLE IF NOT EXISTS `product_variant_description` (
  `id_product_variant` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'max char for seo in title bar is 65',
  UNIQUE KEY `id_product` (`id_product_variant`,`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_group`
--

CREATE TABLE IF NOT EXISTS `product_variant_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) unsigned NOT NULL,
  `input_type` tinyint(1) unsigned NOT NULL COMMENT '0 = dropdown, 1 = radio, 2 = swatch',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`id_product`),
  KEY `input_type` (`input_type`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_group_description`
--

CREATE TABLE IF NOT EXISTS `product_variant_group_description` (
  `id_product_variant_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_variant_group` (`id_product_variant_group`,`language_code`),
  KEY `language_code` (`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_group_option`
--

CREATE TABLE IF NOT EXISTS `product_variant_group_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_variant_group` int(10) unsigned NOT NULL,
  `swatch_type` tinyint(1) unsigned NOT NULL COMMENT '0 = one color, 1 = two colors, 2 = three colors, 3 = file',
  `color` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color2` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color3` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product_variant_group` (`id_product_variant_group`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_group_option_description`
--

CREATE TABLE IF NOT EXISTS `product_variant_group_option_description` (
  `id_product_variant_group_option` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_variant_group_option` (`id_product_variant_group_option`,`language_code`),
  KEY `id_product_variant_group_optio_2` (`id_product_variant_group_option`),
  KEY `language_code` (`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_option`
--

CREATE TABLE IF NOT EXISTS `product_variant_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product_variant` int(10) unsigned NOT NULL,
  `id_product_variant_group` int(10) unsigned NOT NULL,
  `id_product_variant_group_option` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product_variant` (`id_product_variant`,`id_product_variant_group`,`id_product_variant_group_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pub`
--

CREATE TABLE IF NOT EXISTS `pub` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `width` smallint(4) NOT NULL COMMENT 'width of the pub',
  `display_in_column` tinyint(4) NOT NULL COMMENT '0 = left column, 1 = right column',
  `display_in_page` tinyint(4) NOT NULL COMMENT '0 = home page only, 1 = every page',
  `display_start_date` datetime NOT NULL,
  `display_end_date` datetime NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `display_start_date` (`display_start_date`),
  KEY `display_end_date` (`display_end_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pub`
--

INSERT INTO `pub` (`id`, `name`, `width`, `display_in_column`, `display_in_page`, `display_start_date`, `display_end_date`, `sort_order`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(3, 'POKER RUN', 0, 0, 0, '2014-03-21 00:00:00', '2014-06-28 00:00:00', 0, 1, 113, 113, '2014-03-21 16:20:44', '2014-03-21 20:20:44'),
(4, 'DÉFI LÉO 2014', 0, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 1, 113, 113, '2014-03-21 16:26:03', '2014-03-21 20:26:03');

-- --------------------------------------------------------

--
-- Table structure for table `pub_description`
--

CREATE TABLE IF NOT EXISTS `pub_description` (
  `id_pub` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `url_type` tinyint(1) unsigned NOT NULL COMMENT '0 = no url, 1 = url, 2 = cmspage, 3 = subscription / contest',
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `target_blank` tinyint(1) unsigned NOT NULL,
  `id_cmspage` int(10) unsigned NOT NULL,
  `id_subscription_contest` int(10) unsigned NOT NULL,
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_pub`,`language_code`),
  KEY `id_cmspage` (`id_cmspage`),
  KEY `id_subscription_contest` (`id_subscription_contest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pub_description`
--

INSERT INTO `pub_description` (`id_pub`, `language_code`, `url_type`, `url`, `target_blank`, `id_cmspage`, `id_subscription_contest`, `filename`) VALUES
(3, 'fr', 2, '', 0, 26, 0, 'dd65cab3ea50938ff7566fbbcbf38847.jpg'),
(3, 'en', 0, '', 0, 0, 0, ''),
(4, 'fr', 2, '', 0, 26, 0, '35798c2020ee8f4e014e5f566db14f7f.jpg'),
(4, 'en', 0, '', 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `rebate_coupon`
--

CREATE TABLE IF NOT EXISTS `rebate_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL COMMENT '0 = Percent/Fixed amount off product price, 1 = Percent/Fixed amount off cart total, 2 = Buy X Get Y, 3 = Free gift with $ purchase, 4 = Free shipping, 5 = Percent/Fixed amount off first purchase',
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `coupon` tinyint(1) unsigned NOT NULL COMMENT '0 = Rebate, 1 = Coupon',
  `coupon_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `coupon_max_usage` tinyint(1) unsigned NOT NULL,
  `coupon_max_usage_customer` tinyint(1) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `all_product` tinyint(1) unsigned NOT NULL COMMENT 'all product must be in the cart',
  `applicable_on_sale` tinyint(1) unsigned NOT NULL COMMENT 'applies to items on sale',
  `min_cart_value` decimal(10,2) unsigned NOT NULL COMMENT 'min cart value required',
  `max_weight` tinyint(1) unsigned NOT NULL COMMENT 'Max weight for Free shipping',
  `discount_type` tinyint(1) unsigned NOT NULL COMMENT '0 = fixed, 1 = percentage',
  `discount` decimal(10,2) unsigned NOT NULL,
  `min_qty_required` tinyint(1) unsigned NOT NULL COMMENT 'min qty required of specified product in cart',
  `max_qty_allowed` smallint(1) unsigned NOT NULL,
  `buy_x_qty` tinyint(1) unsigned NOT NULL,
  `get_y_qty` tinyint(1) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_product` (`type`),
  KEY `name` (`name`),
  KEY `coupon_code` (`coupon_code`),
  KEY `applicable_on_sale` (`applicable_on_sale`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `min_qty_required` (`min_qty_required`),
  KEY `buy_x_qty` (`buy_x_qty`),
  KEY `discount_type` (`discount_type`),
  KEY `discount` (`discount`),
  KEY `coupon` (`coupon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rebate_coupon_category`
--

CREATE TABLE IF NOT EXISTS `rebate_coupon_category` (
  `id_rebate_coupon` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_rebate` (`id_rebate_coupon`,`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rebate_coupon_description`
--

CREATE TABLE IF NOT EXISTS `rebate_coupon_description` (
  `id_rebate_coupon` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_rebate_coupon`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rebate_coupon_product`
--

CREATE TABLE IF NOT EXISTS `rebate_coupon_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_rebate_coupon` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_rebate_coupon` (`id_rebate_coupon`),
  KEY `id_product` (`id_product`),
  KEY `id_rebate_coupon_2` (`id_rebate_coupon`,`id_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scorm_certificate`
--

CREATE TABLE IF NOT EXISTS `scorm_certificate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scorm_certificate_additional_field`
--

CREATE TABLE IF NOT EXISTS `scorm_certificate_additional_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scorm_certificate_additional_field_value`
--

CREATE TABLE IF NOT EXISTS `scorm_certificate_additional_field_value` (
  `id_scorm_cetificate_additional_field` int(11) NOT NULL,
  `id_scorm_certificate_product` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `id_ scorm_cetificate_additional_field` (`id_scorm_cetificate_additional_field`,`id_scorm_certificate_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scorm_certificate_condition`
--

CREATE TABLE IF NOT EXISTS `scorm_certificate_condition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_scorm_certificate_product` int(11) NOT NULL,
  `id_custom_fields` int(11) NOT NULL COMMENT 'If -1 it means We have to look in the score_from and score_to',
  `id_custom_fields_option` int(11) NOT NULL COMMENT 'If -1 it means Single Check box to true, If -2 it means Single Check box to false',
  `score_from` tinyint(4) NOT NULL,
  `score_to` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_ scorm_certificate_product` (`id_scorm_certificate_product`,`id_custom_fields`,`id_custom_fields_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scorm_certificate_product`
--

CREATE TABLE IF NOT EXISTS `scorm_certificate_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_product_downloadable_files` int(11) NOT NULL,
  `id_scorm_certificate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_product_downloadable_files` (`id_product_downloadable_files`,`id_scorm_certificate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_gateway`
--

CREATE TABLE IF NOT EXISTS `shipping_gateway` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `access_key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_password` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `meter_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `hide_arrival_date` tinyint(1) NOT NULL COMMENT 'If the shipping company dont return an approx. date arrival',
  `active` tinyint(1) unsigned NOT NULL,
  `class` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the class to use for shipping',
  `logo` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the PNG File.',
  `provides_extra_care` tinyint(1) NOT NULL COMMENT 'If 1 then we will show the check box in shipping section of the product.',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `access_key` (`access_key`),
  KEY `merchant_id` (`merchant_id`),
  KEY `merchant_password` (`merchant_password`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `shipping_gateway`
--

INSERT INTO `shipping_gateway` (`id`, `name`, `access_key`, `merchant_id`, `merchant_password`, `meter_number`, `hide_arrival_date`, `active`, `class`, `logo`, `provides_extra_care`) VALUES
(1, 'UPS', '9C7084D4CE2139F8', 'Simple Commerce', 'Ascp74653', '118527899', 0, 0, 'UPSShipping', 'shipping-ups-logo.png', 0),
(2, 'Canada Post', '', 'CPC_SEBCHINPETE_INC', '', '118527899', 0, 0, 'CanadaPostShipping', 'shipping-canada-post-logo.png', 0),
(3, 'Canpar', '510087585', 'Ra8hEtV8XofqlYiO', 't6oDriPoMH5oeg9aIFevUWEaa', '118527899', 0, 0, 'CanparShipping', 'shipping-canpar-logo.jpg', 1),
(4, 'FedEx', '510087585', 'Ra8hEtV8XofqlYiO', 't6oDriPoMH5oeg9aIFevUWEaa', '118527899', 1, 0, 'FedExShipping', 'shipping-fedex-logo.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE IF NOT EXISTS `state` (
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `country_code` (`country_code`,`code`),
  KEY `country_code_2` (`country_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`country_code`, `code`) VALUES
('CA', 'AB'),
('CA', 'BC'),
('CA', 'MB'),
('CA', 'NB'),
('CA', 'NL'),
('CA', 'NS'),
('CA', 'NT'),
('CA', 'NU'),
('CA', 'ON'),
('CA', 'PE'),
('CA', 'QC'),
('CA', 'SK'),
('CA', 'YT'),
('US', 'AK'),
('US', 'AL'),
('US', 'AR'),
('US', 'AZ'),
('US', 'CA'),
('US', 'CO'),
('US', 'CT'),
('US', 'DE'),
('US', 'FL'),
('US', 'GA'),
('US', 'HI'),
('US', 'IA'),
('US', 'ID'),
('US', 'IL'),
('US', 'IN'),
('US', 'KS'),
('US', 'KY'),
('US', 'LA'),
('US', 'MA'),
('US', 'MD'),
('US', 'ME'),
('US', 'MI'),
('US', 'MN'),
('US', 'MO'),
('US', 'MS'),
('US', 'MT'),
('US', 'NC'),
('US', 'ND'),
('US', 'NE'),
('US', 'NH'),
('US', 'NJ'),
('US', 'NM'),
('US', 'NV'),
('US', 'NY'),
('US', 'OH'),
('US', 'OK'),
('US', 'OR'),
('US', 'PA'),
('US', 'RI'),
('US', 'SC'),
('US', 'SD'),
('US', 'TN'),
('US', 'TX'),
('US', 'UT'),
('US', 'VA'),
('US', 'VT'),
('US', 'WA'),
('US', 'WI'),
('US', 'WV'),
('US', 'WY');

-- --------------------------------------------------------

--
-- Table structure for table `state_description`
--

CREATE TABLE IF NOT EXISTS `state_description` (
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `state_code` (`state_code`,`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `state_description`
--

INSERT INTO `state_description` (`state_code`, `language_code`, `name`) VALUES
('AB', 'en', 'Alberta'),
('BC', 'en', 'British Columbia'),
('MB', 'en', 'Manitoba'),
('NB', 'en', 'New Brunswick'),
('NL', 'en', 'Newfoundland and Labrador'),
('NT', 'en', 'Northwest Territories'),
('NS', 'en', 'Nova Scotia'),
('NU', 'en', 'Nunavut'),
('ON', 'en', 'Ontario'),
('PE', 'en', 'Prince Edward Island'),
('QC', 'en', 'Quebec'),
('SK', 'en', 'Saskatchewan'),
('YT', 'en', 'Yukon'),
('AB', 'fr', 'Alberta'),
('BC', 'fr', 'Colombie-Britannique'),
('MB', 'fr', 'Manitoba'),
('NB', 'fr', 'Nouveau-Brunswick'),
('NL', 'fr', 'Terre-Neuve et Labrador'),
('NT', 'fr', 'Territoires du Nord-Ouest'),
('NS', 'fr', 'Nouvelle-Écosse'),
('NU', 'fr', 'Nunavut'),
('ON', 'fr', 'Ontario'),
('PE', 'fr', 'Île-du-Prince-Édouard'),
('QC', 'fr', 'Québec'),
('SK', 'fr', 'Saskatchewan'),
('YT', 'fr', 'Yukon'),
('AL', 'en', 'Alabama'),
('AK', 'en', 'Alaska'),
('AZ', 'en', 'Arizona'),
('AR', 'en', 'Arkansas'),
('CA', 'en', 'California'),
('CO', 'en', 'Colorado'),
('CT', 'en', 'Connecticut'),
('DE', 'en', 'Delaware'),
('FL', 'en', 'Florida'),
('GA', 'en', 'Georgia'),
('HI', 'en', 'Hawaii'),
('ID', 'en', 'Idaho'),
('IL', 'en', 'Illinois'),
('IN', 'en', 'Indiana'),
('IA', 'en', 'Iowa'),
('KS', 'en', 'Kansas'),
('KY', 'en', 'Kentucky'),
('LA', 'en', 'Louisiana'),
('ME', 'en', 'Maine'),
('MD', 'en', 'Maryland'),
('MA', 'en', 'Massachusetts'),
('MI', 'en', 'Michigan'),
('MN', 'en', 'Minnesota'),
('MS', 'en', 'Mississippi'),
('MO', 'en', 'Missouri'),
('MT', 'en', 'Montana'),
('NE', 'en', 'Nebraska'),
('NV', 'en', 'Nevada'),
('NH', 'en', 'New Hampshire'),
('NJ', 'en', 'New Jersey'),
('NM', 'en', 'New Mexico'),
('NY', 'en', 'New York'),
('NC', 'en', 'North Carolina'),
('ND', 'en', 'North Dakota'),
('OH', 'en', 'Ohio'),
('OK', 'en', 'Oklahoma'),
('OR', 'en', 'Oregon'),
('PA', 'en', 'Pennsylvania'),
('RI', 'en', 'Rhode Island'),
('SC', 'en', 'South Carolina'),
('SD', 'en', 'South Dakota'),
('TN', 'en', 'Tennessee'),
('TX', 'en', 'Texas'),
('UT', 'en', 'Utah'),
('VT', 'en', 'Vermont'),
('VA', 'en', 'Virginia'),
('WA', 'en', 'Washington'),
('WV', 'en', 'West Virginia'),
('WI', 'en', 'Wisconsin'),
('WY', 'en', 'Wyoming'),
('AL', 'fr', 'Alabama'),
('AK', 'fr', 'Alaska'),
('AZ', 'fr', 'Arizona'),
('AR', 'fr', 'Arkansas'),
('CA', 'fr', 'Californie'),
('CO', 'fr', 'Colorado'),
('CT', 'fr', 'Connecticut'),
('DE', 'fr', 'Delaware'),
('FL', 'fr', 'Floride'),
('GA', 'fr', 'Géorgie'),
('HI', 'fr', 'Hawaï'),
('ID', 'fr', 'Idaho'),
('IL', 'fr', 'Illinois'),
('IN', 'fr', 'Indiana'),
('IA', 'fr', 'Iowa'),
('KS', 'fr', 'Kansas'),
('KY', 'fr', 'Kentucky'),
('LA', 'fr', 'Louisiane'),
('ME', 'fr', 'Maine'),
('MD', 'fr', 'Maryland'),
('MA', 'fr', 'Massachusetts'),
('MI', 'fr', 'Michigan'),
('MN', 'fr', 'Minnesota'),
('MS', 'fr', 'Mississippi'),
('MO', 'fr', 'Missouri'),
('MT', 'fr', 'Montana'),
('NE', 'fr', 'Nebraska'),
('NV', 'fr', 'Nevada'),
('NH', 'fr', 'New Hampshire'),
('NJ', 'fr', 'New Jersey'),
('NM', 'fr', 'Nouveau-Mexique'),
('NY', 'fr', 'New York'),
('NC', 'fr', 'Caroline du Nord'),
('ND', 'fr', 'Dakota du Nord'),
('OH', 'fr', 'Ohio'),
('OK', 'fr', 'Oklahoma'),
('OR', 'fr', 'Oregon'),
('PA', 'fr', 'Pennsylvanie'),
('RI', 'fr', 'Rhode Island'),
('SC', 'fr', 'Caroline du Sud'),
('SD', 'fr', 'Dakota du Sud'),
('TN', 'fr', 'Tennessee'),
('TX', 'fr', 'Texas'),
('UT', 'fr', 'Utah'),
('VT', 'fr', 'Vermont'),
('VA', 'fr', 'Virginie'),
('WA', 'fr', 'Washington'),
('WV', 'fr', 'Virginie-Occidentale'),
('WI', 'fr', 'Wisconsin'),
('WY', 'fr', 'Wyoming');

-- --------------------------------------------------------

--
-- Table structure for table `store_locations`
--

CREATE TABLE IF NOT EXISTS `store_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer_address` int(10) unsigned NOT NULL COMMENT 'Use if transfert from customer address',
  `hide_address` tinyint(1) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `lat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lng` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `open_mon` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_mon_start_time` time NOT NULL,
  `open_mon_end_time` time NOT NULL,
  `open_tue` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_tue_start_time` time NOT NULL,
  `open_tue_end_time` time NOT NULL,
  `open_wed` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_wed_start_time` time NOT NULL,
  `open_wed_end_time` time NOT NULL,
  `open_thu` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_thu_start_time` time NOT NULL,
  `open_thu_end_time` time NOT NULL,
  `open_fri` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_fri_start_time` time NOT NULL,
  `open_fri_end_time` time NOT NULL,
  `open_sat` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_sat_start_time` time NOT NULL,
  `open_sat_end_time` time NOT NULL,
  `open_sun` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  `open_sun_start_time` time NOT NULL,
  `open_sun_end_time` time NOT NULL,
  `active` tinyint(1) unsigned NOT NULL COMMENT '0 = no, 1 = yes',
  PRIMARY KEY (`id`),
  KEY `id_customer_address` (`id_customer_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_contest`
--

CREATE TABLE IF NOT EXISTS `subscription_contest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `contest` tinyint(1) unsigned NOT NULL COMMENT '0 = Subscription, 1 = Contest',
  `customer_only` tinyint(3) unsigned NOT NULL,
  `include_form_address` tinyint(3) unsigned NOT NULL,
  `include_form_telephone` tinyint(3) unsigned NOT NULL,
  `id_rebate_coupon` tinyint(3) unsigned NOT NULL,
  `coupon_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `active` (`active`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `coupon` (`contest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_contest_description`
--

CREATE TABLE IF NOT EXISTS `subscription_contest_description` (
  `id_subscription_contest` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_subscription_contest`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_contest_person`
--

CREATE TABLE IF NOT EXISTS `subscription_contest_person` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL COMMENT 'If it''s for customer only',
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_subscription_contest` int(10) unsigned NOT NULL,
  `gender` tinyint(4) NOT NULL,
  `contest_winner` tinyint(3) unsigned NOT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `sendmail_failed` tinyint(4) NOT NULL COMMENT 'if mail failed',
  PRIMARY KEY (`id`),
  KEY `language_code` (`language_code`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visited_qty` int(10) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `visited_qty` (`visited_qty`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag_description`
--

CREATE TABLE IF NOT EXISTS `tag_description` (
  `id_tag` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tag` (`id_tag`,`language_code`),
  KEY `alias` (`alias`),
  KEY `id_tag_2` (`id_tag`,`language_code`,`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax`
--

CREATE TABLE IF NOT EXISTS `tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `tax_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `tax`
--

INSERT INTO `tax` (`id`, `code`, `tax_number`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 'TPS', 'À insérer si nécessaire', 113, 99, '2013-05-20 08:04:36', '2013-05-20 12:04:36'),
(2, 'TVQ', 'À insérer si nécessaire', 113, 113, '2013-05-20 08:05:39', '2013-05-20 12:05:39'),
(3, 'TVH', 'À insérer si nécessaire', 113, 113, '2013-05-20 08:06:15', '2013-05-20 12:06:15');

-- --------------------------------------------------------

--
-- Table structure for table `tax_description`
--

CREATE TABLE IF NOT EXISTS `tax_description` (
  `id_tax` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tax` (`id_tax`,`language_code`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tax_description`
--

INSERT INTO `tax_description` (`id_tax`, `language_code`, `name`) VALUES
(1, 'fr', 'TPS - test'),
(1, 'en', 'GST'),
(2, 'fr', 'TVQ'),
(2, 'en', 'QST'),
(3, 'fr', 'TVH'),
(3, 'en', 'HST');

-- --------------------------------------------------------

--
-- Table structure for table `tax_group`
--

CREATE TABLE IF NOT EXISTS `tax_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`),
  KEY `date_modified` (`date_modified`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `tax_group`
--

INSERT INTO `tax_group` (`id`, `name`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 'test', 99, 99, '2013-12-18 10:40:30', '2013-12-18 15:40:30'),
(2, 'bouff', 99, 99, '2013-12-18 10:40:55', '2013-12-18 15:40:55');

-- --------------------------------------------------------

--
-- Table structure for table `tax_rule`
--

CREATE TABLE IF NOT EXISTS `tax_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `state_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip_from` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `zip_to` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `country_code` (`country_code`),
  KEY `state_code` (`state_code`),
  KEY `zip_from` (`zip_from`),
  KEY `zip_to` (`zip_to`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dumping data for table `tax_rule`
--

INSERT INTO `tax_rule` (`id`, `name`, `country_code`, `state_code`, `zip_from`, `zip_to`, `active`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 'Québec - TPS-5%/TVQ-9.975', 'CA', 'QC', '', '', 1, 113, 113, '2013-05-19 22:40:15', '2013-05-20 02:40:15'),
(2, 'Alberta - TPS-5%', 'CA', 'AB', '', '', 1, 113, 113, '2013-05-20 08:09:01', '2013-05-20 12:09:01'),
(3, 'Ontario - TVH-13%', 'CA', 'ON', '', '', 1, 113, 113, '2013-05-20 08:13:32', '2013-05-20 12:13:32'),
(4, 'Colombie-Britannique - TPS-5%', 'CA', 'BC', '', '', 1, 113, 113, '2013-05-20 08:15:04', '2013-05-20 12:15:04'),
(5, 'Manitoba - TPS-5%', 'CA', 'MB', '', '', 1, 113, 113, '2013-05-20 08:16:50', '2013-05-20 12:16:50'),
(6, 'Nouveau-Brunswick - TVH-13%', 'CA', 'NB', '', '', 1, 113, 113, '2013-05-20 08:18:33', '2013-05-20 12:18:33'),
(7, 'Terre-Neuve et Labrador - TVH-13%', 'CA', 'NL', '', '', 1, 113, 113, '2013-05-20 08:19:41', '2013-05-20 12:19:41'),
(8, 'Territoires du Nord-Ouest - TPS-5%', 'CA', 'NT', '', '', 1, 113, 113, '2013-05-20 08:20:53', '2013-05-20 12:20:53'),
(9, 'Nouvelle-Écosse - TVH-15%', 'CA', 'NS', '', '', 1, 113, 113, '2013-05-20 09:38:15', '2013-05-20 13:38:15'),
(11, 'Île-du-Prince-Édouard - TVH-14%', 'CA', 'PE', '', '', 1, 113, 113, '2013-05-20 09:42:18', '2013-05-20 13:42:18'),
(12, 'Saskatchewan - TVH-5%', 'CA', 'SK', '', '', 1, 113, 113, '2013-05-20 09:46:46', '2013-05-20 13:46:46'),
(13, 'Yukon - TPS-5%', 'CA', 'YT', '', '', 1, 113, 113, '2013-05-20 09:47:46', '2013-05-20 13:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `tax_rule_exception`
--

CREATE TABLE IF NOT EXISTS `tax_rule_exception` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tax_rule` int(10) unsigned NOT NULL,
  `id_customer_type` int(10) unsigned NOT NULL,
  `id_tax_group` int(10) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tax_rule` (`id_tax_rule`),
  KEY `id_customer_type` (`id_customer_type`),
  KEY `id_tax_group` (`id_tax_group`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tax_rule_exception_rate`
--

CREATE TABLE IF NOT EXISTS `tax_rule_exception_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tax_rule_exception` int(10) unsigned NOT NULL,
  `id_tax_rule_rate` int(10) unsigned NOT NULL,
  `rate` decimal(6,5) unsigned NOT NULL,
  `id_tax_rule` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_tax_rule_exception` (`id_tax_rule_exception`),
  KEY `id_tax_rule_rate` (`id_tax_rule_rate`),
  KEY `id_tax_rule` (`id_tax_rule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tax_rule_rate`
--

CREATE TABLE IF NOT EXISTS `tax_rule_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tax_rule` int(10) unsigned NOT NULL,
  `id_tax` int(10) unsigned NOT NULL,
  `rate` decimal(6,5) unsigned NOT NULL,
  `stacked` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tax_rule` (`id_tax_rule`),
  KEY `id_tax` (`id_tax`),
  KEY `stacked` (`stacked`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

--
-- Dumping data for table `tax_rule_rate`
--

INSERT INTO `tax_rule_rate` (`id`, `id_tax_rule`, `id_tax`, `rate`, `stacked`, `sort_order`, `id_user_created`, `id_user_modified`, `date_created`, `date_modified`) VALUES
(1, 1, 1, '0.05000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 1, 2, '0.09975', 0, 2, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 3, 3, '0.13000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 5, 1, '0.05000', 0, 2, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 6, 3, '0.13000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 7, 3, '0.13000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 4, 1, '0.05000', 0, 2, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 9, 3, '0.15000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 2, 1, '0.05000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 11, 3, '0.14000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 12, 3, '0.05000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 13, 1, '0.05000', 0, 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 8, 1, '0.05000', 0, 2, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_bundled_product_category`
--

CREATE TABLE IF NOT EXISTS `tpl_product_bundled_product_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_bundled_product_group`
--

CREATE TABLE IF NOT EXISTS `tpl_product_bundled_product_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tpl_product_bundled_product_category` int(10) unsigned NOT NULL,
  `input_type` tinyint(1) unsigned NOT NULL,
  `required` tinyint(1) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tpl_product_bundled_product_category` (`id_tpl_product_bundled_product_category`),
  KEY `input_type` (`input_type`),
  KEY `required` (`required`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_bundled_product_group_description`
--

CREATE TABLE IF NOT EXISTS `tpl_product_bundled_product_group_description` (
  `id_tpl_product_bundled_product_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_tpl_product_bundled_product_group` (`id_tpl_product_bundled_product_group`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_variant_category`
--

CREATE TABLE IF NOT EXISTS `tpl_product_variant_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_variant_group`
--

CREATE TABLE IF NOT EXISTS `tpl_product_variant_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tpl_product_variant_category` int(10) unsigned NOT NULL,
  `input_type` tinyint(1) unsigned NOT NULL COMMENT '0 = dropdown, 1 = radio, 2 = swatch',
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tpl_product_variant_category` (`id_tpl_product_variant_category`),
  KEY `input_type` (`input_type`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_variant_group_description`
--

CREATE TABLE IF NOT EXISTS `tpl_product_variant_group_description` (
  `id_tpl_product_variant_group` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_variant_group` (`id_tpl_product_variant_group`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_variant_group_option`
--

CREATE TABLE IF NOT EXISTS `tpl_product_variant_group_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tpl_product_variant_group` int(10) unsigned NOT NULL,
  `swatch_type` tinyint(1) unsigned NOT NULL COMMENT '0 = one color, 1 = two colors, 2 = three colors, 3 = file',
  `color` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color2` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `color3` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tpl_product_variant_group` (`id_tpl_product_variant_group`),
  KEY `sort_order` (`sort_order`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_product_variant_group_option_description`
--

CREATE TABLE IF NOT EXISTS `tpl_product_variant_group_option_description` (
  `id_tpl_product_variant_group_option` int(10) unsigned NOT NULL,
  `language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `id_product_variant_group_option` (`id_tpl_product_variant_group_option`,`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_tag`
--

CREATE TABLE IF NOT EXISTS `tpl_tag` (
  `id_tpl_tag_group` int(10) unsigned NOT NULL,
  `id_tag` int(10) unsigned NOT NULL,
  `sort_order` tinyint(1) unsigned NOT NULL,
  UNIQUE KEY `id_tpl_tag_group` (`id_tpl_tag_group`,`id_tag`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpl_tag_group`
--

CREATE TABLE IF NOT EXISTS `tpl_tag_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission` smallint(1) unsigned NOT NULL COMMENT '999 = Super User',
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `phone_home` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `phone_cell` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `default_language_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `setting` text COLLATE utf8_unicode_ci NOT NULL,
  `id_user_created` int(10) unsigned NOT NULL,
  `id_user_modified` int(10) unsigned NOT NULL,
  `lastlogin` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` tinyint(1) unsigned NOT NULL COMMENT 'If deleted and in relation with id_user_created or id_user_modified in any table.',
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`,`password`),
  KEY `username` (`username`),
  KEY `password_reset_key` (`password_reset_key`),
  KEY `active` (`active`),
  KEY `default_language_code` (`default_language_code`),
  KEY `gender` (`gender`),
  KEY `permission` (`permission`),
  KEY `id_user_created` (`id_user_created`),
  KEY `id_user_modified` (`id_user_modified`),
  KEY `auth_key` (`auth_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=116 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `permission`, `firstname`, `lastname`, `address`, `city`, `state`, `zip`, `country`, `phone_home`, `phone_cell`, `gender`, `email`, `username`, `password`, `password_reset_key`, `default_language_code`, `setting`, `id_user_created`, `id_user_modified`, `lastlogin`, `active`, `date_created`, `date_modified`, `deleted`, `auth_key`) VALUES
(99, 999, 'Super', 'User', '', '', '', '', 'CA', '', '', 1, '', 'simplecommerce', '168cbb00aede3050ccbd704b50a31ac7', '', 'en', '', 18, 1, '2014-02-13 16:25:53', 1, '2012-02-09 12:36:56', '2012-10-22 21:00:46', 0, 'ffc79b8587ec902efe5e0b15d0b64d68'),
(113, 0, 'admin', 'admin', '', '', 'QC', '', 'CA', '', '', 1, 'admin@simplecommerce.com', 'admin', '67f43efc5701784db1504e4993d7e393', '', 'fr', '', 0, 99, '2014-04-02 07:10:18', 1, '0000-00-00 00:00:00', '2012-11-05 15:11:33', 0, 'abccfda18622d2b6ecc07181a5b557bf'),
(115, 0, 'Sophie', 'Carpentier', '', '', 'QC', '', 'CA', '', '', 0, 'sophie.hd@hotmail.ca', '1234', '30ed55b89373c25492993b3d004440ff', '', 'fr', '', 113, 113, '2014-03-19 10:50:26', 1, '2014-03-19 10:48:30', '2014-03-19 14:48:30', 0, 'd8b63996e944b497a9e74c50bf95350c');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
