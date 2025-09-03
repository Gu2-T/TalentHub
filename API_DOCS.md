# TalentHub API Documentation

This document describes the main API endpoints for TalentHub.

---

Email Notifications

- Registration verification code

- New application alert to employer

- Shortlist notification (prepare for interview)

- Rejection notification (apologetic message)


Auth

POST /api/auth/register → Register new user

POST /api/auth/verify-email → Verify email

POST /api/auth/login → Login

POST /api/auth/logout → Logout

POST /api/auth/refresh → Refresh token

GET /api/auth/me → Get current user


Profile

POST /api/profiles/create → Create profile (Employer/Applicant)

PATCH /api/update-profile → Update profile

GET /api/profile → Get my profile

GET /api//photo/{folder}/{filename} → Get photo


Jobs

POST /api/jobs → Create job (employer only, verified)

GET /api/jobs → List jobs (role-specific visibility)

GET /api/jobs/{id} → View single job

PUT /api/jobs/{id} → Update job (employer only)

DELETE /api/jobs/{id} → Delete job (employer only)


Applications

POST /api/jobs/{jobId}/apply → Apply to a job (applicant)

PUT /api/applications/{id}/shortlist → Shortlist applicant (employer)

PUT /api/applications/{id}/reject → Reject applicant (employer)

Admin

GET /api/admin/employers/pending → List pending employer profiles

PATCH /api/admin/employers/{id}/approve → Approve employer

PATCH /api/admin/employers/{id}/reject → Reject employer with reason