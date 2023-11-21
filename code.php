<?php
// Informasi koneksi ke database MySQL
$host = "localhost";
$username = "root"; 
$password = ""; 
$database = "quizz"; 

// Membuat koneksi ke database MySQL
$koneksi = mysqli_connect($host, $username, $password, $database);

// Memeriksa koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Mengatur filter bulan default ke "Semua"
$selectedMonth = isset($_GET['bulan']) ? $_GET['bulan'] : 'Semua';

// Query yang akan dijalankan, tetapi akan ditambahkan kondisi WHERE jika bulan tidak sama dengan 'Semua'
$query = "SELECT * FROM data_quizz";
if ($selectedMonth !== 'Semua') {
    $query = "SELECT * FROM data_quizz WHERE Bulan = ?";
}

// Membuat prepared statement jika bulan tidak sama dengan 'Semua'
if ($selectedMonth !== 'Semua') {
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $selectedMonth);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // Jika bulan sama dengan 'Semua', jalankan query tanpa prepared statement
    $result = mysqli_query($koneksi, $query);
}

$dataPoints = []; // Array untuk menyimpan data dari database
while ($row = mysqli_fetch_assoc($result)) {
    // Sesuaikan ini dengan struktur kolom dalam tabel Anda
    $dataPoints[] = [
        "Bulan" => $row['Bulan'],
        "Ekspor" => $row['Nilai Ekspor (US $)'],
        "Impor" => $row['Nilai Impor (US $)'],
        "Berat_Ekspor" => $row['Berat Ekspor (KG)'],
        "berat_Impor" => $row['Berat Impor (KG)']
    ];
}
?>


