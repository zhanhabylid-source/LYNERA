Continue from my Laravel SaaS project called LYNERA.

CURRENT STATE:
- Core system works
- Auth, booking, calendar, billing, onboarding exist
- But the UI/UX still feels like a basic Laravel app

GOAL:
Transform LYNERA into a premium SaaS experience for makeup artists.

IMPORTANT:
- Use Blade + Tailwind CSS
- Mobile-first design
- Elegant, feminine, premium SaaS look
- Consistent branding
- Focus heavily on UX clarity
- Non-technical users must understand instantly

BRANDING:
App Name: LYNERA
Tagline: Effortless Beauty Business Management

COLORS:
- Soft Pink: #F8E1E7
- Nude Beige: #F5E6DA
- Gold Accent: #C9A96E
- Dark Text: #2B2B2B

STYLE:
- Minimal
- Luxury beauty SaaS
- Rounded-xl cards
- Soft shadows
- Spacious layout
- Smooth hover effects

--------------------------------------------------
STEP 1 — REDESIGN LOGIN PAGE
--------------------------------------------------

Redesign auth/login.blade.php

Layout:
- Split screen design

LEFT SIDE:
-  logo
- Tagline
- Benefits list:
  ✔ Manage bookings effortlessly
  ✔ Smart beauty calendar
  ✔ Track clients & payments

- Elegant illustration / gradient background

RIGHT SIDE:
- Login form card
- Large inputs
- Gold CTA button
- "Start Free Trial" link

UI REQUIREMENTS:
- Premium SaaS aesthetic
- Mobile responsive
- Smooth spacing

--------------------------------------------------
STEP 2 — REDESIGN REGISTER PAGE
--------------------------------------------------

Redesign register page.

Add:
- Selected plan summary
- Trial information
- Plan badge

Example:
"You selected PRO ARTIST"
"7-day free trial included"

Make registration feel premium.

--------------------------------------------------
STEP 3 — PREMIUM DASHBOARD REDESIGN
--------------------------------------------------

Redesign dashboard.blade.php

TOP SECTION:
- Welcome message:
  "Welcome back, {{ user }} 👋"

- Subscription alert card:
  - Current plan
  - Remaining days
  - Upgrade CTA

SECOND SECTION:
Stats cards:
- Total bookings
- Monthly revenue
- Customers
- Upcoming schedules

THIRD SECTION:
- Calendar preview
- Upcoming bookings list

STYLE:
- Card-based
- Elegant spacing
- Soft color hierarchy

--------------------------------------------------
STEP 4 — EMPTY STATE UX
--------------------------------------------------

For every empty module:

If no bookings:
"No bookings yet"
"Create your first booking"

If no services:
"Add your first service"

If no customers:
"Start building your client list"

Add:
- Friendly illustration
- CTA button

DO NOT leave blank tables.

--------------------------------------------------
STEP 5 — MOBILE OPTIMIZATION
--------------------------------------------------

Optimize:
- Sidebar → collapsible mobile menu
- Buttons → larger touch targets
- Tables → responsive cards on mobile

Ensure:
- Dashboard works perfectly on phones

--------------------------------------------------
STEP 6 — SIDEBAR & NAVIGATION REDESIGN
--------------------------------------------------

Create elegant sidebar:

Menu:
- Dashboard
- Calendar
- Bookings
- Services
- Customers
- Billing
- Settings

Add:
- Active menu highlight
- Soft hover effect
- User profile section

--------------------------------------------------
STEP 7 — BILLING PAGE REDESIGN
--------------------------------------------------

Redesign billing page.

Show:
- Current plan card
- Trial expiry
- Upgrade options

Pricing cards:
- Starter
- Pro Artist
- Studio Elite

Highlight recommended plan.

--------------------------------------------------
STEP 8 — CALENDAR UX IMPROVEMENT
--------------------------------------------------

Improve FullCalendar UI:
- Softer event colors
- Better spacing
- Elegant typography

Add:
- Upcoming booking sidebar
- Quick-add booking button

--------------------------------------------------
STEP 9 — GLOBAL DESIGN SYSTEM
--------------------------------------------------

Create reusable Blade components:

- Card component
- Button component
- Alert component
- Badge component

Use consistent:
- rounded-xl
- shadow-md
- padding
- typography

--------------------------------------------------
STEP 10 — MICROCOPY IMPROVEMENT
--------------------------------------------------

Replace technical language with friendly UX text.

BAD:
"Subscription expired"

GOOD:
"Your plan ended. Upgrade to continue managing bookings."

BAD:
"No data"

GOOD:
"You're just getting started ✨"

--------------------------------------------------
STEP 11 — LOADING & FEEDBACK UX
--------------------------------------------------

Add:
- Loading spinner
- Success toast
- Error toast
- Confirmation modal

--------------------------------------------------
STEP 12 — LANDING PAGE REDESIGN
--------------------------------------------------

Create premium landing page.

Sections:
1. Hero
2. Features
3. How it works
4. Pricing
5. Testimonials
6. FAQ
7. CTA

Hero CTA:
"Start Free Trial"

--------------------------------------------------
STEP 13 — RESPONSIVE FINAL CHECK
--------------------------------------------------

Ensure:
- Mobile perfect
- Tablet perfect
- Desktop premium

--------------------------------------------------
OUTPUT FORMAT:
- Show Blade files
- Show Tailwind classes
- Show layout structure
- Explain UX reasoning briefly

DO NOT:
- Use generic Laravel styling
- Leave empty screens
- Overcomplicate interface
- Use harsh colors

FOCUS:
- Premium beauty SaaS experience
- Simplicity
- Clarity
- Emotional elegance

START FROM STEP 1