/url-shortener/
│
├── /assets/                  # Static files (CSS, JS, images)
│   ├── css/
│   │   └── styles.css        # Custom CSS for styling
│   └── js/
│       └── scripts.js        # Custom JavaScript for interactivity
│
├── /includes/                # PHP includes (database connection, functions, etc.)
│   ├── db.php                # Database connection
│   ├── auth.php              # Authentication functions
│   ├── functions.php         # Helper functions
│   └── admin_functions.php   # Admin-specific functions
│
├── /pages/                   # Individual pages
│   ├── index.php             # Homepage (login/signup form)
│   ├── dashboard.php         # User dashboard
│   ├── admin_dashboard.php   # Admin dashboard
│   ├── login.php             # Login page
│   ├── signup.php            # Signup page
│   └── logout.php            # Logout script
│
├── /process/                 # Form processing scripts
│   ├── login_process.php     # Handle login
│   ├── signup_process.php    # Handle signup
│   ├── shorten_process.php   # Handle URL shortening
│   └── edit_process.php      # Handle URL editing
│
├── .htaccess                 # URL rewriting rules
└── config.php                # Application configuration