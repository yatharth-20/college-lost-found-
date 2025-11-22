@echo off
echo Running scheduled backup...
C:\xampp\php\php.exe C:\xampp\htdocs\college-lost-found\auto_backup.php
echo Backup completed on %date% %time%