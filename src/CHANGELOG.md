# Changelog

All notable changes to Oh Eweedy (Weedy Gallery) are documented here.

- **Version 6.0 (March 2026)**

    - UI: Lock icon added for sensitive/protected posts – visual indicator for protected content.
    - Features: Image zoom functionality – click to zoom and view images in detail.
    - UX: Refactored sharing functionality with improved user experience.
    - Design: Improved color, thicker contrast throughout the site.
    - UI: Creation information now shown on tab with timestamp in posts.

- **Version 5.2 (April 2025)**

    - Features: Lima City partner link added.

- **Version 5.1 (October-December 2024)**

    - Features: Comments now visible between **users only** (December 2024).
    - UI: Published dates shown in main gallery and user posts (October 2024).
    - UI: Better layout for showing posts in grid at profile page (November 2024).
    - UX: Improved comment toggle functionality (December 2024).
    - Fixes: Fixed bugs with post-to-draft redirects (November 2024).
    - Admin: Enhanced admin panel improvements (November/December 2024).
    - Dev: Better seeder structure with factory updates, VS Code development environment recommendations (November 2024).
    - Fixes: Fixed user profile showing unpublished posts (November 2024).

- **Version 5.0 (September 2024)**

    - Features: **User profiles** introduced – browse other users' galleries and view their posted images.
    - Features: User descriptions – add personal descriptions to profiles.
    - UI: Profile links – quick access to user profiles from comments and posts.
    - Features: Last activity tracking – see when users were last active.
    - UI: Improved profile page layout with better image grid display, round user avatars throughout the site.
    - UX: Clickable usernames in various places, better handling of long usernames with automatic truncation.
    - UI: Gray styling for pagination next link.
    - Branding: Renamed to **"Weedy Gallery"** in the title bar.

- **Version 4.0 (August 2024)**

    - Security: **Post privacy controls** – mark posts as sensitive/protected.
    - Features: Publishing workflow – better control over when posts become visible with **draft system**.
    - Admin: Enhanced moderation tools for managing published content.
    - Features: Email registration with email verification, password visibility toggle on registration.
    - UI: Published dates shown instead of creation dates in galleries.
    - UX: Improved visibility controls, better post republishing workflow.
    - Admin: Enhanced admin panel for content management, improved registration interface.

- **Version 3.0 (August 2024)**

    - Performance: **Multiple image variants** – automatic generation of different image sizes for faster loading.
    - Performance: Optimized thumbnails for faster gallery browsing.
    - UX: Placeholder images for graceful handling of posts without images.
    - Performance: Optimized image loading for better performance, better image handling in edit mode.
    - Localization: German locale and timezone support.
    - Compliance: Privacy policy checkbox on registration, **GDPR/DSGVO compliance pages** added.

- **Version 2.0 (July-August 2024)**

    - Features: **Pagination** – browse gallery page by page (12 posts per page).
    - Features: Post navigation – jump between posts easily, navigation controls at top and bottom.
    - Branding: New favicon (tree trunk logo) and "Weedy Gallery" application name.
    - UX: Separate pagination for published posts and drafts, mobile-friendly pagination controls.
    - Features: Proper post sorting by creation date, user-specific image galleries ("My Images").

- **Version 1.0 (July 2024)**

    - Migration: **Old post migration** – successfully migrated all images from previous PHP website.
    - Migration: Legacy data import – preserved descriptions and metadata from old database.
    - Migration: Username matching – connected old posts to new user accounts.
    - Dev: Factory and seeder improvements for better test data, updated database structure for legacy content.

- **Version 0.0 (June-July 2024)**

    - Features: **Image gallery** – view all posted images in beautiful gallery layout.
    - Features: User registration and login, post images and share with community.
    - Features: Comments on posts, custom color theme with brand colors.
    - UI: Favicons with tree trunk logo, admin dashboard for user and content management.
    - Features: Profile images – upload your own profile picture.
    - UX: Mobile-friendly responsive design, image edit and delete functionality.
    - UI: Clean, modern interface with custom colors and improved form layouts.
    - UI: Display images in clean grid layout with post titles, creation info, comment counts, and user attribution.

---

## Infrastructure & Setup (2019-2021)

- Platform: Built on **Laravel framework** with Docker-based development environment (LEMP stack).
- Stack: MySQL 5.7 database, Redis caching, MailHog for email testing.
- Stack: Nginx web server, PHP 7.4 runtime.
- Dev: Composer for PHP dependencies, NPM for JavaScript dependencies.
- Storage: Persistent MySQL data storage.

---

*For technical details and full commit history, refer to the git log.*
