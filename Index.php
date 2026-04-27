<?php
// System Information Dashboard
session_start();

// Set theme preference
if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
}

$theme = $_SESSION['theme'] ?? 'light';

// Get system information
function getSystemInfo() {
    $info = [];
    
    // OS and Kernel
    $info['os'] = php_uname('s');
    $info['kernel'] = php_uname('r');
    $info['hostname'] = gethostname();
    
    // Uptime
    if (file_exists('/proc/uptime')) {
        $uptime = file_get_contents('/proc/uptime');
        $uptime_array = explode(' ', $uptime);
        $uptime_seconds = (int)$uptime_array[0];
        $info['uptime'] = formatUptime($uptime_seconds);
        $info['uptime_seconds'] = $uptime_seconds;
    }
    
    // CPU Information
    if (file_exists('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/processor\s*:\s*(\d+)/', $cpuinfo, $matches);
        $info['cpu_count'] = count($matches[1]) + 1;
        preg_match('/model name\s*:\s*(.+)/', $cpuinfo, $matches);
        $info['cpu_model'] = $matches[1] ?? 'Unknown';
    }
    
    // Load Average
    $load = sys_getloadavg();
    $info['load_average'] = $load;
    
    // Memory Information
    if (file_exists('/proc/meminfo')) {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s*(\d+)/', $meminfo, $matches);
        $info['memory_total'] = (int)$matches[1] * 1024;
        preg_match('/MemAvailable:\s*(\d+)/', $meminfo, $matches);
        $info['memory_available'] = (int)$matches[1] * 1024;
        $info['memory_used'] = $info['memory_total'] - $info['memory_available'];
        $info['memory_usage_percent'] = ($info['memory_used'] / $info['memory_total']) * 100;
    }
    
    // Disk Information
    $disk_total = disk_total_space('/');
    $disk_free = disk_free_space('/');
    $disk_used = $disk_total - $disk_free;
    $info['disk_total'] = $disk_total;
    $info['disk_free'] = $disk_free;
    $info['disk_used'] = $disk_used;
    $info['disk_usage_percent'] = ($disk_used / $disk_total) * 100;
    
    // Network Information
    if (file_exists('/proc/net/dev')) {
        $netinfo = file_get_contents('/proc/net/dev');
        $info['network'] = parseNetworkInfo($netinfo);
    }
    
    // System Release
    if (file_exists('/etc/os-release')) {
        $os_release = parse_ini_file('/etc/os-release');
        $info['distro'] = $os_release['PRETTY_NAME'] ?? 'Unknown';
        $info['distro_version'] = $os_release['VERSION_ID'] ?? 'Unknown';
    }
    
    return $info;
}

function formatUptime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $parts = [];
    if ($days > 0) $parts[] = "$days day" . ($days > 1 ? 's' : '');
    if ($hours > 0) $parts[] = "$hours hour" . ($hours > 1 ? 's' : '');
    if ($minutes > 0) $parts[] = "$minutes minute" . ($minutes > 1 ? 's' : '');
    
    return implode(', ', $parts) ?: 'Less than a minute';
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function parseNetworkInfo($netinfo) {
    $interfaces = [];
    $lines = explode("\n", $netinfo);
    
    foreach ($lines as $line) {
        if (trim($line) && strpos($line, ':') !== false) {
            list($name, $stats) = explode(':', $line, 2);
            $name = trim($name);
            
            // Skip loopback and virtual interfaces for brevity
            if (in_array($name, ['lo', 'docker0', 'virbr0'])) continue;
            
            $stats_array = preg_split('/\s+/', trim($stats));
            if (count($stats_array) >= 10) {
                $interfaces[$name] = [
                    'rx_bytes' => $stats_array[0],
                    'tx_bytes' => $stats_array[8]
                ];
            }
        }
    }
    
    return $interfaces;
}

