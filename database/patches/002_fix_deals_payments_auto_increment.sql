-- Safe patch to ensure deals_payments and related deal tables have auto_increment restored
ALTER TABLE `deals_payments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
