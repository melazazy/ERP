# Navigation Permission System

## Overview
The navigation system has been updated to only display links that users can access based on their assigned role. This ensures that users only see navigation options they have permission to use.

## How It Works

### Permission Checking
The navigation component now includes a `canAccess()` method that checks user permissions based on their role. This method is used throughout the navigation to conditionally display menu items.

### Role-Based Permissions

#### System Administrator
- **Full Access**: Can see and access all navigation items
- **Permissions**: All permissions set to `true`

#### Warehouse Manager
- **Inventory Operations**: Full access to all inventory functions
- **Items Management**: Full access to item-related functions
- **Search Operations**: Full access to search functions
- **Reports Access**: Full access to all reports
- **User Management**: No access to user management functions
- **Role Management**: No access to role management
- **Backup Management**: No access to backup functions

#### Receiving Clerk
- **Inventory Operations**: Limited to receiving and receiving search only
- **Items Management**: No access
- **Search Operations**: Limited to receiving search only
- **Reports Access**: No access
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Requisition Clerk
- **Inventory Operations**: Limited to requisition and transfer only
- **Items Management**: No access
- **Search Operations**: Limited to requisition search only
- **Reports Access**: No access
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Trust Clerk
- **Inventory Operations**: Limited to trusts only
- **Items Management**: No access
- **Search Operations**: Limited to trust search only
- **Reports Access**: No access
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Inventory Controller
- **Inventory Operations**: No access
- **Items Management**: Limited to item monitor and item report
- **Search Operations**: No access
- **Reports Access**: Full access to all reports
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Store Keeper
- **Inventory Operations**: No access
- **Items Management**: Limited to item monitor and item report
- **Search Operations**: No access
- **Reports Access**: No access
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Accountant
- **Inventory Operations**: No access
- **Items Management**: No access
- **Search Operations**: No access
- **Reports Access**: Full access to all reports
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Auditor
- **Inventory Operations**: No access
- **Items Management**: No access
- **Search Operations**: No access
- **Reports Access**: Full access to all reports
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access

#### Department Manager
- **Inventory Operations**: No access
- **Items Management**: No access
- **Search Operations**: No access
- **Reports Access**: No access
- **User Management**: No access
- **Role Management**: No access
- **Backup Management**: No access
- **Department Management**: Full access to department management

## Implementation Details

### Permission Structure
Each role has a set of permissions that can be:
- `true`: Full access to all operations in that category
- `false`: No access to any operations in that category
- `array`: Limited access to specific operations within that category

### Navigation Sections

#### Desktop Navigation
1. **Inventory Operations**: Receiving, Requisition, Trusts, Transfer
2. **Items**: Item Card, Item Monitor, Item Report
3. **Search**: Receiving Search, Requisition Search, Trust Search, Document Search
4. **Reports**: Export Reports, Inventory Reports, Department Reports, Supplier Reports
5. **Management**: User Management, Role Management, Backup Management, Profile

#### Mobile Navigation
The same permission logic is applied to the responsive mobile navigation menu.

### Code Structure
The permission system is implemented directly in the navigation component using:
- `$this->canAccess('permission_name')` for boolean permissions
- `$this->canAccess('permission_name', 'specific_operation')` for array-based permissions

## Benefits
1. **Security**: Users can only see navigation options they have access to
2. **User Experience**: Cleaner interface with only relevant options
3. **Role Clarity**: Clear separation of responsibilities between different roles
4. **Maintainability**: Centralized permission logic in one component

## Adding New Roles
To add a new role, simply add it to the `getPermissionsByRole()` method in the navigation component with the appropriate permission settings.

## Adding New Permissions
To add new permissions, update the permission arrays in the `getPermissionsByRole()` method and add the corresponding permission checks in the navigation template.
