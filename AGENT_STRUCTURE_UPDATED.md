# Updated Agent Structure Documentation

## Overview
All users in the system (except admins) are agents. The hierarchy is determined by the parent_id relationship, not by different roles.

## Agent Types

### Independent Agents (Top Level)
- Role: 'agent'
- Parent ID: NULL
- Created through:
  1. Website registration (system-created)
  2. Admin panel (admin-created)
- Can create and manage child agents
- Can manage their own whitelist

### Child Agents
- Role: 'agent' (same as parent)
- Parent ID: Set to their creating agent's ID
- Created by:
  1. Other agents (through manage_child_agents.php)
  2. Admin (on behalf of a parent agent)
- Managed by their parent agent
- Parent agent controls:
  - Balance
  - Active status
  - Other management functions

## Database Structure
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'agent') NOT NULL,
    balance BIGINT DEFAULT 0,
    parent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## Key Changes
1. Removed 'sub_agent' role - all users are 'agent'
2. Hierarchy determined by parent_id relationship
3. Child agents have same capabilities as parent agents
4. Parent agents have full control over their child agents

## Parent-Child Relationship
- Parent agents can:
  - Create child agents
  - Manage child agent balances
  - Control child agent status
  - View child agent transactions
- Child agents are full agents who:
  - Have a parent_id linking to their creating agent
  - Can be managed by their parent agent
  - Can create their own child agents

## File Structure Changes
1. Renamed management files:
   - agent/manage_subagents.php → agent/manage_child_agents.php
   - admin/manage_subagents.php → admin/manage_child_agents.php
2. Updated function names:
   - isSubAgent() → hasParentAgent()
   - getSubAgents() → getChildAgents()
   - Added getParentAgent() function

## Security
- Parent agents can only manage their direct child agents
- Child agents operate independently but under parent oversight
- Admin can manage all agents and relationships