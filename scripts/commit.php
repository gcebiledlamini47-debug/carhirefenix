<?php
/**
 * Commit the changes to git
 */

$cwd = '/vercel/share/v0-project';
chdir($cwd);

// Configure git
shell_exec('git config user.name "v0[bot]"');
shell_exec('git config user.email "v0[bot]@users.noreply.github.com"');

// Add all changes
echo "[v0] Adding all changes...\n";
shell_exec('git add -A');

// Create detailed commit message
$commitMsg = <<<'EOT'
refactor: comprehensive security hardening and code quality improvements

SECURITY IMPROVEMENTS:
- Implement prepared statements (Database.php) to prevent SQL injection
- Add CSRF protection to all forms (booking.php, contact.php)
- Add session timeout and regeneration after login
- Add rate limiting to contact and login endpoints
- Upgrade to bcrypt password hashing (cost 12)
- Add activity and error logging system

CODE QUALITY:
- Create Validator class for input validation and sanitization
- Refactor db.php to use class-based architecture
- Create SecurityHelper class for centralized security utilities
- Create configuration system with constants
- Implement proper exception handling

DATABASE:
- Add indexes on frequently queried columns
- Update schema with security enhancements
- Add created_at timestamp tracking

ARCHITECTURE:
- Create organized classes directory (/classes)
- Create config directory (/config)
- Create logs directory for audit trail
- Maintain backward compatibility

Co-authored-by: v0[bot] <v0[bot]@users.noreply.github.com>
EOT;

echo "[v0] Committing changes...\n";
$output = shell_exec('git commit -m ' . escapeshellarg($commitMsg) . ' 2>&1');
echo $output;

echo "\n[v0] Git Status:\n";
echo shell_exec('git status');

echo "\n[v0] Recent commits:\n";
echo shell_exec('git log --oneline -3');

echo "\n[v0] Commit completed successfully!\n";
?>
