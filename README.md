# Allstage E-Commerce Platform

Allstage is a full-stack, heavyweight streetwear e-commerce platform. It is split into three main components: a custom PHP REST API, an admin dashboard panel, and a storefront client.

---

## Project Structure

* **/backend**: Custom lightweight PHP backend API. Handles routing, controllers, database models, and routes requests to MySQL via PDO.
* **/Frontend**: Storefront client built with React, Vite, and TanStack Start / TanStack Router.
* **/admin/admin-hub-main**: Administrative dashboard panel built with React, Vite, TanStack Router, Radix UI, and Recharts.

---

## Local Setup Prerequisites

Ensure you have the following installed on your machine:
1. **XAMPP** (includes Apache and MySQL)
2. **Node.js** (v18 or higher) & **npm** (comes with Node)
3. **Git** (for cloning and version control)

---

## Step-by-Step Installation Guide

Follow these steps to run the complete stack locally using XAMPP:

### Step 1: Clone the Project into XAMPP htdocs
For XAMPP Apache to serve your PHP backend automatically, the project folder **must** reside inside XAMPP's `htdocs` directory:
```bash
# Navigate to htdocs
cd C:\xampp\htdocs

# Clone or move your files here so the structure matches:
# C:\xampp\htdocs\<your-folder-name>\All-stage
```
*(For example, if your folder is `all-stage`, the full path will be `C:\xampp\htdocs\all-stage\All-stage`).*

---

