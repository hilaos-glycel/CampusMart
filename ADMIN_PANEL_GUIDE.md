# 🛡️ CampusMart Admin Panel - Complete Guide

## Overview
The CampusMart Admin Panel is a comprehensive administrative interface for managing the JH Cerilles State College Student Marketplace. It provides full control over users, listings, categories, services, and system settings.

## 🚀 Quick Start

### 1. Setup Admin System
```bash
# Run the admin setup script
http://localhost/CampusMart/setup_admin_system.php
```

### 2. Default Admin Credentials
- **Username:** admin
- **Password:** admin123
- **Email:** admin@campusmart.local
- **Role:** Super Admin

### 3. Access Admin Panel
```
http://localhost/CampusMart/admin/login.php
```

## 📋 Features

### 🔐 Authentication System
- **Secure Login:** Separate admin authentication system
- **Role-based Access:** Super Admin, Admin, Moderator roles
- **Session Management:** Automatic timeout and security
- **Password Hashing:** bcrypt encryption for passwords

### 👥 User Management (`admin/users.php`)
- **View All Users:** Paginated list with search and filters
- **User Actions:**
  - Activate/Deactivate users
  - Suspend users
  - Delete users (with confirmation)
- **User Information:**
  - Student ID, name, email, course, year level
  - Registration date and contact information
  - Account status tracking
- **Filters:** Status, course, search by name/email/username
- **Statistics:** Total, active, pending, suspended users

### 🛍️ Listing Management (`admin/listings.php`)
- **View All Listings:** Complete product listing overview
- **Listing Actions:**
  - Approve/Reject listings
  - Feature/Unfeature products
  - Edit listing details
  - Delete listings
- **Listing Information:**
  - Title, description, price, category
  - Seller information
  - Images and condition
  - Status and creation date
- **Filters:** Status, category, price range, date
- **Bulk Actions:** Multiple listing management

### 🏷️ Category Management (`admin/categories.php`)
- **Category CRUD:** Create, read, update, delete categories
- **Category Features:**
  - Name and slug management
  - Icon assignment
  - Active/inactive status
  - Description and metadata
- **Usage Statistics:** Listings per category
- **SEO-friendly:** URL slug generation

### 💼 Service Management (`admin/services.php`)
- **Service Oversight:** Tutoring and freelance services
- **Service Actions:**
  - Approve/reject services
  - Feature services
  - Manage service providers
- **Service Types:** Tutoring, freelance, consulting
- **Booking Management:** View and manage bookings

### 💬 Message Monitoring (`admin/messages.php`)
- **Message Overview:** System-wide messaging statistics
- **Content Moderation:** Review reported messages
- **User Communication:** Monitor user interactions
- **Spam Detection:** Identify and manage spam

### 📊 Dashboard (`admin/index.php`)
- **System Statistics:**
  - User metrics (total, active, new)
  - Listing metrics (active, sold, featured)
  - Message activity (daily, weekly)
  - Service statistics
- **Recent Activity:** Latest users and listings
- **Quick Actions:** Common administrative tasks
- **Real-time Updates:** Auto-refresh functionality

### ⚙️ System Settings (`admin/settings.php`)
- **General Settings:**
  - Site name and description
  - Contact information
  - Maintenance mode
- **User Settings:**
  - Registration requirements
  - Email verification
  - Account approval process
- **Listing Settings:**
  - Approval workflow
  - Featured listing limits
  - Price ranges
- **Security Settings:**
  - Session timeout
  - Login attempts
  - Password requirements

### 📈 Reports & Analytics (`admin/reports.php`)
- **User Analytics:**
  - Registration trends
  - Activity patterns
  - Geographic distribution
- **Listing Analytics:**
  - Popular categories
  - Price trends
  - Success rates
- **Financial Reports:**
  - Transaction volumes
  - Revenue tracking
  - Commission calculations
- **Export Options:** CSV, PDF reports

