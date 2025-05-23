# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased
- Implement CSRF protection
- Add remember me checkbox
- New user email verification
- Refactor account-related views and tests
- Split User Enabled vs Email verified in admin views
- Refactor password reset functionality
- Update Vite version
- Cleanup Monorepo

This version include new migrations:
- DropPasswordResetsTable : Drop the PasswordResets table
- DropVerificationsTable : Drop the Verifications table
- UpdateUsersTable : Add `password_last_set` column to the User table
- UserVerificationTable : Add new common table for all user verifications 

## 6.0.0-alpha.4 - 2025-03-31
- Add frontend permission check.
- Permission's `conditions` field is **DEPRECATED**. This field cannot be verified on the frontend and is prone to confusion. A better permission system is planned for UF6.1 which will make this field obsolete anyway.
- All built in permissions are updated to `always()`, plus :
  - `create_user_field` : Removed. Will be replaced by group permissions management in 6.1;
  - `delete_user` : Conditions changed to `always()`. Master ID is manually enforced in the controller. Role isn't enforced anymore;
  - `update_user_field`: Conditions changed to `always()`. Was giving access to all field anyway. More granular permissions should be added in 6.1 (for enable, password, etc.);
  - `update_user_field_group` : Removed. The ability to edit users in your own group is removed. Granular group permissions management will be added in 6.1;
  - `update_user_field_role` : Renamed the slug to `update_user_role`. 
  - `update_role_field` : Conditions changed to `always()`. Was giving access to all field anyway. Ability to change permissions could be added in 6.1;
  - New `uri_group_own` slug to view the group page of your own group.
  - Permission with `uri_user` slug which allowed to view the user page of any user in your group, except the master user and Site and Group Administrators: Renamed to `uri_user_in_group` and allow to view the user page of any user in your group period.
  - Permission with `view_group_field` slug which allowed to view certain properties of your own group : Changed the slug to `view_group_field_own` and allow to view properties of your own group.
  - `view_role_field`: Conditions changed to `always()`. Was giving access to all field anyway. Ability to change permissions could be added in 6.1;
  - `view_user_field`: Conditions changed to `always()`.
  - `view_user_field_group` : Removed. The condition was too complex.
- New permissions with slugs `view_user_activities` & `view_user_roles`
- Move System Info to new Config Page and add Cache clearing UI
- Login redirect to the previous page
- Fix type error present in alpha.3

## 6.0.0-alpha.3 - 2025-03-01
- Implement Frontend Translations
- Replace Moment with Luxon
- Remove old locales & cleanup French & English dictionaries 

## 6.0.0-alpha.2 - 2025-01-22
- First real Alpha release of UserFrosting 6
