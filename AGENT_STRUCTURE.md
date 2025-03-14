# Agent Structure Documentation

## Overview
The system has a two-level hierarchy of agents:
1. Top Level Agents
2. Sub-agents (directly under top level agents)

There are no nested levels of sub-agents - sub-agents cannot create their own sub-agents.

## Agent Types

### Top Level Agents
- Role: 'agent'
- Parent ID: NULL
- Can be created in two ways:
  1. Through website registration (system-created)
  2. Through admin panel (admin-created)
- Can create and manage sub-agents
- Can manage their own whitelist

### Sub-agents
- Role: 'sub_agent'
- Parent ID: Set to their creating agent's ID
- Created only by top level agents
- Cannot create their own sub-agents
- Limited to functionality provided by their parent agent

## Database Structure
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'agent', 'sub_agent') NOT NULL,
    balance BIGINT DEFAULT 0,
    parent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## Key Points
1. No nested levels - sub-agents cannot have their own sub-agents
2. Parent-child relationship is enforced through the parent_id field
3. Top level agents can be identified by:
   - role = 'agent'
   - parent_id = NULL
4. Sub-agents can be identified by:
   - role = 'sub_agent'
   - parent_id = [creating agent's ID]
5. When a top level agent is deleted:
   - Their sub-agents' parent_id is set to NULL (ON DELETE SET NULL)
   - This prevents orphaned references