<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #274c7f, #f0f8ff); /* Warna biru muda */
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2px;
        }

        header {
            background-color: #FFA500;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-bottom: 2px; /* Jarak dari header ke summary-box */
        }

        .summary-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2px; /* Jarak dari summary-box ke grafik */
        }

        .summary-box {
            width: 180px;
            height: 120px;
            margin: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #fff; /* Warna latar belakang putih */
        }

        .plot {
            width: 800px; /* Persempit lebar grafik */
            height: 380px; /* Persempit tinggi grafik */
            margin: 3px;
            border: 2px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .small-plot {
            width: 380px; /* Persempit lebar small-plot */
            height: 280px; /* Persempit tinggi small-plot */
            margin: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <header>
        <h1>Dashboard Ekspor Impor Tahun 2023</h1>
        <h2 style="color:black;">Bayu Kurniawan / 3322600019 (2 D4 SDT)</h2>
        <form action="" method="GET">
            <label for="bulan">Pilih Bulan:</label>
            <select name="bulan" id="bulan">
                <option value="Semua" <?php if ($selectedMonth == 'Semua') echo 'selected'; ?>>Semua</option>
                <option value="Januari" <?php if ($selectedMonth == 'Januari') echo 'selected'; ?>>Januari</option>
                <option value="Februari" <?php if ($selectedMonth == 'Februari') echo 'selected'; ?>>Februari</option>
                <option value="Maret" <?php if ($selectedMonth == 'Maret') echo 'selected'; ?>>Maret</option>
                <option value="April" <?php if ($selectedMonth == 'April') echo 'selected'; ?>>April</option>
                <option value="Mei" <?php if ($selectedMonth == 'Mei') echo 'selected'; ?>>Mei</option>
                <option value="Juni" <?php if ($selectedMonth == 'Juni') echo 'selected'; ?>>Juni</option>
                <option value="Juli" <?php if ($selectedMonth == 'Juli') echo 'selected'; ?>>Juli</option>
                <option value="Agustus" <?php if ($selectedMonth == 'Agustus') echo 'selected'; ?>>Agustus</option>
                <option value="September" <?php if ($selectedMonth == 'September') echo 'selected'; ?>>September</option>
                <option value="Oktober" <?php if ($selectedMonth == 'Oktober') echo 'selected'; ?>>Oktober</option>
                <option value="November" <?php if ($selectedMonth == 'November') echo 'selected'; ?>>November</option>
                <option value="Desember" <?php if ($selectedMonth == 'Desember') echo 'selected'; ?>>Desember</option>
            </select>
            <input type="submit" value="Tampilkan">
        </form>
    </header>

    <div class="container">
        <div class="summary-container">
            <div class="summary-box" id="eksporTotal">
                <h3>Jumlah Nilai Ekspor</h3>
                <p id="eksporAmount">0</p>
            </div>
            <div class="summary-box" id="beratEksporTotal">
                <h3>Jumlah Berat Ekspor</h3>
                <p id="beratEksporAmount">0</p>
            </div>
            <div class="summary-box" id="imporTotal">
                <h3>Jumlah Nilai Impor</h3>
                <p id="imporAmount">0</p>
            </div>
            <div class="summary-box" id="beratImporTotal">
                <h3>Jumlah Berat Impor</h3>
                <p id="beratImporAmount">0</p>
            </div>
        </div>

        <div class="plot" id="myPlot1"></div>

        <div class="summary-container">
            <div class="small-plot" id="myPlot2"></div>
            <div class="small-plot" id="myPlot3"></div>
        </div>
    </div>

    <script>
        var dataPoints = <?php echo json_encode($dataPoints); ?>;
        
        // Plot Grafik 1
        function updatePlot(selectedMonth) {
            var filteredData = dataPoints.filter(point => point.Bulan === selectedMonth || selectedMonth === 'Semua');

            var trace1 = {
                x: filteredData.map(point => point.Bulan),
                y: filteredData.map(point => point.berat_Impor),
                mode: 'lines+markers', // Menambahkan titik pada grafik
                connectgaps: true,
                name: 'Berat Impor (KG)' // Label untuk grafik ini
            };

            var trace2 = {
                x: filteredData.map(point => point.Bulan),
                y: filteredData.map(point => point.Berat_Ekspor),
                mode: 'lines+markers', // Menambahkan titik pada grafik
                connectgaps: true,
                name: 'Berat Ekspor (KG)' // Label untuk grafik ini
            };

            var trace3 = {
                x: filteredData.map(point => point.Bulan),
                y: filteredData.map(point => point.Impor),
                mode: 'lines+markers', // Menambahkan titik pada grafik
                connectgaps: true,
                name: 'Nilai Impor (US $)' // Label untuk grafik ini
            };

            var trace4 = {
                x: filteredData.map(point => point.Bulan),
                y: filteredData.map(point => point.Ekspor),
                mode: 'lines+markers', // Menambahkan titik pada grafik
                connectgaps: true,
                name: 'Nilai Ekspor (US $)' // Label untuk grafik ini
            };

            var data1 = [trace1, trace2, trace3, trace4];

            var layout1 = {
                title: 'Line Chart Ekspot Import 2023',
                legend: { x: 1, y: 1 }, // Menentukan posisi legenda di sebelah kanan atas
                showlegend: true
            };

            Plotly.newPlot('myPlot1', data1, layout1);

            // Plot Grafik ke-2
            var data2 = [
                {
                    x: filteredData.map(point => point.Bulan),
                    y: filteredData.map(point => point.Ekspor),
                    type: 'scatter'
                }
            ];
            var layout2 = {
                title: 'Time Series Ekspor',
                showlegend: false
            };
            Plotly.newPlot('myPlot2', data2, layout2);

            // Plot Grafik ke-3
            var data3 = [
                {
                    x: filteredData.map(point => point.Bulan),
                    y: filteredData.map(point => point.Impor),
                    type: 'scatter'
                }
            ];
            var layout3 = {
                title: 'Time Series Impor',
                showlegend: false
            };
            Plotly.newPlot('myPlot3', data3, layout3);
        }

        // Initial plot render
        updatePlot('<?php echo $selectedMonth; ?>');

        // Function to handle form submission
        document.querySelector('form').addEventListener('submit', function (event) {
            event.preventDefault();
            var selectedMonth = document.getElementById('bulan').value;
            updatePlot(selectedMonth);
        });

        // Logika untuk menghitung jumlah masing-masing data dan menampilkannya di kotak
        var eksporTotal = dataPoints.reduce((total, point) => total + parseFloat(point.Ekspor), 0);
        document.getElementById('eksporAmount').innerText = eksporTotal.toLocaleString();

        var imporTotal = dataPoints.reduce((total, point) => total + parseFloat(point.Impor), 0);
        document.getElementById('imporAmount').innerText = imporTotal.toLocaleString();

        var beratEksporTotal = dataPoints.reduce((total, point) => total + parseFloat(point.Berat_Ekspor), 0);
        document.getElementById('beratEksporAmount').innerText = beratEksporTotal.toLocaleString();

        var beratImporTotal = dataPoints.reduce((total, point) => total + parseFloat(point.berat_Impor), 0);
        document.getElementById('beratImporAmount').innerText = beratImporTotal.toLocaleString();
    </script>

    <?php
    // Tutup koneksi setelah selesai bekerja dengan database
    mysqli_close($koneksi);
    ?>
</body>
</html>
