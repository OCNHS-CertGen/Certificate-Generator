# OCNHS Certificate Generator (CertGen) - User Manual

Welcome to the **OCNHS Certificate Generator (CertGen)** user manual. This guide is written in simple English to help you understand how to use the system. 

---

## 📋 Table of Contents
1. [What is CertGen?](#1-what-is-certgen)
2. [User Roles (Who Can Use What?)](#2-user-roles-who-can-use-what)
3. [For Students and Alumni: Requesting Certificates Online](#3-for-students-and-alumni-requesting-certificates-online)
4. [For School Staff: Managing Student Requests](#4-for-school-staff-managing-student-requests)
5. [For School Staff: Generating Certificates Manually](#5-for-school-staff-generating-certificates-manually)
6. [For School Staff: Viewing History and Reports](#6-for-school-staff-viewing-history-and-reports)
7. [For School Staff: Changing Settings and Backups](#7-for-school-staff-changing-settings-and-backups)
8. [For Super Administrators: Managing Accounts and Templates](#8-for-super-administrators-managing-accounts-and-templates)

---

## 1. What is CertGen?
**CertGen** is a web-based system for **Olongapo City National High School (OCNHS)**. It does two main things:
1. It allows students and alumni (graduates) to request school certificates online.
2. It helps school registrars and administrators verify requests, generate official certificates, print them, and keep a history of issued documents.

---

## 2. User Roles (Who Can Use What?)

The system has three types of users:

1. **Students / Alumni (Public Users):**
   * Do not need a login password.
   * Can fill out request forms online.
   * Can track the status of their requested documents.

2. **Admins (School Staff / Registrar):**
   * Need a username and password to log in.
   * Can see and process online requests.
   * Can create and print certificates manually.
   * Can view and download the history of issued certificates.
   * Can change their own profile name and password.

3. **Super Admins (System Administrators):**
   * Have all Admin powers.
   * Can add, edit, or delete Admin accounts.
   * Can edit certificate layout templates.
   * Can backup or restore system data.

---

## 3. For Students and Alumni: Requesting Certificates Online

If you are a student or alumni and need a document (like a Graduation Certificate or Form 137), follow these steps:

### How to Submit a Request
1. **Go to the portal homepage.** You will see two boxes: **Online Request** and **Admin Login**.
2. Click **Request Now** under **Online Request**.
3. **Step 1: Personal Information**
   * Fill in your Full Name. Make sure it matches your birth certificate.
   * Enter your active email address and active contact number.
   * Enter your home address, date of birth, and place of birth.
   * Click **Next Step**.
4. **Step 2: Academic Details**
   * Select the document you want to request.
   * Enter your Grade Level and the School Year you graduated or attended.
   * Click **Next Step**.
5. **Step 3: Requirements & Purpose**
   * Upload a clear photo of your **Valid ID** (front side).
   * Upload a **Selfie photo** of yourself.
   * Type the reason/purpose of your request (for example: "For college admission" or "For job application").
   * Click **Submit Request**.
6. **Save your Reference Number.** The screen will show a code like `OCNHS-2026-XXXX`. Copy this code. You will need it to track your request.

### How to Track Your Request
1. Open the **Online Request** page.
2. Click the **Track My Request** button at the top.
3. Paste your **Reference Number** and click **Check Status**.
4. You will see one of these statuses:
   * 🟡 **Pending:** Your request is received and waiting for the registrar to check.
   * 🔵 **Processing:** Your document (usually Form 137 / SF10) is being prepared.
   * 🟢 **Ready for Pickup:** Your document is done! You can go to the school's EMIS Office to pick it up.
   * 🔴 **Rejected:** Your request was denied. A reason will be displayed (for example: "ID photo was blurry").
   * ⚫ **Released:** You have already picked up the document.

---

## 4. For School Staff: Managing Student Requests

Admins can manage requests submitted by students. 

### Checking Incoming Requests
1. Log into your Admin account.
2. Go to the dashboard. If there are new student requests, you will hear a notification sound and see a notification alert.
3. Click the **Online Requests** button at the top left of the dashboard.
4. You will see a list of requests. You can click the status tabs (Pending, Processing, Ready, Released, Rejected) to filter the list.

### Verifying and Approving a Request
1. Find a request with a yellow **Pending** status.
2. Click the **Verify Request** button on that row.
3. A modal box will pop up showing:
   * The student's filled details (Name, LRN, Grade, School Year, etc.).
   * The uploaded ID photo and Selfie. Click on any photo to zoom in.
4. **Decide what action to take:**
   * **If everything is correct:** Click **Verify & Approve** (or **Approve & Start Processing** for Form 137). 
     * The student will receive an automatic email telling them their request is approved or ready.
   * **If something is wrong (e.g., wrong ID, mismatch name):** Click **Reject Request**. Type a clear reason. 
     * The student will receive an automatic email explaining why they were rejected.

### Releasing the Document
1. When the student arrives at the office to pick up their certificate:
   * Go to **Online Requests** and click the **Ready for Pickup** tab.
   * Find the student's name and click **Release Document**.
   * The system will update the status to **Released** and record the transaction in the history log.

---

## 5. For School Staff: Generating Certificates Manually

Sometimes you need to print a certificate for a walk-in student without an online request. You can generate certificates directly.

### Step-by-Step Generation
1. Go to the Admin **Dashboard**.
2. Click on the card for the certificate you want to create (e.g., **Enrollment**, **Good Moral Character**, **Graduation**, etc.).
3. Fill out the student form:
   * Select the certificate type.
   * Enter the student's full name, LRN (12 digits), grade, section, curriculum, school year, and purpose.
   * *Note: The form fields automatically change depending on the certificate you select. For example, "Reconstructed Diploma" will ask for the original principal's name and superintendent's name.*
4. Enter the **Principal's Name** and **Title** (defaults are already filled).
5. Click **Generate Certificate** at the bottom.
6. A preview of the certificate will open. Check it carefully.
7. Click the **Print Certificate** button at the top toolbar to print it or save it as a PDF.
8. **Crucial Step:** After printing, a message box will ask: *"Was the certificate successfully printed or saved?"*
   * Click **OK**. This saves a copy of this issuance to the system history database.
   * You will see colorful confetti on your screen to celebrate!

---

## 6. For School Staff: Viewing History and Reports

The system tracks every certificate printed. This makes report-making very easy.

### Using the History Page
1. Click the **Issuance History** button on the dashboard top navigation bar.
2. You will see a **Bar Chart** at the top showing which certificates are requested the most.
3. You can see a list of all issued certificates at the bottom.

### Filtering and Exporting Records
1. Use the filter section to find specific records:
   * **From Date / To Date:** Show certificates issued within a certain date range.
   * **Certificate Type:** Show only one type of document.
   * Click **Filter** to search, or **Clear** to reset.
2. Click **Download CSV** to save the records as an Excel-compatible spreadsheet. The downloaded file will include a summary statistic report at the top followed by the raw data list.

---

## 7. For School Staff: Changing Settings and Backups

Admins can manage their account credentials and create backups.

### Account Settings
1. Click **Settings** in the top navigation menu.
2. Select **Account Settings**.
3. You can change your **Display Name**.
4. You can change your **Password** by entering your current password and typing a new one twice.
5. Click **Update Credentials** to save.

### System Backup (To prevent data loss)
1. Go to **Settings** and click **System Backup**.
2. **Export Database:** Click **Export DB (.SQL)**. This downloads all your history and student requests.
3. **Export Project Files:** Click **Export Files (.ZIP)**. This downloads a backup of the website files and uploaded images.
4. **Import Database:** If you need to restore data from a previous backup:
   * Select your backup `.sql` file under the "Import Data" section.
   * Click **Start Database Import**.
   * *Warning: This will overwrite your current database. Make sure you back up your current status first!*

---

## 8. For Super Administrators: Managing Accounts and Templates

Super Admins have access to the **Admin Options** dropdown menu at the top bar.

### Admin Management (Managing Staff Accounts)
1. Click **Admin Options** > **Admin Management**.
2. You will see a list of all registered admin accounts.
3. **To add a new staff member:**
   * Click **Create New Admin**.
   * Type their Full Name, Username, and Password.
   * Click **Create Admin**.
4. **To update a staff member:**
   * Click **Edit Profile** on their row to change their name or set a new password.
5. **To disable/suspend an account:**
   * Click **Disable**. The staff member will no longer be able to log in. You can click **Enable** to restore their access later.

### Template Management (Changing layouts and text formats)
1. Click **Admin Options** > **Templates**.
2. Select a certificate template from the left sidebar.
3. You will see the **Certificate Title** and **Certificate Body** editor fields. 
4. The templates use HTML code for styling. You can edit the text directly.
5. **Using Placeholders:** The templates use special placeholders in brackets. The system replaces these placeholders with real student details when printing.
   * `[sn]` = Student Name
   * `[lrn]` = Learner Reference Number
   * `[grade]` = Grade Level
   * `[section]` = Section or Strand
   * `[sy]` = School Year
   * `[purpose]` = Purpose of Certificate
   * `[school_name]` = Olongapo City National High School
6. Click **Save Changes** when done.
7. Click **Disable** at the top right to temporarily prevent students from requesting this certificate type online.
8. If you make a mistake, click **Restore Default** to go back to the original layout.
