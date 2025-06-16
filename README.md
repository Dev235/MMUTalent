7/6/2025
- Done with New User Registration, Login, FAQ, Question Submission and Feedback Form 
- There is 2 table for this one is users another one is faq

**To try it out**
1) Access the index.php to try out the Login and New User Registration page
2) Access faq.php to try out the feedback form.

13/6/2025
- Edited registration.php (Enforced Password Policy)
- Done manageFAQ (I combined with manageFeedback as well)
- Done with Admins Announcements/Workshops

16/6/2025
- Did talent management page
- Profile Page

17/6/2025
1. Dynamic User Profile System

Created userDashboard.php:
This page now functions as a public-facing profile.
It dynamically displays a user's name, faculty, date of birth, and "About Me" description from the database.
It can show the logged-in user's profile by default, or any other user's profile if an ID is provided in the URL (e.g., userDashboard.php?id=5).
Created editProfile.php:
A dedicated page for users to add or update their personal information.
Includes a form with fields for name, student ID, faculty, date of birth, and a textarea for the "About Me" section.
Added profile picture upload functionality.
Enhanced Security: The "Edit Profile" and talent management buttons on the dashboard will only appear if the logged-in user is viewing their own profile.
2. Talent Management

Created addTalent.php, editTalent.php, and viewTalent.php:
Users can now add, edit, and delete their "talents" (services) from their profile.
Each talent can have a title, a description, and its own image.
The dashboard displays all talents in a responsive three-column grid.
Talent Detail Page:
Clicking on a talent now leads to a dedicated viewTalent.php page showing its full details, image, and the profile of the user offering it.
Added a dynamic "Back" button using JavaScript to return the user to their previous page (either the dashboard or a future catalogue page).
3. Session-Based Shopping Cart

Created shoppingCart.php and cart_logic.php:
Implemented a full shopping cart system using PHP sessions to store items temporarily.
Users can add talents to the cart from the viewTalent.php page.
The cart page displays all items, the total price, and allows users to remove items or clear the entire cart.
Checkout & Order Simulation:
Created a checkout.php page with a simulated payment form.
Created an order_success.php page which thanks the user and clears the cart from the session after a "purchase".
Updated Navigation Bar (navbar.php):
The main navigation now includes a link to the shopping cart.
A red badge with the number of items in the cart appears when the cart is not empty.
Links like "My Profile" and "Shopping Cart" now only appear if a user is logged in.
4. Database Updates

Modified users table: Added new columns (student_id, faculty, date_of_birth, about_me, profile_picture) to support the detailed user profiles.
Created services table: A new table to store all the details for each talent offered by users, linking back to the user's ID.
