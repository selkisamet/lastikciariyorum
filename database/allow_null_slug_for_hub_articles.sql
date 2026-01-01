-- Allow NULL slug for HUB articles
-- HUB articles don't need slugs because they're accessed via canonical URLs only
-- Example: /istanbul/ (city HUB) or /istanbul/sultanbeyli/ (district HUB)

ALTER TABLE articles MODIFY slug VARCHAR(255) NULL;

-- Update unique constraint to handle NULL slugs properly
-- Note: MySQL allows multiple NULL values in unique indexes
-- This is correct behavior for HUB articles (each location can have one HUB with slug=NULL)