### Step 2: Database Setup (MySQL)
1. Launch the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**.
2. Open your browser and navigate to **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Click **New** in the left sidebar, name the database `allstag`, and click **Create**.
4. To import the schema and data:
   * Select the `allstag` database.
   * Go to the **Import** tab.
   * Choose and import [allstage_complete.sql](file:///c:/xampp/htdocs/all-stage/All-stage/backend/database/allstage_complete.sql) from the `backend/database/` directory. This single file sets up all tables, foreign key constraints, default categories/products, and the initial administrator account.

---

### Step 3: Configure & Run the PHP Backend

You have two options to run your backend API server locally:

#### Option A: Running with XAMPP (Apache)
Choose this option if you are using XAMPP's Apache server to host your local files.

1. Navigate to the `backend/` directory:
   ```bash
   cd backend
   ```
2. Create a `.env` file with the following configuration:
   ```ini
   APP_NAME="Allstag"
   APP_ENV=local
   APP_DEBUG=true
   # Point to the XAMPP htdocs subdirectory path:
   APP_URL=http://localhost/all-stage/All-stage/backend/public
   TIMEZONE=Asia/Kolkata

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=allstag
   DB_USERNAME=root
   DB_PASSWORD=""
   ```
   *(Note: Casing matters! Make sure the folder names in `APP_URL` exactly match the folders inside your `htdocs` directory).*

3. **Verify Apache Routing**:
   Ensure the `RewriteBase` in `backend/public/.htaccess` matches your subdirectory path:
   ```apache
   RewriteBase /all-stage/All-stage/backend/public/
   ```

#### Option B: Running without XAMPP (PHP Built-in Server)
Choose this option if you want to run the backend via command line without placing it in XAMPP's `htdocs` directory.

1. Navigate to the backend public directory:
   ```bash
   cd backend/public
   ```
2. Start the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
3. Create a `.env` file in the `backend/` directory with the following configuration:
   ```ini
   APP_NAME="Allstag"
   APP_ENV=local
   APP_DEBUG=true
   # Point to the local PHP server port:
   APP_URL=http://localhost:8000
   TIMEZONE=Asia/Kolkata

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=allstag
   DB_USERNAME=root
   DB_PASSWORD=""
   ```
4. **Update Frontend & Admin configurations** to point to the port `8000` API:
   * In [admin/admin-hub-main/.env](file:///c:/xampp/htdocs/all-stage/All-stage/admin/admin-hub-main/.env):
     ```ini
     VITE_ADMIN_API_URL=http://localhost:8000/api/admin
     ```
   * In [Frontend/.env](file:///c:/xampp/htdocs/all-stage/All-stage/Frontend/.env):
     ```ini
     VITE_API_URL=http://localhost:8000/api
     ```

---

### Step 4: Configure & Run the Admin Panel
1. Open a new terminal and navigate to the admin directory:
   ```bash
   cd admin/admin-hub-main
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Create a `.env` file in the `admin/admin-hub-main/` directory:
   ```ini
   # Points the admin panel to the backend API served by Apache
   VITE_ADMIN_API_URL=http://localhost/all-stage/All-stage/backend/public/api/admin
   ```
4. Start the admin development server:
   ```bash
   npm run dev
   ```
   *(Vite will serve the panel locally, e.g. on `http://localhost:8080` or `http://localhost:8084`).*

---

### Step 5: Configure & Run the Storefront (Frontend)
1. Open another terminal and navigate to the storefront directory:
   ```bash
   cd Frontend
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Create a `.env` file in the `Frontend/` directory:
   ```ini
   # Points the storefront to the public backend API served by Apache
   VITE_API_URL=http://localhost/all-stage/All-stage/backend/public/api
   ```
4. Start the storefront development server:
   ```bash
   npm run dev
   ```

---

## Default Login Credentials

Use these credentials to log in to the Admin Panel:
* **Default Admin Email**: `admin@allstage.com` (Password: `admin123`)
* **Verified Super Admin Email**: `abhisheksxna.15@gmail.com` (Password: `Abhishek@12121`)

### Changing Admin Credentials & Password Recovery

#### Option A: Prior to importing (in the SQL dump file)
If you want to customize your email and password *before* importing the database into phpMyAdmin:
1. Open the [allstage_complete.sql](file:///c:/xampp/htdocs/all-stage/All-stage/backend/database/allstage_complete.sql) file.
2. Locate the line inserting the default admin user (around line 148):
   ```sql
   INSERT INTO `admins` VALUES (3,'Abhishek Saxena','your.email@example.com','...',...);
   ```
3. Change `'abhisheksxna.15@gmail.com'` to your desired email.
4. Replace the bcrypt password hash (the 4th value) with a new hash generated via the PHP command below.
5. Save the file and import it into phpMyAdmin.

#### Option B: Updating the database directly (post-import)
If you have already imported the database and want to manually update details:
1. Generate a secure bcrypt password hash by running:
   ```bash
   php -r "echo password_hash('YourNewPasswordHere', PASSWORD_BCRYPT);"
   ```
2. Update the `admins` table:
   ```sql
   UPDATE admins 
   SET email = 'your.new.email@gmail.com', 
       password = 'PASTE_THE_GENERATED_HASH_HERE' 
   WHERE id = 3;  -- Replace with admin ID
   ```

You can also reset a forgotten password directly in the application using the **Forgot Password** link on the login screen. It will generate a 6-digit verification code, which is written to your inbox (or logged locally to `backend/logs/mail.log` if SMTP is disabled/restricted).

### Troubleshooting SMTP & Email Verification (OTP)

If you change the email configuration and stop receiving OTP emails:
1. **Gmail SMTP Authentication**: The mailer is configured in your [backend/.env](file:///c:/xampp/htdocs/all-stage/All-stage/backend/.env) file:
   ```ini
   MAIL_USERNAME=your.email@gmail.com
   MAIL_PASSWORD=your_google_app_password
   MAIL_FROM=your.email@gmail.com
   ```
   If you change `MAIL_USERNAME` to your own email address, you **must** supply a 16-character **Google App Password** in the `MAIL_PASSWORD` field. Standard Gmail passwords will result in a connection/authentication rejection.
2. **Generating a Google App Password**:
   * Navigate to [Google Account Security](https://myaccount.google.com/security).
   * Ensure **2-Step Verification** is enabled.
   * Search for **App passwords** in the top search bar.
   * Generate an app password (e.g., name it "AllStage Dev") and copy the 16-character code.
   * Paste it into `MAIL_PASSWORD` in your `backend/.env` file.
3. **Local Dev Failsafe Log**:
   If SMTP is failing or you do not have internet access, the backend automatically intercepts the delivery and logs the OTP content directly to [backend/logs/mail.log](file:///c:/xampp/htdocs/all-stage/All-stage/backend/logs/mail.log). You can open this log file to copy the OTP instantly for local developer testing!
4. **Sender vs. Recipient Emails**:
   The configurations in [backend/.env](file:///c:/xampp/htdocs/all-stage/All-stage/backend/.env) (like `MAIL_USERNAME` and `MAIL_PASSWORD`) represent the **sender credentials** (the mail carrier). You do **not** need to change `.env` to log in with a different admin or customer email. The system will send the OTP to whatever email address you enter in the website's login form, as long as the `.env` sender credentials are valid.

---

## Architectural Highlights

### 1. Kafka Event-Driven Fallback
The project implements a decoupling architecture via event topics. When deploying to production, events can publish to a Kafka broker REST proxy (`KAFKA_REST_URL`).
* **Local Development**: If no `KAFKA_REST_URL` is set, the system prints `[events:local]` and runs in-process in-memory queues using local consumer dispatchers. You do **not** need Kafka installed locally to test workflows.

### 2. Live Page View Tracking & Dynamic Dashboard
* Storefront routes track visits using TanStack's `useLocation()` hook.
* Real-time metrics (Total/Pending/Completed orders, revenue, customer totals, and unique visitors) are queried dynamically from MySQL tables to feed the admin dashboard stats and chart trends.
