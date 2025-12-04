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
- Points reset on their `allowance_day` by scheduled command `points:reset`
- Allowance only posts if kid has `points >= 1` on their allowance day
- Points system can be disabled per kid via `points_enabled` boolean

## Common Development Commands

### Laravel Development
```bash
composer install                    # Install PHP dependencies
php artisan migrate                 # Run database migrations
php artisan migrate:fresh --force   # Reset database (use with caution)
php artisan tinker                  # Interactive PHP console
php artisan schedule:run            # Manually trigger scheduled tasks
php artisan allowance:post          # Manually post allowances
php artisan points:reset            # Manually reset points
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

Two critical cron jobs run daily:

```php
Schedule::command('allowance:post')->dailyAt('02:00');  // America/Chicago
Schedule::command('points:reset')->dailyAt('02:01');    // America/Chicago
```

### Allowance Posting (app/Console/Commands/PostAllowances.php)
- Runs daily at 2:00 AM Central Time
- Filters kids by `allowance_day` matching current day (e.g., 'monday')
- **Awards allowance:** If kid has `points >= 1`, adds `allowance_amount` to balance
- **Denies allowance:** If kid has `points < 1`, creates $0 transaction with denial message
- Only processes kids with active accounts (`username IS NOT NULL`)

### Points Reset (app/Console/Commands/ResetPoints.php)
- Runs daily at 2:01 AM Central Time (after allowance posting)
- Resets points to `max_points` for kids whose `allowance_day` is today
- Only resets if `points_enabled = true`
- Creates record in `point_adjustments` table for audit trail

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
- Separate cron service runs `php artisan schedule:run` every minute

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

### Future Roadmap
- **v1.1:** Manual savings goals for kids
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

### Controllers
- `app/Http/Controllers/KidController.php` - Parent-side kid management
- `app/Http/Controllers/KidAuthController.php` - Kid login/dashboard
- `app/Http/Controllers/KidDashboardController.php` - Kid-initiated transactions
- `app/Http/Controllers/ManageFamilyController.php` - Family member management
- `app/Http/Controllers/FamilyInviteController.php` - Family invite acceptance

### Views
- `resources/views/parent/dashboard.blade.php` - Parent main view
- `resources/views/kid/dashboard.blade.php` - Kid main view
- `resources/views/kids/manage.blade.php` - Individual kid settings
- `resources/views/manage-family.blade.php` - Family management
- `resources/views/kid/auth/login.blade.php` - Kid login form

### Commands
- `app/Console/Commands/PostAllowances.php` - Daily allowance posting
- `app/Console/Commands/ResetPoints.php` - Weekly points reset
- `routes/console.php` - Schedule configuration

### Configuration
- `composer.json` - PHP dependencies (keep Symfony at 7.x)
- `package.json` - Node dependencies
- `phpunit.xml` - Test configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `.env.example` - Environment template
