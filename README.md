# TalentHub 

TalentHub is a job marketplace platform built with **Laravel** where:
- Employers can create and manage job postings.
- Applicants can create profiles and apply to jobs.
- Admins verify employers and manage the platform.
- Email notifications are sent for registration, applications, shortlisting, and rejection.

---
## 👥 User Roles
- `1 = Admin`
- `2 = Employer`
- `3 = Applicant`

---

##  Features
- **Authentication**
  - Register, Login, Email verification
  - JWT authentication with refresh tokens
- **Profiles**
  - Employer profiles (with TIN, verification by Admin)
  - Applicant profiles (with Resume & Profile Photo)
- **Jobs**
  - Verified employers can post jobs with deadlines
  - Applicants see only active jobs
- **Applications**
  - Applicants apply to jobs
  - Employers can shortlist or reject applications
  - Email notifications for applicants and employers
- **Admin**
  - Approves or rejects employer profiles

---

##  Tech Stack
- **Backend:** Laravel 10
- **Database:** MySQL
- **Authentication:** JWT (php-open-source-saver/jwt-auth)
- **File Storage**: Laravel Storage (Public Disk)
- **Mail:** Laravel Mail with Blade templates

---

##  Installation
```bash
# Clone repository
git clone https://github.com/Gu2-T/TalentHub.git

cd TalentHub

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start server
php artisan serve

