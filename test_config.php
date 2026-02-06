<?php
// test_config.php
echo "<h1>Testing Configuration</h1>";

// Test 1: Include config.php
echo "<h2>Test 1: Include config.php</h2>";
if (@include 'config.php') {
    echo "✓ config.php included successfully<br>";
    
    // Test 2: Check database connection
    echo "<h2>Test 2: Database Connection</h2>";
    if (isset($koneksi) && $koneksi) {
        echo "✓ Database connection established<br>";
        
        // Test 3: Check if function exists
        echo "<h2>Test 3: Function Check</h2>";
        if (function_exists('getBerita')) {
            echo "✓ Function getBerita() exists<br>";
            
            // Test 4: Test the function
            echo "<h2>Test 4: Test getBerita() function</h2>";
            $test_result = getBerita($koneksi, 2);
            if (is_array($test_result)) {
                echo "✓ Function returned array with " . count($test_result) . " items<br>";
                
                if (count($test_result) > 0) {
                    echo "<h3>Sample Data:</h3>";
                    echo "<pre>";
                    print_r($test_result[0]);
                    echo "</pre>";
                }
            } else {
                echo "✗ Function did not return array<br>";
            }
        } else {
            echo "✗ Function getBerita() does not exist<br>";
        }
        
        // Close connection
        mysqli_close($koneksi);
        
    } else {
        echo "✗ Database connection failed<br>";
        echo "Error: " . mysqli_connect_error() . "<br>";
    }
} else {
    echo "✗ Failed to include config.php<br>";
}

// Test 5: Check PHP version and extensions
echo "<h2>Test 5: PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not Loaded') . "<br>";

// Test 6: Check database
echo "<h2>Test 6: Database Check</h2>";
$conn = mysqli_connect("localhost", "root", "");
if ($conn) {
    // Check if database exists
    $result = mysqli_query($conn, "SHOW DATABASES LIKE 'portal_berita'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✓ Database 'portal_berita' exists<br>";
        
        // Check tables
        mysqli_select_db($conn, "portal_berita");
        $tables = mysqli_query($conn, "SHOW TABLES");
        echo "✓ Tables in database:<br>";
        while ($table = mysqli_fetch_array($tables)) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;✓ " . $table[0] . "<br>";
        }
    } else {
        echo "✗ Database 'portal_berita' does not exist<br>";
        echo '<a href="setup_database.php">Setup database now</a>';
    }
    mysqli_close($conn);
} else {
    echo "✗ Cannot connect to MySQL server<br>";
}
?>