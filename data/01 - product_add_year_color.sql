ALTER TABLE `product`
ADD `year` INT(11) NULL AFTER `model`,
ADD `mileage` FLOAT NULL AFTER `year`,
ADD `color` varchar(32) NULL AFTER `mileage`; 