# Smart Attendance System (PHP + MySQL + OpenCV)

## Features
- Authentication with role selection (Admin / Staff)
- Role-based dashboards
- Student registration and face dataset capture (20 images)
- Face recognition attendance marking via Python OpenCV API
- Attendance table with edit and export
- Reports with filters, monthly pie chart, and low-attendance alerts

## Folder Structure
- `config/` database connection
- `includes/` auth and shared layout
- `admin/` admin pages
- `staff/` staff pages
- `student/` student registration
- `api/` PHP proxy for Python face API
- `scripts/` Python face recognition service
- `dataset/` captured face images
- `database.sql` complete schema and seed data

## Setup
1. Import SQL:
   ```bash
   mysql -u root -p < database.sql
   ```
2. Serve PHP app:
   ```bash
   php -S 0.0.0.0:8000
   ```
3. Install Python dependencies and run face API:
   ```bash
   pip install -r requirements.txt
   python scripts/face_service.py
   ```
4. Open app: `http://localhost:8000`

## Demo Credentials
- Admin: `admin` / `password123`
- Staff: `staff1` / `password123`
