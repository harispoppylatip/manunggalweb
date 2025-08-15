<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Debug - Sensor Data</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .null { color: #999; font-style: italic; }
        .zero { color: #e74c3c; font-weight: bold; }
        .normal { color: #27ae60; }
        .refresh { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 10px 0; }
        .stats { background: #ecf0f1; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
    <script>
        function refreshPage() { location.reload(); }
        setInterval(refreshPage, 10000); // Auto refresh every 10 seconds
    </script>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Debug - Sensor Data</h1>
        <button class="refresh" onclick="refreshPage()">🔄 Refresh Now</button>
        <p><em>Auto-refresh every 10 seconds</em></p>

<?php
try {
    $mysqli = getDbConnection();
    
    echo "<div style='color: green;'>✅ Connected to database: " . $GLOBALS['DB_CONFIG']['name'] . "</div>";
    
    // Get latest 20 records
    echo "<h2>📊 Latest 20 Sensor Records</h2>";
    $result = $mysqli->query("
        SELECT id, ts, suhu, kelembapan_tanah, ph, relay 
        FROM sensor_realtime 
        ORDER BY ts DESC 
        LIMIT 20
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Timestamp</th><th>Suhu (°C)</th><th>Kelembapan Tanah (%)</th><th>pH</th><th>Relay</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['ts'] . "</td>";
            
            // Temperature
            if ($row['suhu'] === null) {
                echo "<td class='null'>NULL</td>";
            } else {
                echo "<td class='normal'>" . $row['suhu'] . "</td>";
            }
            
            // Humidity
            if ($row['kelembapan_tanah'] === null) {
                echo "<td class='null'>NULL</td>";
            } elseif ($row['kelembapan_tanah'] == 0) {
                echo "<td class='zero'>0 ⚠️</td>";
            } else {
                echo "<td class='normal'>" . $row['kelembapan_tanah'] . "</td>";
            }
            
            // pH
            if ($row['ph'] === null) {
                echo "<td class='null'>NULL</td>";
            } else {
                echo "<td class='normal'>" . $row['ph'] . "</td>";
            }
            
            // Relay
            echo "<td>" . ($row['relay'] ? 'ON' : 'OFF') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: orange;'>⚠️ No data found in sensor_realtime table</div>";
    }
    
    // Statistics
    echo "<h2>📈 Data Statistics (Last 24 Hours)</h2>";
    $stats = $mysqli->query("
        SELECT 
            COUNT(*) as total_records,
            COUNT(suhu) as suhu_count,
            COUNT(kelembapan_tanah) as humidity_count,
            COUNT(ph) as ph_count,
            AVG(suhu) as avg_suhu,
            AVG(kelembapan_tanah) as avg_humidity,
            AVG(ph) as avg_ph,
            MIN(kelembapan_tanah) as min_humidity,
            MAX(kelembapan_tanah) as max_humidity,
            SUM(CASE WHEN kelembapan_tanah = 0 THEN 1 ELSE 0 END) as zero_humidity_count,
            SUM(CASE WHEN kelembapan_tanah IS NULL THEN 1 ELSE 0 END) as null_humidity_count
        FROM sensor_realtime 
        WHERE ts >= (NOW() - INTERVAL 24 HOUR)
    ");
    
    if ($stats && $row = $stats->fetch_assoc()) {
        echo "<div class='stats'>";
        echo "<strong>📊 Statistics (Last 24h):</strong><br>";
        echo "• Total Records: " . $row['total_records'] . "<br>";
        echo "• Records with Temperature: " . $row['suhu_count'] . "<br>";
        echo "• Records with Humidity: " . $row['humidity_count'] . "<br>";
        echo "• Records with pH: " . $row['ph_count'] . "<br>";
        echo "• Average Temperature: " . ($row['avg_suhu'] ? round($row['avg_suhu'], 2) . "°C" : "N/A") . "<br>";
        echo "• Average Humidity: " . ($row['avg_humidity'] ? round($row['avg_humidity'], 2) . "%" : "N/A") . "<br>";
        echo "• Average pH: " . ($row['avg_ph'] ? round($row['avg_ph'], 2) : "N/A") . "<br>";
        echo "• Min Humidity: " . ($row['min_humidity'] !== null ? $row['min_humidity'] . "%" : "N/A") . "<br>";
        echo "• Max Humidity: " . ($row['max_humidity'] !== null ? $row['max_humidity'] . "%" : "N/A") . "<br>";
        echo "<span style='color: red;'>• Zero Humidity Records: " . $row['zero_humidity_count'] . " ⚠️</span><br>";
        echo "<span style='color: orange;'>• NULL Humidity Records: " . $row['null_humidity_count'] . "</span><br>";
        echo "</div>";
    }
    
    // Show table structure
    echo "<h2>🏗️ Table Structure</h2>";
    $structure = $mysqli->query("DESCRIBE sensor_realtime");
    if ($structure) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

        <p><em>🕒 Last updated: <?php echo date('Y-m-d H:i:s'); ?></em></p>
    </div>
</body>
</html>
