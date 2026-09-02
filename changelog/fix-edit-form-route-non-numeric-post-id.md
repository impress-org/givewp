Significance: patch
Type: fixed

Fix fatal TypeError in EditFormRoute when $_GET['post'] is non-numeric. Adds is_numeric() guard before abs() call so plugins like Contact Form 7 that use hexadecimal post IDs no longer crash the WordPress admin.
