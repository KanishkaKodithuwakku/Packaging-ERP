# 🔐 Role-Based Access Control (RBAC) User Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Understanding Roles and Permissions](#understanding-roles-and-permissions)
3. [Accessing RBAC Management](#accessing-rbac-management)
4. [User Management](#user-management)
5. [Role Management](#role-management)
6. [Permission Management](#permission-management)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The Role-Based Access Control (RBAC) system allows administrators to manage user access to different parts of the packaging ERP system. Users are assigned roles, and roles are assigned permissions, creating a flexible and secure access control system.

### Key Concepts:
- **Users**: Individual people who access the system
- **Roles**: Collections of permissions (e.g., Sales, Purchase, Production)
- **Permissions**: Specific actions users can perform (e.g., "create quotations", "view inventory")

---

## 🏷️ Understanding Roles and Permissions

### Default Roles in the System:

| Role | Description | Typical Users |
|------|-------------|---------------|
| **Admin** | Full system access | System administrators, IT managers |
| **Sales** | Sales and quotation management | Sales representatives, sales managers |
| **Purchase** | Purchase order management | Purchase officers, procurement managers |
| **Production** | Job orders and material requests | Production managers, floor supervisors |
| **Store** | GRN and inventory management | Store keepers, warehouse managers |
| **Account** | Delivery notes and accounting | Accountants, finance staff |

### Permission Categories:

#### 📋 Quotation Permissions
- `view quotations` - View quotation list and details
- `create quotations` - Create new quotations
- `edit quotations` - Modify existing quotations
- `delete quotations` - Remove quotations
- `send quotations` - Send quotations to customers
- `accept quotations` - Accept customer quotations
- `reject quotations` - Reject quotations

#### 📦 Order Management Permissions
- `view customer orders` - View customer order list and details
- `create customer orders` - Create new customer orders
- `edit customer orders` - Modify existing customer orders
- `delete customer orders` - Remove customer orders
- `view supplier orders` - View supplier order list and details
- `create supplier orders` - Create new supplier orders
- `edit supplier orders` - Modify existing supplier orders
- `delete supplier orders` - Remove supplier orders

#### 📥 GRN Permissions
- `view grns` - View GRN list and details
- `create grns` - Create new GRNs
- `edit grns` - Modify existing GRNs
- `delete grns` - Remove GRNs

#### 🏭 Production Permissions
- `view job orders` - View job order list and details
- `create job orders` - Create new job orders
- `edit job orders` - Modify existing job orders
- `delete job orders` - Remove job orders

#### 📦 Inventory Permissions
- `view inventory` - View inventory levels and details
- `manage inventory` - Create, edit, and delete inventory items
- `view inventory transactions` - View inventory movement history

#### 🚚 Delivery Permissions
- `view delivery notes` - View delivery note list and details
- `create delivery notes` - Create new delivery notes
- `edit delivery notes` - Modify existing delivery notes
- `delete delivery notes` - Remove delivery notes
- `print delivery notes` - Generate delivery note prints

#### 📋 Material Request Permissions
- `view material requests` - View material request list and details
- `create material requests` - Create new material requests
- `edit material requests` - Modify existing material requests
- `delete material requests` - Remove material requests

---

## 🚪 Accessing RBAC Management

### Prerequisites:
- You must have **Admin** role to access RBAC management
- Navigate to the **Administration** section in the sidebar

### Navigation Steps:
1. **Login** to the system with admin credentials
2. **Locate** the "Administration" section in the left sidebar
3. **Click** on any of the following:
   - **Role Management** - Manage roles and their permissions
   - **User Management** - Manage users and assign roles
   - **Permission Management** - Manage individual permissions

---

## 👥 User Management

### Creating a New User

#### Step 1: Access User Management
1. Navigate to **Administration** → **User Management**
2. Click the **"Add User"** button (usually blue button in top-right)

#### Step 2: Fill User Details
```
Required Fields:
├── Full Name: "John Smith"
├── Email Address: "john.smith@company.com"
├── Password: "SecurePassword123!"
└── Confirm Password: "SecurePassword123!"
```

#### Step 3: Assign Roles
- **Select Role(s)** from the available roles
- Users can have **multiple roles**
- Common combinations:
  - Sales + Account (for sales managers)
  - Store + Production (for warehouse supervisors)

#### Step 4: Save User
- Click **"Create User"** button
- User will receive confirmation message
- New user appears in the user list

### Editing an Existing User

#### Step 1: Find the User
1. Use the **search bar** to find the user by name or email
2. Click the **"Edit"** button (pencil icon) next to the user

#### Step 2: Modify Details
- Update **name**, **email**, or **password** as needed
- **Add or remove roles** using checkboxes
- **Deactivate user** by unchecking "Active" status

#### Step 3: Save Changes
- Click **"Update User"** button
- Changes are applied immediately

### User Management Best Practices

#### ✅ Do's:
- **Use descriptive names** (e.g., "John Smith - Sales Manager")
- **Assign minimum required permissions** (principle of least privilege)
- **Use company email addresses**
- **Set strong passwords** (minimum 8 characters, mixed case, numbers)
- **Review user access regularly**

#### ❌ Don'ts:
- **Don't share user accounts**
- **Don't assign admin role unnecessarily**
- **Don't use weak passwords**
- **Don't forget to deactivate former employees**

---

## 🏷️ Role Management

### Creating a New Role

#### Step 1: Access Role Management
1. Navigate to **Administration** → **Role Management**
2. Click the **"Add Role"** button

#### Step 2: Define Role
```
Role Details:
├── Role Name: "Quality Control"
├── Description: "Manages quality inspection and approval processes"
└── Permissions: [Select relevant permissions]
```

#### Step 3: Assign Permissions
- **Browse permission categories**:
  - Quotation permissions
  - Order management permissions
  - GRN permissions
  - Production permissions
  - Inventory permissions
  - Delivery permissions
  - Material request permissions
- **Check permissions** that this role should have
- **Uncheck permissions** that should be restricted

#### Step 4: Save Role
- Click **"Create Role"** button
- Role appears in the roles list

### Editing Existing Roles

#### Step 1: Select Role to Edit
1. Find the role in the **Role Management** list
2. Click the **"Edit"** button (pencil icon)

#### Step 2: Modify Permissions
- **Add permissions**: Check additional permission boxes
- **Remove permissions**: Uncheck permission boxes
- **Update description**: Modify role description if needed

#### Step 3: Save Changes
- Click **"Update Role"** button
- All users with this role will immediately get updated permissions

### Role Management Best Practices

#### ✅ Do's:
- **Use clear, descriptive role names**
- **Group related permissions** logically
- **Document role purposes** in descriptions
- **Review role permissions regularly**
- **Test role assignments** with non-admin users

#### ❌ Don'ts:
- **Don't create too many similar roles**
- **Don't assign permissions without understanding their impact**
- **Don't delete roles** that are actively used
- **Don't modify admin role** permissions

---

## 🔐 Permission Management

### Viewing All Permissions

#### Step 1: Access Permission Management
1. Navigate to **Administration** → **Permission Management**
2. View the complete list of system permissions

#### Step 2: Understand Permission Structure
- **Permission Name**: The exact permission identifier
- **Description**: What the permission allows
- **Used By Roles**: Which roles currently have this permission
- **Actions**: Edit or delete options

### Creating Custom Permissions

#### Step 1: Add New Permission
1. Click **"Add Permission"** button
2. Fill in permission details:
   ```
   Permission Name: "approve special orders"
   Description: "Allow approval of orders exceeding normal limits"
   ```

#### Step 2: Assign to Roles
- **Select roles** that should have this permission
- **Save permission**
- Permission becomes available for role assignment

### Permission Management Best Practices

#### ✅ Do's:
- **Use consistent naming** (e.g., "action object" format)
- **Provide clear descriptions**
- **Group related permissions** logically
- **Document custom permissions**

#### ❌ Don'ts:
- **Don't create duplicate permissions**
- **Don't use vague permission names**
- **Don't delete system permissions**
- **Don't assign permissions without testing**

---

## 🎯 Best Practices

### Security Best Practices

#### 1. Principle of Least Privilege
- **Grant minimum required permissions** for each user
- **Review and remove unnecessary permissions** regularly
- **Use role-based access** instead of individual permissions when possible

#### 2. Regular Access Reviews
- **Monthly review** of user access
- **Quarterly review** of role permissions
- **Annual audit** of entire RBAC system

#### 3. Strong Authentication
- **Enforce strong passwords** (minimum 8 characters, mixed case, numbers, symbols)
- **Consider two-factor authentication** for admin accounts
- **Regular password changes** for sensitive accounts

### Operational Best Practices

#### 1. User Onboarding
```
New Employee Process:
1. Create user account with temporary password
2. Assign appropriate role(s) based on job function
3. Provide login credentials securely
4. Require password change on first login
5. Provide system training
```

#### 2. Role Design
- **Create roles based on job functions**, not individual users
- **Use descriptive role names** that reflect responsibilities
- **Group related permissions** together
- **Avoid role proliferation** (too many similar roles)

#### 3. Documentation
- **Maintain role documentation** with clear descriptions
- **Document permission purposes** and impacts
- **Keep user assignment records** for auditing
- **Update documentation** when changes are made

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue: User Cannot Access Expected Features

**Symptoms:**
- User reports "Access Denied" messages
- Features appear but are not clickable
- User can see menu items but cannot perform actions

**Solutions:**
1. **Check user's roles**: Verify user has appropriate roles assigned
2. **Verify role permissions**: Ensure role has required permissions
3. **Clear browser cache**: Sometimes cached permissions cause issues
4. **Re-login**: Have user logout and login again

**Step-by-Step Fix:**
```
1. Go to User Management
2. Find the user having issues
3. Click "Edit" on the user
4. Verify roles are assigned correctly
5. Go to Role Management
6. Check that the user's roles have proper permissions
7. Save any changes
8. Ask user to logout and login again
```

#### Issue: Changes to Roles Not Taking Effect

**Symptoms:**
- Modified role permissions don't work immediately
- Users still have old permissions after role updates

**Solutions:**
1. **Clear application cache**: Run `php artisan cache:clear`
2. **Re-login users**: Have affected users logout and login
3. **Check for conflicting roles**: User might have multiple roles with different permissions

#### Issue: Cannot Delete Users or Roles

**Symptoms:**
- Delete buttons are missing or disabled
- Error messages when trying to delete

**Solutions:**
1. **Check dependencies**: Users/roles might be in use
2. **Remove assignments first**: Unassign users from roles before deleting
3. **Use deactivation**: Deactivate instead of deleting when possible

#### Issue: Admin User Locked Out

**Symptoms:**
- Cannot login with admin credentials
- All admin users have access issues

**Solutions:**
1. **Check database directly**: Verify admin user exists and is active
2. **Reset password via database**: Update password hash directly
3. **Create new admin user**: Add temporary admin user for access

**Emergency Admin Recovery:**
```sql
-- Reset admin password to 'admin123'
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@example.com';
```

### Getting Help

#### When to Contact System Administrator:
- **Permission system not working** after troubleshooting
- **Need to create new custom roles** or permissions
- **Security concerns** or suspected unauthorized access
- **System performance issues** related to user management

#### Information to Provide:
- **User account** experiencing issues
- **Specific error messages** received
- **Steps taken** before the problem occurred
- **Expected vs. actual behavior**

---

## 📞 Support Contacts

### System Administrator
- **Email**: admin@company.com
- **Phone**: +1-555-0123
- **Office Hours**: Monday-Friday, 9:00 AM - 5:00 PM

### IT Support
- **Email**: it-support@company.com
- **Phone**: +1-555-0124
- **Emergency**: +1-555-0125 (24/7)

---

## 📚 Additional Resources

### Training Materials
- **System Overview Video**: [Link to training video]
- **User Manual**: [Link to comprehensive manual]
- **FAQ Document**: [Link to frequently asked questions]

### System Information
- **Version**: 1.0.0
- **Last Updated**: [Current Date]
- **Next Review**: [Date + 3 months]

---

*This guide covers the basic RBAC functionality. For advanced features or custom implementations, please contact your system administrator.*
