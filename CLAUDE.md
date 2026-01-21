# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AllowanceLab is a family allowance management system where parents act as "the bank" and children learn financial responsibility through a point-based accountability system. The application uses a family-centric architecture where multiple parents can manage the same children's accounts.

**Live URL:** https://allowancelab.com
**Owner:** joshua.sopko@gmail.com

## Key Architecture Principles

### Family-Based Multi-Tenancy
- Application is organized around **families**, not individual users
- Multiple parents can belong to the same family via `family_members` pivot table
- Kids belong directly to families via `family_id` foreign key
- All data access must be scoped through family membership
- Use `User::accessibleKids()` to get kids a parent can manage

### Dual Authentication Systems
- **Parent authentication:** Standard Laravel Breeze (`auth` middleware, User model)
- **Kid authentication:** Separate guard (`auth:kid` middleware, Kid model extends Authenticatable)
- Kids login at `/kid/login` with username/password, parents use email/password
- Never mix authentication contexts - controllers should be specific to one user type

### Transaction and Balance System
- All money movements are recorded in the `transactions` table
- Balance is stored on `Kid` model but must stay in sync with transactions
- Transaction types: `deposit` (money added), `withdrawal` (money spent)
- `initiated_by` field tracks whether transaction was by 'parent' or 'kid'
- Never modify balance without creating a corresponding transaction

### Points System
- Kids start each week with `max_points` (default: 10)
- Points can be adjusted by parents anytime via `point_adjustments` table
- Points reset on their `allowance_day` by scheduled command `allowance:process`
- Allowance only posts if kid has `points >= 1` on their allowance day
- After allowance evaluation, points are reset to `max_points` (regardless of whether allowance was awarded)
- Points system can be disabled per kid via `points_enabled` boolean

## Common Development Commands

### Laravel Development
```bash
composer install                    # Install PHP dependencies
php artisan migrate                 # Run database migrations
php artisan migrate:fresh --force   # Reset database (use with caution)
php artisan tinker                  # Interactive PHP console
php artisan schedule:run            # Manually trigger scheduled tasks
php artisan allowance:process       # Manually process allowances and reset points
```

### Testing
```bash
composer test                       # Run PHPUnit tests (uses in-memory SQLite)
php artisan test                    # Alternative test command
php artisan test --filter TestName  # Run specific test
```

### Frontend Development
```bash
npm install                         # Install Node dependencies
npm run dev                         # Start Vite dev server
npm run build                       # Build for production
```

### Full Development Environment
```bash
composer dev                        # Runs server, queue, logs, and Vite concurrently
```

### Git Workflow
- **dev branch:** Work and test here
- **main branch:** Production - auto-deploys to Railway
```bash
git checkout dev
# ... make changes ...
git add . && git commit -m "message"
git push origin dev
# When ready to deploy:
git checkout main && git merge dev && git push origin main
```

## Database Models and Relationships

### Family (app/Models/Family.php)
- `owner()` - belongsTo User (family owner/creator)
- `members()` - belongsToMany User (all parents with access)
- `kids()` - hasMany Kid
- `invites()` - hasMany FamilyInvite

