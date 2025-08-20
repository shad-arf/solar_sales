-- Sample pricing data for testing
-- This assumes you have items in your items table already

-- First, let's add some sample pricing for existing items
-- Replace the item_id values with actual IDs from your items table

-- Sample pricing for item ID 1 (replace with actual item ID)
INSERT INTO `item_prices` (`item_id`, `name`, `price`, `unit`, `description`, `is_default`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Retail', 150.00, 'piece', 'Standard retail price', 1, 1, 1, NOW(), NOW()),
(1, 'Installer', 120.00, 'piece', 'Price for certified installers', 0, 1, 2, NOW(), NOW()),
(1, 'Reseller', 100.00, 'piece', 'Wholesale price for resellers', 0, 1, 3, NOW(), NOW());

-- Sample pricing for item ID 2 (replace with actual item ID)
INSERT INTO `item_prices` (`item_id`, `name`, `price`, `unit`, `description`, `is_default`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 'Retail', 250.00, 'piece', 'Standard retail price', 1, 1, 1, NOW(), NOW()),
(2, 'Installer', 200.00, 'piece', 'Price for certified installers', 0, 1, 2, NOW(), NOW()),
(2, 'Reseller', 175.00, 'piece', 'Wholesale price for resellers', 0, 1, 3, NOW(), NOW());

-- Sample pricing for item ID 3 (replace with actual item ID)
INSERT INTO `item_prices` (`item_id`, `name`, `price`, `unit`, `description`, `is_default`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 'Retail', 75.00, 'piece', 'Standard retail price', 1, 1, 1, NOW(), NOW()),
(3, 'Installer', 60.00, 'piece', 'Price for certified installers', 0, 1, 2, NOW(), NOW()),
(3, 'Reseller', 50.00, 'piece', 'Wholesale price for resellers', 0, 1, 3, NOW(), NOW());

-- To check your existing items and add pricing, you can run:
-- SELECT id, name FROM items WHERE deleted_at IS NULL;
-- Then replace the item_id values above with your actual item IDs