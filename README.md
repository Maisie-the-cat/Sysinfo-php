# System Information Dashboard

A lightweight, modern PHP-based web dashboard for displaying real-time system information. This project provides a clean, responsive interface to monitor CPU, memory, disk, network, and OS information on Linux systems.

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)
![Language: PHP](https://img.shields.io/badge/Language-PHP-blue.svg)

## Features

- **System Overview**: Display OS, kernel version, hostname, and system uptime
- **CPU Monitoring**: Shows CPU core count, model, and load averages (1m, 5m, 15m)
- **Memory Usage**: Displays total, used, and available memory with visual progress bar and doughnut chart
- **Disk Usage**: Shows disk space utilization with progress indicator and chart visualization
- **Network Interfaces**: Lists active network interfaces with received and transmitted bytes
- **Load Visualization**: Interactive charts powered by Chart.js for CPU load and memory/disk usage
- **Theme Toggle**: Light and dark mode support with persistent session storage
- **Responsive Design**: Mobile-friendly layout that adapts to all screen sizes
- **Real-time Data**: System information is dynamically fetched from `/proc` filesystem on each page load

## Requirements

- PHP 7.0 or higher
- Linux/Unix-based operating system
- Web server (Apache, Nginx, etc.)
- Read permissions to `/proc/` filesystem for optimal data collection

## Installation

1. **Clone or download this repository**:
   ```bash
   git clone https://github.com/Maisie-the-cat/Sysinfo-php.git
   cd Sysinfo-php
   ```

2. **Set up your web server** to serve the directory (example with Apache):
   ```apache
   <VirtualHost *:80>
       ServerName sysinfo.local
       DocumentRoot /path/to/Sysinfo-php
       <Directory /path/to/Sysinfo-php>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Access the dashboard**:
   Open your web browser and navigate to `http://localhost` (or your configured server address)

## Usage

### Basic Access
Simply navigate to the dashboard URL in your web browser. The system information will be automatically retrieved and displayed.

### Theme Toggle
Click the "Dark Mode" button in the top-right corner to switch between light and dark themes. Your preference is saved in the session.

### URL Parameters
- `?theme=dark` - Load the dashboard with dark theme
- `?theme=light` - Load the dashboard with light theme

Example: `http://localhost/Index.php?theme=dark`

## File Structure

```
Sysinfo-php/
├── Index.php          # Main application file with PHP logic and HTML output
├── README.md          # This file
├── LICENSE            # MIT License
└── assets/
    ├── css/
    │   └── style.css  # Stylesheet (included reference)
    └── js/
        └── script.js  # JavaScript (included reference)
```

## How It Works

### Backend (PHP)

The application uses PHP functions to gather system information:

1. **`getSystemInfo()`** - Main function that collects all system data
   - Reads from `/proc/cpuinfo` for CPU information
   - Reads from `/proc/uptime` for system uptime
   - Reads from `/proc/meminfo` for memory statistics
   - Reads from `/proc/net/dev` for network interface data
   - Reads from `/etc/os-release` for distribution information
   - Uses PHP built-in functions for disk space information

2. **Helper Functions**:
   - `formatUptime()` - Converts seconds to readable format (days, hours, minutes)
   - `formatBytes()` - Converts byte values to human-readable format (B, KB, MB, GB, TB)
   - `parseNetworkInfo()` - Extracts network interface statistics from `/proc/net/dev`

### Frontend

- Responsive grid layout using CSS Grid
- Color-coded status indicators (safe, warning, critical) for resource usage
- Interactive charts using Chart.js library
- Font Awesome icons for visual enhancement
- Session-based theme persistence

## Data Display

### System Overview
- Operating System (Distribution)
- Distribution Version
- Kernel Version
- System Uptime

### CPU Information
- CPU Cores Count
- CPU Model Name
- Load Averages (1, 5, and 15 minute intervals)
- Load visualization chart

### Memory Usage
- Total Memory
- Used Memory
- Available Memory
- Memory Usage Percentage (with color coding)
- Progress bar and doughnut chart visualization

### Disk Usage
- Total Disk Space
- Used Disk Space
- Free Disk Space
- Disk Usage Percentage (with color coding)
- Progress bar and doughnut chart visualization

### Network Interfaces
- Individual interface cards for each active network adapter
- RX (Received) Bytes
- TX (Transmitted) Bytes
- Automatically filters out loopback and virtual interfaces (lo, docker0, virbr0)

## Permissions

The dashboard requires:
- **Read access** to `/proc/cpuinfo`, `/proc/uptime`, `/proc/meminfo`, `/proc/net/dev`
- **Read access** to `/etc/os-release`
- **Execute permissions** on `/` for disk space queries

These are typically available to any user on standard Linux systems.

## Browser Compatibility

- Chrome/Chromium 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance Considerations

- Lightweight and minimal dependencies
- No database required
- Data is fetched on each page load for real-time accuracy
- Static assets cached by browser
- Suitable for monitoring local or remote servers

## Customization

### Styling
Modify `assets/css/style.css` to customize colors, fonts, and layout.

### Chart Options
Edit the chart initialization code in `Index.php` (lines 341-425) to customize chart appearance and behavior.

### Excluded Network Interfaces
Modify the exclusion list in the `parseNetworkInfo()` function (line 112) to filter different interfaces.

## Troubleshooting

### Missing System Information
- **Uptime not showing**: Ensure `/proc/uptime` is readable
- **CPU info missing**: Check `/proc/cpuinfo` accessibility
- **Memory data unavailable**: Verify `/proc/meminfo` permissions
- **No network interfaces**: Confirm `/proc/net/dev` is accessible or running with appropriate permissions

### Dashboard Not Loading
- Verify PHP is properly installed and configured
- Check web server error logs
- Ensure proper file permissions on the directory

### Incomplete Data on Non-Linux Systems
This dashboard is optimized for Linux systems. Some features may not work on other operating systems due to reliance on Linux-specific `/proc` filesystem.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Feel free to submit pull requests or open issues for bugs and feature requests.

## Credits

- Built with PHP
- UI enhanced with Font Awesome icons
- Charts powered by Chart.js

---

**Last Updated**: April 2026