## 🔧 Technical Details

### File Structure
```
admin/
├── login.php          # Admin login page
├── logout.php         # Admin logout handler
├── index.php          # Main dashboard
├── users.php          # User management
├── listings.php       # Listing management
├── categories.php     # Category management
├── services.php       # Service management
├── settings.php       # System settings
└── reports.php        # Analytics and reports

includes/
└── admin_auth.php     # Admin authentication class

css/
└── admin.css          # Admin panel styles
```

### Database Tables
- **admin_users:** Admin account management
- **users:** Regular user accounts
- **listings:** Product listings
- **categories:** Product categories
- **services:** Service offerings
- **messages:** User communications
- **activity_logs:** System activity tracking
- **system_settings:** Configuration storage

### Security Features
- **SQL Injection Prevention:** PDO prepared statements
- **XSS Protection:** Input sanitization and output escaping
- **CSRF Protection:** Token validation
- **Session Security:** Secure session management
- **Role-based Access:** Permission checking
- **Activity Logging:** Comprehensive audit trail

## 👤 User Roles

### Super Admin
- **Full Access:** All administrative functions
- **User Management:** Create/delete admin accounts
- **System Settings:** Modify all configurations
- **Security:** Access to security logs and settings

### Admin
- **User Management:** Manage regular users
- **Content Management:** Listings, categories, services
- **Reports:** View analytics and reports
- **Limited Settings:** Basic configuration access

### Moderator
- **Content Review:** Approve/reject listings and services
- **User Support:** Handle user issues
- **Basic Reports:** View usage statistics
- **Limited Access:** No system configuration

## 🛠️ Maintenance

### Regular Tasks
1. **User Review:** Check pending registrations
2. **Content Moderation:** Review reported content
3. **System Monitoring:** Check error logs
4. **Database Cleanup:** Remove old activity logs
5. **Security Updates:** Monitor for vulnerabilities

### Backup Procedures
1. **Database Backup:** Regular MySQL dumps
2. **File Backup:** Upload directories and configuration
3. **Log Rotation:** Archive old log files
4. **Recovery Testing:** Verify backup integrity

### Performance Optimization
1. **Database Indexing:** Optimize query performance
2. **Image Optimization:** Compress uploaded images
3. **Cache Management:** Implement caching strategies
4. **Server Monitoring:** Track resource usage

## 🔍 Troubleshooting

### Common Issues

#### Login Problems
- **Forgot Password:** Use database to reset admin password
- **Session Issues:** Clear browser cache and cookies
- **Permission Denied:** Check admin role assignments

#### Database Errors
- **Connection Failed:** Verify database credentials
- **Table Missing:** Run database setup script
- **Query Errors:** Check error logs for details

#### File Upload Issues
- **Permission Denied:** Check directory permissions
- **File Too Large:** Adjust PHP upload limits
- **Invalid Format:** Verify allowed file types

### Error Logs
- **PHP Errors:** Check server error logs
- **Application Logs:** Review activity_logs table
- **Database Logs:** Monitor MySQL error log

## 📞 Support

### Getting Help
1. **Documentation:** Review this guide thoroughly
2. **Test Scripts:** Run diagnostic scripts
3. **Error Logs:** Check system logs for issues
4. **Database:** Verify data integrity

### Contact Information
- **System Administrator:** admin@campusmart.local
- **Technical Support:** Check error logs and documentation
- **Emergency:** Restore from backup if needed

## 🔄 Updates and Maintenance

### Version Control
- Keep track of admin panel changes
- Test updates in development environment
- Backup before applying updates

### Security Updates
- Regularly update PHP and MySQL
- Monitor for security vulnerabilities
- Apply security patches promptly

### Feature Requests
- Document new feature requirements
- Test thoroughly before deployment
- Update documentation accordingly

---

**Last Updated:** October 2024  
**Version:** 1.0  
**Compatibility:** PHP 7.4+, MySQL 8.0+
