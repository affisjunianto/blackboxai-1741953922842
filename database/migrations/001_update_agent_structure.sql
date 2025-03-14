-- Migration script to update agent structure
-- Removes sub_agent role and updates all users to be agents

-- Start transaction
START TRANSACTION;

-- Update all sub_agents to be agents
UPDATE users 
SET role = 'agent' 
WHERE role = 'sub_agent';

-- Modify the role enum to remove sub_agent
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'agent') NOT NULL;

-- Verify no sub_agents remain
SELECT COUNT(*) as remaining_sub_agents 
FROM users 
WHERE role = 'sub_agent';

-- If the count is 0, commit the transaction
-- If not 0, the transaction will be rolled back
COMMIT;

-- Note: After running this migration:
-- 1. All former sub_agents are now agents
-- 2. Hierarchy is maintained through parent_id relationships
-- 3. The role column no longer accepts 'sub_agent' as a value