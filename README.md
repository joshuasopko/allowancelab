# AllowanceLab

## Project Overview
AllowanceLab is a family allowance management application that positions parents as "the bank" while teaching children financial responsibility through a point-based accountability system. The app bridges the gap between overly complex banking apps like Greenlight and existing chore trackers that are either too simple or complicated.

**Live URL:** https://allowancelab.com  
**Owner:** Mr. Joshua (joshua.sopko@gmail.com)  
**Personal Context:** 6 children, making this both a personal solution and potential public product

## Core Philosophy
- Parents maintain full financial control (they are "the bank")
- Children learn responsibility through weekly point system
- Kids start each week with maximum points (default: 10)
- If points drop to zero by allowance day, no allowance is posted
- Parents can adjust points, add/subtract money, set allowances

## Tech Stack
- **Framework:** Laravel 11 with Breeze authentication
- **Database:** PostgreSQL
- **Hosting:** Railway (us-west1)
- **Email:** Resend API (hello@allowancelab.com)
- **Version Control:** GitHub
- **Local Environment:** Laravel Herd (PHP 8.4.15)
- **Production Environment:** Railway (PHP 8.2.29)

## Architecture

### Family-Based Structure
- Application uses family-based architecture (not user-based)
- Multiple parents can manage the same children's accounts
- Parents belong to families via `family_parent` pivot table
- Children belong to families via `family_id` foreign key

### Key Models
- **Family:** Container for parents and kids
- **Parent:** Users who manage family accounts
- **Kid:** Children who receive allowances and earn/lose points
- **Transaction:** Records all money movements (parent actions, allowances, etc.)

### Database Tables
- `families`: Family containers
- `users`: Parent accounts (authenticated users)
- `family_parent`: Pivot table linking parents to families
- `kids`: Children accounts linked to families
- `transactions`: All monetary transactions with type tracking
- `allowances`: Scheduled allowance rules per kid
- `password_reset_tokens`: Laravel auth
- `sessions`: Laravel sessions
- `cache` & `cache_locks`: Laravel cache
- `jobs` & `job_batches` & `failed_jobs`: Queue system

## Key Features (Current MVP)

### Parent Dashboard
- View all kids' balances and points
- Quick actions: add/subtract money, adjust points
- Add new children to family
- Invite other parents to join family
- View recent transactions

### Kid Dashboard  
- View current balance and points
- See transaction history (ledger)
- Visual indicators for transaction types
- Mobile-responsive design

### Allowance System
- Automated posting daily at 2:00 AM (America/Chicago)
- Only posts if kid has points > 0
- Parents can set allowance amount and day of week per kid
- Allowances can be enabled/disabled per kid

### Points System
- Automated reset every Sunday at 2:01 AM (America/Chicago)
- Default starting points: 10 (configurable per kid)
- Parents can manually adjust points anytime
- Points visible on both parent and kid dashboards

### Transaction Tracking
- All money movements recorded with types:
  - `parent_add`: Parent added money
  - `parent_subtract`: Parent subtracted money
  - `allowance`: Automated allowance posting
  - `allowance_denied`: Attempted post but points were 0
- Ledger views with filtering capabilities
- Real-time balance updates

### Family Management
- Email invitation system for additional parents
- Invite codes stored in database
- Email sent via Resend API
- Parents can manage all kids in family

## Automated Tasks (Cron Jobs)

### Schedule Configuration
Located in: `app/Console/Kernel.php`
```php
$schedule->command('allowances:post')->dailyAt('02:00');
$schedule->command('kids:reset-points')->weeklyOn(0, '02:01'); // Sunday 2:01 AM
```

### Railway Cron Configuration
Railway cron service configured separately to trigger schedule:run

## Development Workflow

### Branch Strategy
- **main:** Production branch (auto-deploys to Railway)
- **dev:** Development branch (work here, test locally)

### Deploy Process
1. Work and test on `dev` branch locally
2. When ready to deploy:
```bash
git checkout main
git merge dev
git push origin main
```
3. Railway auto-deploys when `main` updates

### Testing Emails
Use Gmail + aliasing for unlimited test accounts:
- joshua.sopko+test1@gmail.com
- joshua.sopko+test2@gmail.com
- joshua.sopko+anything@gmail.com

All deliver to main inbox but treated as unique users.

## Environment Configuration

