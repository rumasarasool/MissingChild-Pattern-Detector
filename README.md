# Missing Children Pattern Detector System

A comprehensive web-based database system for tracking missing children, detecting patterns, and managing investigations.

## Features

### Core Database Functionalities
- ✅ Add New Missing Child Record
- ✅ Update Existing Child Information
- ✅ Delete Wrong/Resolved Records
- ✅ Insert Found Child Report
- ✅ Store Witness Reports
- ✅ Store Suspect Information
- ✅ Record Location History of Sightings
- ✅ Store Admin/Investigator Login Accounts

### Searching & Retrieval Functionalities
- ✅ Search Missing Child by Name
- ✅ Search Missing Child by ID
- ✅ Search by Age, Gender, City, Date
- ✅ Search Found Children for Possible Matches
- ✅ Search by Location (city, area, landmark)
- ✅ Display All Missing Children
- ✅ Display All Found Children
- ✅ Retrieve Case History of a Child
- ✅ Retrieve Witness Reports for a Specific Case
- ✅ Retrieve Suspects Linked to Multiple Cases

### Real-Time Pattern Detection Functionalities
- ✅ Detect High-Risk Missing Locations (hotspots)
- ✅ Detect Time-Based Patterns (peak hours/dates)
- ✅ Detect Repeat Suspects Across Reports
- ✅ Detect Children Missing from Same Area or School
- ✅ Identify Suspicious Activity Zones (multiple sightings)
- ✅ Match Found Children with Missing Records (age, gender, location, time)

### Statistical / Analytical Functionalities
- ✅ Total Missing Children Count
- ✅ Total Found Children Count
- ✅ Monthly/Yearly Missing Trends
- ✅ Location-wise Case Frequency
- ✅ Gender-wise Missing Trend
- ✅ Age Group Pattern Analysis
- ✅ Pending vs Resolved Case Statistics

### Admin + System Functionalities
- ✅ Admin Login Authentication
- ✅ Add/Remove Admin Users
- ✅ Generate Reports (via export functionality)
- ✅ Update Case Status (Open, Matched, Resolved)
- ✅ Maintain Data Integrity & Security
- ✅ Backup & Recovery Plan (SQL schema provided)

### Real-Time Alert Functionalities
- ✅ Alert When Multiple Children Go Missing From Same Location
- ✅ Alert When a New Found Report Matches Existing Missing Case
- ✅ Alert When Suspect Appears in Multiple Reports
- ✅ Alert for Suspicious Activity Zones

## Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Charts**: Chart.js

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server (or use XAMPP/WAMP)

### Setup Steps

1. **Clone or extract the project** to your web server directory
   ```
   /path/to/your/webserver/missing_child/
   ```

2. **Create the database**
   - Open phpMyAdmin or MySQL command line
   - Import the SQL schema:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
   Or use phpMyAdmin to import `sql/schema.sql`

3. **Configure database connection**
   - Edit `config/database.php`
   - Update database credentials if needed:
   ```php
   private $host = 'localhost';
   private $db_name = 'missing_children_db';
   private $username = 'root';
   private $password = '';
   ```

4. **Set up web server**
   - For XAMPP: Place project in `htdocs/missing_child/`
   - For WAMP: Place project in `www/missing_child/`
   - For Apache: Configure virtual host or place in document root

5. **Set proper permissions**
   ```bash
   chmod 755 -R /path/to/missing_child/
   ```

6. **Access the application**
   - Open browser: `http://localhost/missing_child/`
   - Default login credentials:
     - Username: `admin`
     - Password: `admin123`

## Project Structure

```
missing_child/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   └── auth.php              # Authentication functions
├── models/
│   ├── Child.php             # Missing/Found child operations
│   ├── Witness.php           # Witness report operations
│   ├── Suspect.php           # Suspect operations
│   ├── Location.php          # Location/sighting operations
│   ├── Admin.php             # Admin operations
│   └── PatternDetector.php   # Pattern detection logic
├── views/
│   ├── dashboard.php         # Main dashboard (index.php)
│   ├── add_child.php         # Add missing child form
│   ├── edit_child.php        # Edit child form
│   ├── view_child.php        # View child details
│   ├── search.php            # Search interface
│   ├── statistics.php        # Statistics & analytics
│   ├── patterns.php          # Pattern detection results
│   ├── add_witness.php       # Add witness report
│   ├── add_sighting.php      # Add sighting
│   ├── add_suspect.php       # Add/link suspect
│   ├── add_found.php         # Add found child
│   ├── update_status.php    # Update case status
│   └── admin/
│       └── users.php         # Admin user management
├── api/
│   ├── match_found.php       # Match found child endpoint
│   └── generate_alert.php   # Alert generation system
├── sql/
│   └── schema.sql            # Complete database schema
├── assets/
│   ├── css/
│   │   └── style.css         # Custom styles
│   └── js/
│       └── main.js           # Frontend JavaScript
├── index.php                 # Entry point (dashboard)
├── login.php                 # Admin login
└── logout.php                # Logout handler
```

## Usage

### Adding a Missing Child
1. Login to the system
2. Navigate to "Add Missing Child"
3. Fill in required information
4. Save the record

### Searching
1. Go to "Search" page
2. Select search type (Missing, Found, or Location)
3. Apply filters as needed
4. View results

### Pattern Detection
1. Navigate to "Patterns" page
2. View detected patterns:
   - High-risk locations
   - Repeat suspects
   - Area clustering
   - Time patterns
   - Suspicious zones

### Statistics
1. Go to "Statistics" page
2. View comprehensive analytics:
   - Case status distribution
   - Gender/age analysis
   - Monthly trends
   - Location frequency

### Admin Functions
1. Login as admin
2. Access "Admin" menu
3. Manage users, view reports

## Security Features

- Password hashing using PHP `password_hash()`
- Prepared statements (PDO) to prevent SQL injection
- Input sanitization
- Session-based authentication
- CSRF protection ready
- Role-based access control

## Database Schema

The database includes the following tables:
- `admins` - Admin/investigator accounts
- `missing_children` - Main missing child records
- `found_children` - Found child reports
- `witness_reports` - Witness information
- `suspects` - Suspect information
- `suspect_cases` - Suspect-case associations
- `sightings` - Location history
- `case_history` - Case status tracking
- `alerts` - System alerts

## Sample Data

The schema includes sample data for demonstration:
- 2 admin accounts
- 5 missing children cases
- 2 found children reports
- 3 suspects
- Multiple witness reports and sightings

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Login Issues
- Default credentials: admin/admin123
- Check if admin account exists in database
- Verify password hash in database

### Permission Errors
- Check file permissions
- Ensure web server has read access
- Check PHP error logs

## Future Enhancements

- Export to PDF functionality
- Email notifications
- Map integration (Google Maps)
- Photo upload functionality
- Advanced reporting
- Mobile app integration

## License

This project is for educational/academic purposes.

## Support

For issues or questions, please refer to the project documentation or contact the development team.

---

**Note**: This system is designed for presentation and demonstration purposes. For production use, additional security measures and testing are recommended.

