<?php

/**
 * PHPMaker 5 configuration file
 */

// Database connection
define("EW_CONN_HOST", "localhost");
define("EW_CONN_PORT", 3306);
define("EW_CONN_USER", "root");
define("EW_CONN_PASS", "");
define("EW_CONN_DB", "webspider_db");

// Show SQL for debug
//define("EW_DEBUG_ENABLED"); // Uncomment to debug

define("EW_IS_WINDOWS", (strtolower(substr(PHP_OS, 0, 3)) === 'win')); // Is Windows OS
define("EW_IS_PHP5", (phpversion() >= "5.0.0")); // Is PHP5
define("EW_PATH_DELIMITER", ((EW_IS_WINDOWS) ? "\\" : "/")); // Physical path delimiter
define("EW_ROOT_RELATIVE_PATH", "."); // Relative path of app root
define("EW_DEFAULT_DATE_FORMAT", "mm/dd/yyyy"); // Default date format
define("EW_DEFAULT_DATE_FORMAT_ID", "6"); // Default date format
define("EW_DATE_SEPARATOR", "/"); // Date separator
define("EW_PROJECT_NAME", "FYP_byMaker"); // Project Name
define("EW_RANDOM_KEY", 'j%Dti5W2$2k00DjI'); // Random key for encryption

/**
 * Encoding for Ajax
 * Note: If you use non English languages, you may need to set the encoding for
 * Ajax features. Make sure your encoding is supported by your PHP and either
 * iconv functions or multibyte string functions are enabled. See PHP manual
 * for details
 * e.g. define("EW_ENCODING", "ISO-8859-1");
 */
define("EW_ENCODING", "ISO-8859-1"); // Encoding for Ajax

/**
 * Password (MD5 and case-sensitivity)
 * Note: If you enable MD5 password, make sure that the passwords in your
 * user table are stored as MD5 hash (32-character hexadecimal number) of the
 * clear text password. If you also use case-insensitive password, convert the
 * clear text passwords to lower case first before calculating MD5 hash.
 * Otherwise, existing users will not be able to login. MD5 hash is
 * irreversible, password will be reset during password recovery.
 */
define("EW_MD5_PASSWORD", FALSE); // Use MD5 password
define("EW_CASE_SENSITIVE_PASSWORD", FALSE); // Case-sensitive password

// Session names
define("EW_SESSION_STATUS", EW_PROJECT_NAME . "_status"); // Login Status
define("EW_SESSION_USER_NAME", EW_SESSION_STATUS . "_UserName"); // User Name
define("EW_SESSION_USER_ID", EW_SESSION_STATUS . "_UserID"); // User ID
define("EW_SESSION_USER_LEVEL_ID", EW_SESSION_STATUS . "_UserLevel"); // User Level ID
define("EW_SESSION_USER_LEVEL", EW_SESSION_STATUS . "_UserLevelValue"); // User Level
define("EW_SESSION_PARENT_USER_ID", EW_SESSION_STATUS . "_ParentUserID"); // Parent User ID
define("EW_SESSION_SYS_ADMIN", EW_PROJECT_NAME . "_SysAdmin"); // System Admin
define("EW_SESSION_AR_USER_LEVEL", EW_PROJECT_NAME . "_arUserLevel"); // User Level Array
define("EW_SESSION_AR_USER_LEVEL_PRIV", EW_PROJECT_NAME . "_arUserLevelPriv"); // User Level Privilege Array
define("EW_SESSION_SECURITY", EW_PROJECT_NAME . "_Security"); // Security Array
define("EW_SESSION_MESSAGE", EW_PROJECT_NAME . "_Message"); // System Message
define("EW_SESSION_INLINE_MODE", EW_PROJECT_NAME . "_InlineMode"); // Inline Mode
define("EW_DATATYPE_NUMBER", 1);
define("EW_DATATYPE_DATE", 2);
define("EW_DATATYPE_STRING", 3);
define("EW_DATATYPE_BOOLEAN", 4);
define("EW_DATATYPE_MEMO", 5);
define("EW_DATATYPE_BLOB", 6);
define("EW_DATATYPE_TIME", 7);
define("EW_DATATYPE_GUID", 8);
define("EW_DATATYPE_OTHER", 9);
define("EW_ROWTYPE_VIEW", 1); // Row type view
define("EW_ROWTYPE_ADD", 2); // Row type add
define("EW_ROWTYPE_EDIT", 3); // Row type edit
define("EW_ROWTYPE_SEARCH", 4); // Row type search
define("EW_COMPOSITE_KEY_SEPARATOR", ","); // Composite key separator
define("EW_HIGHLIGHT_COMPARE", 1); // Highlight compare mode