### Railway Environment Variables
```
APP_NAME=AllowanceLab
APP_ENV=production
APP_KEY=[generated]
APP_DEBUG=false
APP_TIMEZONE=America/Chicago
APP_URL=https://allowancelab.com

DB_CONNECTION=pgsql
DB_HOST=[Railway PostgreSQL]
DB_PORT=[Railway PostgreSQL]
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=[Railway PostgreSQL]

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=hello@allowancelab.com
MAIL_FROM_NAME="Joshua @ AllowanceLab"
RESEND_API_KEY=[Resend API Key]

QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### Railway Custom Start Command
```bash
php artisan migrate --force && php artisan optimize && /start-container.sh
```

### Local Environment (.env)
Same as Railway but with local database credentials and APP_DEBUG=true

## Important File Locations

### Controllers
- `app/Http/Controllers/ParentDashboardController.php`
- `app/Http/Controllers/KidDashboardController.php`
- `app/Http/Controllers/Auth/RegisteredUserController.php`

### Models
- `app/Models/Family.php`
- `app/Models/User.php` (Parent)
- `app/Models/Kid.php`
- `app/Models/Transaction.php`
- `app/Models/Allowance.php`

### Views
- `resources/views/parent/dashboard.blade.php`
- `resources/views/kid/dashboard.blade.php`
- `resources/views/parent/add-kid.blade.php`
- `resources/views/parent/invite-parent.blade.php`

### Commands
- `app/Console/Commands/PostAllowances.php`
- `app/Console/Commands/ResetKidPoints.php`

### Emails
- `app/Mail/WelcomeEmail.php`
- `app/Mail/InviteParentEmail.php`

## UI/UX Principles

### Design Philosophy
- Mobile-first responsive design (primary access method)
- Clean, professional interface
- Dynamic theme colors based on kid avatar selection
- Comprehensive confirmation modals for destructive actions
- Inline validation with shake animations
- Real-time balance updates

### CSS Organization
- Main styles: `public/css/app.css`
- Kid-specific styles use "kid-" prefix to prevent conflicts
- Unique class and function names throughout

### JavaScript
- Organized in separate files by feature
- Proper event delegation
- Confirmation modals for important actions

## Database Management

### Reset Database (Railway Console)
```bash
php artisan migrate:fresh --force
```

### Quick Data Cleanup (Railway Tinker)
```bash
php artisan tinker
User::truncate();
Family::truncate();
exit
```

### Deploy with Database Reset
Temporarily change Railway start command to:
```bash
php artisan migrate:fresh --force && php artisan optimize && /start-container.sh
```
Then change back after deploy.

## Current Status

### Working Features ✅
- Parent registration and authentication
- Family creation and management
- Add children to family
- Set allowance amounts and schedules
- Add/subtract money from kid accounts
- Adjust kid points manually
- Automated allowance posting (daily 2:00 AM)
- Automated points reset (Sunday 2:01 AM)
- Email delivery via Resend API
- Invite additional parents via email
- Parent dashboard (desktop + mobile)
- Kid dashboard (desktop + mobile)
- Transaction ledger with filtering
- Visual indicators for denied allowances
- Multi-parent family management
- Real-time balance calculations

### Known Considerations
- Local PHP 8.4.15 vs Railway PHP 8.2.29 (keep packages PHP 8.2 compatible)
- Symfony packages must stay at 7.x (not 8.x)
- Queue system configured but not actively used (emails send synchronously via Resend API)

## Future Roadmap

### Version 1.1 - Manual Savings Goals
- Kids can set savings goals
- Visual progress tracking
- Goal completion notifications

### Version 1.2 - Chore Tracking
- Parents assign chores
- Kids mark chores complete
- Parent approval workflow
- Points tied to chore completion

### Version 2.0+ - TBD
- Waiting for real family usage feedback before planning

## Development Best Practices

### Commit Strategy
- Frequent commits with descriptive messages
- Always test locally before merging to main
- Use step-by-step implementation approach

### Code Style
- Proper Laravel conventions
- Clear comments above code snippets
- Organized Blade templating
- Unique naming to prevent conflicts

### Version Control
- Work on `dev` branch
- Merge to `main` only when ready to deploy
- Never push broken code to `main`

## Support & Resources

### Cloudflare Email Routing
- hello@allowancelab.com forwards to joshua.sopko@gmail.com
- DNS records configured and verified
- Free email forwarding service

### Resend Dashboard
- Monitor email delivery
- View logs and analytics
- Domain: allowancelab.com (verified)

### Railway Dashboard
- View deployment logs
- Monitor application health
- Manage environment variables
- Access PostgreSQL database

## Quick Reference Commands

### Git Workflow
```bash
git checkout dev              # Switch to dev branch
git add .                     # Stage changes
git commit -m "message"       # Commit changes
git push origin dev           # Push dev branch
git checkout main             # Switch to main
git merge dev                 # Merge dev into main
git push origin main          # Deploy to Railway
```

### Laravel Commands
```bash
php artisan migrate           # Run migrations
php artisan migrate:fresh     # Drop and recreate tables
php artisan optimize          # Clear and cache config
php artisan tinker            # Interactive console
php artisan schedule:run      # Manually run scheduled tasks
```

### Composer Commands
```bash
composer install              # Install dependencies
composer update               # Update dependencies
composer require package      # Add new package
composer remove package       # Remove package
```

## Contact
- **Developer:** Mr. Joshua
- **Email:** joshua.sopko@gmail.com
- **Live Site:** https://allowancelab.com
- **Repository:** [GitHub repository URL]

---
Last Updated: December 4, 2025