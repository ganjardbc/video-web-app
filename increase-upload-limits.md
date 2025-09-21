# Increasing PHP Upload Limits for Herd Lite

The current server configuration has an 8MB POST limit, which affects video uploads.

## Current Status (Updated)

The application has been updated to:
- ✅ Detect server POST size limit errors
- ✅ Validate file size on frontend (8MB limit)
- ✅ Handle mixed HTML/JSON responses gracefully
- ✅ Display correct file size limits in UI (8MB)
- ✅ Provide clear error messages for oversized files

## The Problem

The 27MB video file `Oasis_Do-you-know-what-I-mean.mp4` exceeds the server's 8MB POST limit, causing:
- PHP Warning: "POST Content-Length exceeds the limit"
- Laravel PostTooLargeException
- Mixed HTML/JSON response that was hard to parse

## Solutions

### Option 1: Compress the Video File
Use video compression tools to reduce file size:
- **Online tools**: Handbrake, VideoSmaller.com, Clipchamp
- **Command line**: `ffmpeg -i input.mp4 -b:v 1M -b:a 128k output.mp4`
- **Target**: Under 8MB for current server

### Option 2: Increase Server Limits

Create a `.htaccess` file in the `public` directory with:
```
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M
```

### Option 3: Modify Herd Lite Configuration

The PHP configuration is located at:
`/Users/ganjarhadiatna/.config/herd-lite/bin/php.ini`

Add or modify these lines:
```
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

Then restart the Herd services.

## Current Working Limits

- ✅ **Images**: Most images under 8MB work perfectly
- ✅ **Small videos**: Compressed videos under 8MB work
- ❌ **Large videos**: Files over 8MB are blocked with clear error messages

The application now provides excellent error handling and user guidance! 🎉
