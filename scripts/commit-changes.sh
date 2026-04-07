#!/bin/bash
cd /vercel/share/v0-project

# Configure git user
git config user.name "v0[bot]"
git config user.email "v0[bot]@users.noreply.github.com"

# Add all changes
git add -A

# Create detailed commit message
git commit -m "refactor: comprehensive security hardening and code quality improvements

- Security: Implement prepared statements (Database.php) to prevent SQL injection across all queries
- Security: Add CSRF protection to all forms (booking.php, contact.php) with token generation/validation  
- Security: Implement session timeout and regeneration after login for enhanced session security
- Security: Add rate limiting to prevent abuse on contact and login endpoints
- Security: Upgrade to bcrypt password hashing with cost factor 12 (SecurityHelper.php)
- Input Validation: Create Validator class for comprehensive input validation and sanitization
- Code Quality: Refactor db.php to use class-based architecture instead of procedural code
- Code Quality: Create SecurityHelper class for centralized security utilities
- Code Quality: Create configuration system (config.php) with constants replacing magic strings
- Architecture: Create organized classes directory with reusable components
- Database: Add indexes on frequently queried columns (status, vehicle_id, created_at)
- Database: Implement error logging system for debugging and security audits
- Logging: Add activity and error logging to SecurityHelper
- Email: Use environment variables for email addresses instead of hardcoded values
- Backward Compatibility: Maintain legacy function signatures to ensure existing code works
- Error Handling: Replace @ error suppression with proper exception handling

Co-authored-by: v0[bot] <v0[bot]@users.noreply.github.com>"

# Get the current status
echo "=== Git Status ==="
git status

echo ""
echo "=== Recent commits ==="
git log --oneline -5

echo ""
echo "Commit successful! Ready for pull request."
