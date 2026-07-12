# Security Notes

This application now includes:

- Prepared statements for database queries to help prevent SQL injection.
- Server-side validation for registration, login, post creation, and post editing.
- Password hashing with PHP password_hash().
- Session-based authentication.
- Role-based access control with roles such as editor and admin.