// Table parameters
define("EW_TABLE_REC_PER_PAGE", "RecPerPage"); // Records per page
define("EW_TABLE_START_REC", "start"); // Start record
define("EW_TABLE_PAGE_NO", "pageno"); // Page number
define("EW_TABLE_BASIC_SEARCH", "psearch"); // Basic search keyword
define("EW_TABLE_BASIC_SEARCH_TYPE","psearchtype"); // Basic search type
define("EW_TABLE_ADVANCED_SEARCH", "advsrch"); // Advanced search
define("EW_TABLE_SEARCH_WHERE", "searchwhere"); // Search where clause
define("EW_TABLE_WHERE", "where"); // Table where
define("EW_TABLE_ORDER_BY", "orderby"); // Table order by
define("EW_TABLE_SORT", "sort"); // Table sort
define("EW_TABLE_KEY", "key"); // Table key
define("EW_TABLE_SHOW_MASTER", "showmaster"); // Table show master
define("EW_TABLE_MASTER_TABLE", "MasterTable"); // Master table
define("EW_TABLE_MASTER_FILTER", "MasterFilter"); // Master filter
define("EW_TABLE_DETAIL_FILTER", "DetailFilter"); // Detail filter
define("EW_TABLE_RETURN_URL", "return"); // Return url

// Database
define("EW_IS_MSACCESS", False); // Access (Reserved, NOT USED)
define("EW_IS_MYSQL",""); // MySQL
define("EW_DB_QUOTE_START", "`");
define("EW_DB_QUOTE_END", "`");

/**
 * MySQL charset (for SET NAMES statement, not used by default)
 * Note: Read http://dev.mysql.com/doc/refman/5.0/en/charset-connection.html
 * before using this setting.
 */
define("EW_MYSQL_CHARSET", "");

// Security
define("EW_ADMIN_USER_NAME", ""); // Administrator user name
define("EW_ADMIN_PASSWORD", ""); // Administrator password

// User level constants
define("EW_USER_LEVEL_COMPAT",""); // Use old User Level values. Comment out to use new User Level values (separate values for View/Search)
define("EW_ALLOW_ADD", 1); // Add
define("EW_ALLOW_DELETE", 2); // Delete
define("EW_ALLOW_EDIT", 4); // Edit
define("EW_ALLOW_LIST", 8); // List
if (defined("EW_USER_LEVEL_COMPAT")) {
	define("EW_ALLOW_VIEW", 8); // View
	define("EW_ALLOW_SEARCH", 8); // Search
} else {
	define("EW_ALLOW_VIEW", 32); // View
	define("EW_ALLOW_SEARCH", 64); // Search
}
define("EW_ALLOW_REPORT", 8); // Report
define("EW_ALLOW_ADMIN", 16); // Admin

// Email
define("EW_EMAIL_COMPONENT", strtoupper("PHP"));
define("EW_SMTP_SERVER", "localhost"); // Smtp server
define("EW_SMTP_SERVER_PORT", 25); // Smtp server port
define("EW_SMTP_SERVER_USERNAME", ""); // Smtp server user name
define("EW_SMTP_SERVER_PASSWORD", ""); // Smtp server password
define("EW_SENDER_EMAIL", "webmaster@{$_SERVER['SERVER_NAME']}"); // Sender email
define("EW_RECIPIENT_EMAIL", ""); // Receiver email

// File upload
define("EW_UPLOAD_DEST_PATH", ""); // Upload destination path (relative to app root)
define("EW_UPLOAD_ALLOWED_FILE_EXT", "gif,jpg,jpeg,bmp,png,doc,xls,pdf,zip"); // Allowed file extensions
define("EW_IMAGE_ALLOWED_FILE_EXT", "gif,jpg,png,bmp"); // Allowed file extensions for images
define("EW_MAX_FILE_SIZE", 2000000); // Max file size
define("EW_THUMBNAIL_FILE_PREFIX", "tn_"); // Thumbnail file prefix
define("EW_THUMBNAIL_FILE_SUFFIX", ""); // Thumbnail file suffix
define("EW_THUMBNAIL_DEFAULT_WIDTH", 0); // Thumbnail default width
define("EW_THUMBNAIL_DEFAULT_HEIGHT", 0); // Thumbnail default height
define("EW_THUMBNAIL_DEFAULT_QUALITY", 75); // Thumbnail default qualtity (JPEG)
define("EW_UPLOADED_FILE_MODE", 0666); // Uploaded file mode
define("EW_UPLOAD_TMP_PATH", ""); // User upload temp path (relative to app root) e.g. "tmp/"

// Audit Trail
define("EW_AUDIT_TRAIL_PATH", ""); // Audit trail path (relative to app root)

// Export records
define("EW_EXPORT_ALL",""); // Export all records // Comment this line out (not set to FALSE) to export one page only
define("EW_XML_ENCODING", ""); // Encoding for Export to XML

// Locale (if localeconv returns empty info)
define("DEFAULT_CURRENCY_SYMBOL", "$");
define("DEFAULT_MON_DECIMAL_POINT", ".");
define("DEFAULT_MON_THOUSANDS_SEP", ",");
define("DEFAULT_POSITIVE_SIGN", "");
define("DEFAULT_NEGATIVE_SIGN", "-");
define("DEFAULT_FRAC_DIGITS", 2);
define("DEFAULT_P_CS_PRECEDES","");
define("DEFAULT_P_SEP_BY_SPACE", FALSE);
define("DEFAULT_N_CS_PRECEDES","");
define("DEFAULT_N_SEP_BY_SPACE", FALSE);
define("DEFAULT_P_SIGN_POSN", 3);
define("DEFAULT_N_SIGN_POSN", 3);

// Remove XSS
define("EW_REMOVE_XSS","");
?>
