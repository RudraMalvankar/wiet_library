# WIET Student Mobile App (React Native)

This app is a full student mobile client for the WIET Library portal.

## Features

- Login + token session
- Dashboard, My Books, Book Details
- Borrowing History
- Search Books
- Notifications + mark as read
- Profile + Digital ID
- Recommendations
- Library Events
- Footfall check-in / check-out
- E-Resources

## Backend API

The app expects the mobile API created in this repository:

- `student/api/mobile/auth/*`
- `student/api/mobile/resources/*`

## Run

```bash
cd student-mobile-app
npm install
npm start
```

## Base URL

Default base URL is configured in `app.json`:

- `http://10.0.2.2/wiet_lib/student/api/mobile`

For a physical device, set:

- `EXPO_PUBLIC_API_BASE_URL=http://<your-local-ip>/wiet_lib/student/api/mobile`

or edit `app.json` -> `expo.extra.apiBaseUrl`.