$system_info = getSystemInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Information Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $theme; ?>-theme">
    <div class="container-fluid">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1><i class="fas fa-server"></i> System Information Dashboard</h1>
                    <p class="hostname"><?php echo htmlspecialchars($system_info['hostname']); ?></p>
                </div>
                <div class="header-right">
                    <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i> Dark Mode
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- System Overview -->
            <section class="section">
                <h2><i class="fas fa-info-circle"></i> System Overview</h2>
                <div class="grid grid-4">
                    <div class="card info-card">
                        <div class="card-label">Operating System</div>
                        <div class="card-value"><?php echo htmlspecialchars($system_info['distro']); ?></div>
                    </div>
                    <div class="card info-card">
                        <div class="card-label">Version</div>
                        <div class="card-value"><?php echo htmlspecialchars($system_info['distro_version']); ?></div>
                    </div>
                    <div class="card info-card">
                        <div class="card-label">Kernel</div>
                        <div class="card-value"><?php echo htmlspecialchars($system_info['kernel']); ?></div>
                    </div>
                    <div class="card info-card">
                        <div class="card-label">Uptime</div>
                        <div class="card-value"><?php echo htmlspecialchars($system_info['uptime']); ?></div>
                    </div>
                </div>
            </section>

            <!-- CPU Information -->
            <section class="section">
                <h2><i class="fas fa-microchip"></i> CPU Information</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-header">CPU Details</div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="label">CPU Cores:</span>
                                <span class="value"><?php echo $system_info['cpu_count']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">CPU Model:</span>
                                <span class="value"><?php echo htmlspecialchars($system_info['cpu_model']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Load Average (1m):</span>
                                <span class="value"><?php echo number_format($system_info['load_average'][0], 2); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Load Average (5m):</span>
                                <span class="value"><?php echo number_format($system_info['load_average'][1], 2); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Load Average (15m):</span>
                                <span class="value"><?php echo number_format($system_info['load_average'][2], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Load Visualization</div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="loadChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Memory Information -->
            <section class="section">
                <h2><i class="fas fa-memory"></i> Memory Usage</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-header">Memory Details</div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="label">Total Memory:</span>
                                <span class="value"><?php echo formatBytes($system_info['memory_total']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Used Memory:</span>
                                <span class="value"><?php echo formatBytes($system_info['memory_used']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Available Memory:</span>
                                <span class="value"><?php echo formatBytes($system_info['memory_available']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Usage Percentage:</span>
                                <span class="value <?php echo $system_info['memory_usage_percent'] > 80 ? 'critical' : ($system_info['memory_usage_percent'] > 60 ? 'warning' : 'safe'); ?>">
                                    <?php echo number_format($system_info['memory_usage_percent'], 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Memory Usage</div>
                        <div class="card-body">
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?php echo $system_info['memory_usage_percent']; ?>%; background-color: <?php echo $system_info['memory_usage_percent'] > 80 ? '#e74c3c' : ($system_info['memory_usage_percent'] > 60 ? '#f39c12' : '#27ae60'); ?>;">
                                </div>
                            </div>
                            <div class="chart">
                                <canvas id="memoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Disk Information -->
            <section class="section">
                <h2><i class="fas fa-hdd"></i> Disk Usage</h2>
                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-header">Disk Details</div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="label">Total Disk Space:</span>
                                <span class="value"><?php echo formatBytes($system_info['disk_total']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Used Disk Space:</span>
                                <span class="value"><?php echo formatBytes($system_info['disk_used']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Free Disk Space:</span>
                                <span class="value"><?php echo formatBytes($system_info['disk_free']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Usage Percentage:</span>
                                <span class="value <?php echo $system_info['disk_usage_percent'] > 80 ? 'critical' : ($system_info['disk_usage_percent'] > 60 ? 'warning' : 'safe'); ?>">
                                    <?php echo number_format($system_info['disk_usage_percent'], 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Disk Usage</div>
                        <div class="card-body">
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?php echo $system_info['disk_usage_percent']; ?>%; background-color: <?php echo $system_info['disk_usage_percent'] > 80 ? '#e74c3c' : ($system_info['disk_usage_percent'] > 60 ? '#f39c12' : '#27ae60'); ?>;">
                                </div>
                            </div>
                            <div class="chart">
                                <canvas id="diskChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Network Information -->
            <section class="section">
                <h2><i class="fas fa-network-wired"></i> Network Interfaces</h2>
                <div class="grid grid-auto">
                    <?php if (!empty($system_info['network'])): ?>
                        <?php foreach ($system_info['network'] as $interface => $stats): ?>
                            <div class="card">
                                <div class="card-header"><?php echo htmlspecialchars($interface); ?></div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="label">RX Bytes:</span>
                                        <span class="value"><?php echo formatBytes($stats['rx_bytes']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">TX Bytes:</span>
                                        <span class="value"><?php echo formatBytes($stats['tx_bytes']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card">
                            <p>No network interfaces found or permission denied.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Last Updated -->
            <section class="section footer-section">
                <p class="last-updated">Last updated: <span id="last-updated"><?php echo date('Y-m-d H:i:s'); ?></span></p>
            </section>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Load chart data
        const loadData = {
            labels: ['1m', '5m', '15m'],
            datasets: [{
                label: 'Load Average',
                data: [<?php echo implode(',', $system_info['load_average']); ?>],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }]
        };

        const memoryData = {
            labels: ['Used', 'Available'],
            datasets: [{
                data: [<?php echo $system_info['memory_used']; ?>, <?php echo $system_info['memory_available']; ?>],
                backgroundColor: ['#e74c3c', '#27ae60']
            }]
        };

        const diskData = {
            labels: ['Used', 'Free'],
            datasets: [{
                data: [<?php echo $system_info['disk_used']; ?>, <?php echo $system_info['disk_free']; ?>],
                backgroundColor: ['#e67e22', '#2ecc71']
            }]
        };

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            const loadCtx = document.getElementById('loadChart');
            const memoryCtx = document.getElementById('memoryChart');
            const diskCtx = document.getElementById('diskChart');

            if (loadCtx) {
                new Chart(loadCtx, {
                    type: 'line',
                    data: loadData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            if (memoryCtx) {
                new Chart(memoryCtx, {
                    type: 'doughnut',
                    data: memoryData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    }
                });
            }

            if (diskCtx) {
                new Chart(diskCtx, {
                    type: 'doughnut',
                    data: diskData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