### User (app/Models/User.php) - Parents
- `ownedFamilies()` - hasMany Family (families they created)
- `families()` - belongsToMany Family (families they're members of)
- `accessibleKids()` - helper method to get all kids from their families

### Kid (app/Models/Kid.php)
- Extends Authenticatable for kid login system
- `family()` - belongsTo Family
- `transactions()` - hasMany Transaction
- `pointAdjustments()` - hasMany PointAdjustment
- `invite()` - hasOne Invite
- Key fields: `balance`, `points`, `max_points`, `allowance_amount`, `allowance_day`, `points_enabled`

### Transaction (app/Models/Transaction.php)
- `kid()` - belongsTo Kid
- Fields: `type` (deposit/withdrawal), `amount`, `description`, `category`, `initiated_by` (parent/kid)

## Scheduled Tasks (routes/console.php)

The scheduler runs hourly on Railway but only processes allowances at 2:00 AM:

```php
Schedule::command('allowance:process')->hourly();  // America/Chicago
```

### Weekly Allowance Processing (app/Console/Commands/ProcessWeeklyAllowance.php)
- **Schedule:** Runs hourly via Railway cron (`php artisan schedule:run` every hour)
- **Execution:** Only executes logic at 2:00 AM Central Time (skips all other hours)
- **Target kids:** Filters by `allowance_day` matching current day (e.g., 'monday') and active accounts (`username IS NOT NULL`)

**Processing sequence for each eligible kid:**
1. **Check points and post allowance:**
   - If `points >= 1`: Awards `allowance_amount` to balance, creates deposit transaction
   - If `points < 1`: Denies allowance, creates $0 transaction with denial message
2. **Reset points (always happens after allowance check):**
   - Resets points to `max_points` (regardless of whether allowance was awarded)
   - Only resets if `points_enabled = true`
   - Creates record in `point_adjustments` table for audit trail

**Railway Configuration:**
- Railway cron service must run `php artisan schedule:run` every hour (at minimum)
- The command self-checks the hour and only processes at 2:00 AM
- This design allows flexible Railway cron scheduling while maintaining precise 2:00 AM execution

## Environment and Deployment

### Local Development (Laravel Herd)
- PHP 8.4.15
- PostgreSQL database
- Timezone: America/Chicago
- Uses `.env` file with `APP_DEBUG=true`

### Production (Railway)
- PHP 8.2.29 (keep Symfony packages at 7.x for compatibility)
- PostgreSQL database (managed by Railway)
- Timezone: America/Chicago
- Auto-deploys when `main` branch is pushed
- Start command: `php artisan migrate --force && php artisan optimize && /start-container.sh`
- **Cron schedule:** `php artisan schedule:run` runs hourly (minimum requirement)
  - Railway cron can be configured to run hourly: `0 * * * *` (every hour at minute 0)
  - The Laravel scheduler then executes `allowance:process` which self-checks for 2:00 AM

**Required Railway Environment Variables for PWA/Mobile:**
```
SESSION_LIFETIME=262800          # 6 months in minutes (for persistent login)
SESSION_SECURE_COOKIE=true       # HTTPS-only cookies (required for production)
SESSION_SAME_SITE=lax           # Allow cookies in PWA standalone mode
APP_URL=https://allowancelab.com # Full production URL
```

### Email Configuration
- **Service:** Resend API
- **From address:** hello@allowancelab.com
- **Emails:** Welcome emails, family invites, kid invites
- Test with Gmail aliasing: joshua.sopko+test@gmail.com (all go to main inbox)

## Critical Development Notes

### PHP Version Compatibility
- Local uses PHP 8.4.15, production uses PHP 8.2.29
- **Keep Symfony packages at 7.x** (not 8.x) for PHP 8.2 compatibility
- Test locally before deploying to catch version-specific issues

### Security Considerations
- Kids store both hashed passwords and plaintext (`password_plaintext` field) for parent visibility
- Family access control: Always verify parent has access to family before allowing kid operations
- Use `auth:kid` middleware for kid routes, `auth` middleware for parent routes
- Never expose kid authentication tokens to parents or vice versa

### UI/UX Patterns
- Mobile-first responsive design (primary access method)
- Kids have customizable avatar colors that theme their dashboard
- Use "kid-" prefix for CSS classes specific to kid views to prevent conflicts
- All destructive actions require confirmation modals
- Transaction amounts always display with 2 decimal places

### Transaction Integrity
When adding/removing money or adjusting points:
1. Update the kid's `balance` or `points` field
2. Save the kid model
3. Create corresponding record in `transactions` or `point_adjustments` table
4. Never skip audit trail - every change must be logged

### Testing Best Practices
- Tests use in-memory SQLite database (configured in phpunit.xml)
- Test mail uses array driver (no actual emails sent)
- Laravel Breeze includes auth tests in tests/Feature/Auth/
- Create feature tests for new allowance/points logic

## Route Organization

### Parent Routes (routes/web.php)
- `/dashboard` - Parent dashboard (requires `auth` middleware)
- `/manage-family` - Family member and invite management
- `/kids/{kid}/manage` - Individual kid settings
- POST `/kids/{kid}/deposit` - Add money to kid account
- POST `/kids/{kid}/spend` - Subtract money from kid account
- PATCH `/kids/{kid}/points` - Adjust kid's points

### Kid Routes (routes/web.php, prefix: 'kid', guard: 'auth:kid')
- `/kid/login` - Kid login page
- `/kid/dashboard` - Kid dashboard (requires `auth:kid`)
- `/kid/profile` - Kid profile settings
- POST `/kid/deposit` - Kid self-reports deposit
- POST `/kid/spend` - Kid self-reports spending

### Public Routes
- `/invite/{token}` - Kid invite acceptance (creates kid account)
- `/family/accept/{token}` - Family parent invite acceptance

## Common Patterns

### Scoping Queries by Family
```php
// Get kids accessible to current parent
$kids = auth()->user()->accessibleKids();

// Verify parent has access to specific kid
$familyIds = auth()->user()->families()->pluck('families.id');
$kid = Kid::whereIn('family_id', $familyIds)->findOrFail($kidId);
```

### Creating Transactions
```php
$kid->transactions()->create([
    'type' => 'deposit',              // or 'withdrawal'
    'amount' => $amount,
    'description' => 'Weekly Allowance',
    'initiated_by' => 'parent',       // or 'kid'
    'category' => null                // optional categorization
]);
```

### Adjusting Points
```php
$kid->pointAdjustments()->create([
    'points_change' => $newPoints - $kid->points,
    'previous_points' => $kid->points,
    'new_points' => $newPoints,
    'reason' => 'Parent adjustment'
]);
$kid->points = $newPoints;
$kid->save();
```

### Creating and Managing Goals
```php
// Create a goal
$goal = Goal::create([
    'family_id' => $kid->family_id,
    'kid_id' => $kid->id,
    'created_by_user_id' => Auth::id(), // or null if kid created
    'title' => 'New Bike',
    'description' => 'Saving for a mountain bike',
    'target_amount' => 250.00,
    'auto_allocation_percentage' => 20, // 20% of weekly allowance
]);

// Manual deposit to goal
$goal->current_amount += $amount;
$kid->balance -= $amount;
$goal->save();
$kid->save();

GoalTransaction::create([
    'goal_id' => $goal->id,
    'kid_id' => $kid->id,
    'family_id' => $goal->family_id,
    'amount' => $amount,
    'transaction_type' => 'manual_deposit',
    'description' => 'Manual deposit to goal',
    'performed_by_user_id' => Auth::id(),
    'created_at' => now(),
]);

// Redeem completed goal (parent only)
$kid->balance += $goal->current_amount;
$goal->status = 'redeemed';
$goal->redeemed_at = now();
$goal->save();
$kid->save();
```

## Current Feature Status

### Implemented
- Parent and kid authentication systems
- Family multi-parent management with email invites
- Kid account creation via invite links
- Allowance scheduling and automated posting
- Points tracking with automated weekly reset
- Transaction ledger with parent/kid initiated tracking
- Email delivery via Resend API
- Mobile-responsive dashboards for both parents and kids
- **Goals feature (v1.1)** - Savings goals with auto-allocation and manual fund transfers

### Future Roadmap
- **v1.2:** Chore tracking with parent approval workflow
- **v2.0+:** TBD based on real family usage feedback

## Important File Locations

### Models
- `app/Models/Family.php` - Family container
- `app/Models/User.php` - Parent accounts
- `app/Models/Kid.php` - Kid accounts (Authenticatable)
- `app/Models/Transaction.php` - Money movement records
- `app/Models/PointAdjustment.php` - Points change audit trail
- `app/Models/FamilyInvite.php` - Parent invitation system
- `app/Models/Invite.php` - Kid invitation system
- `app/Models/Goal.php` - Kid savings goals
- `app/Models/GoalTransaction.php` - Goal fund movement records

### Controllers
- `app/Http/Controllers/KidController.php` - Parent-side kid management
- `app/Http/Controllers/KidAuthController.php` - Kid login/dashboard
- `app/Http/Controllers/KidDashboardController.php` - Kid-initiated transactions
- `app/Http/Controllers/ManageFamilyController.php` - Family member management
- `app/Http/Controllers/FamilyInviteController.php` - Family invite acceptance
- `app/Http/Controllers/GoalController.php` - Goals management for kids and parents

### Views
- `resources/views/parent/dashboard.blade.php` - Parent main view
- `resources/views/kid/dashboard.blade.php` - Kid main view
- `resources/views/kids/manage.blade.php` - Individual kid settings
- `resources/views/manage-family.blade.php` - Family management
- `resources/views/kid/auth/login.blade.php` - Kid login form
- `resources/views/goals/` - Goals views (index, create, edit, show, parent-index)

### Commands
- `app/Console/Commands/ProcessWeeklyAllowance.php` - Weekly allowance processing (checks points, posts allowance, processes goal auto-allocations, resets points)
- `routes/console.php` - Schedule configuration

### Configuration
- `composer.json` - PHP dependencies (keep Symfony at 7.x)
- `package.json` - Node dependencies
- `phpunit.xml` - Test configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `.env.example` - Environment template
