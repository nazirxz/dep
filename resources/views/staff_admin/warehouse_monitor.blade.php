<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Gudang - UD Keluarga Sehati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .monitor-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            color: white;
        }

        .monitor-header {
            background: rgba(0,0,0,0.3);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .monitor-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .warehouse-title {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            margin: 0;
        }

        .datetime-display {
            text-align: right;
            font-size: 1.2rem;
        }

        .warehouse-monitor-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1800px;
            margin: 0 auto;
        }

        .rack-monitor {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease;
        }

        .rack-monitor:hover {
            transform: scale(1.02);
        }

        .rack-monitor-header {
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.3rem;
        }

        .rack-monitor-grid {
            display: grid;
            gap: 3px;
        }

        .rack-monitor-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 3px;
        }

        .rack-monitor-cell {
            aspect-ratio: 1;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            min-height: 40px;
        }

        .rack-monitor-cell.empty {
            background: rgba(255,255,255,0.2);
            border: 1px dashed rgba(255,255,255,0.5);
        }

        .rack-monitor-cell.occupied {
            background: #28a745;
            color: white;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
            border: 1px solid #20c997;
        }

        .rack-monitor-cell.occupied:hover {
            background: #20c997;
            transform: scale(1.1);
            z-index: 10;
        }

        .monitor-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255,255,255,0.15);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            display: block;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .rack-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }

        .legend-color {
            width: 25px;
            height: 25px;
            border-radius: 4px;
        }

        .legend-color.empty {
            background: rgba(255,255,255,0.2);
            border: 1px dashed rgba(255,255,255,0.5);
        }

        .legend-color.occupied {
            background: #28a745;
            border: 1px solid #20c997;
        }

        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @media (max-width: 1400px) {
            .warehouse-monitor-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .warehouse-monitor-grid {
                grid-template-columns: 1fr;
            }
            
            .warehouse-title {
                font-size: 1.8rem;
            }
            
            .monitor-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="monitor-container">
        <div class="monitor-header">
            <div>
                <h1 class="warehouse-title">
                    <i class="fas fa-warehouse"></i>
                    Lokasi Barang di Gudang
                </h1>
                <small style="opacity: 0.8;">UD Keluarga Sehati - Real Time Monitor</small>
            </div>
            <div class="datetime-display">
                <div id="currentTime" class="pulse"></div>
                <div id="currentDate"></div>
            </div>
        </div>

        <div class="monitor-content">
            {{-- Statistics Cards --}}
            <div class="monitor-stats">
                <div class="stat-card">
                    <span class="stat-number" id="totalRacks">{{ 8 * 4 * 6 }}</span>
                    <div class="stat-label">Total Slot</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number text-success" id="occupiedRacks">{{ $incomingItems->whereNotNull('lokasi_rak_barang')->count() }}</span>
                    <div class="stat-label">Slot Terisi</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number text-warning" id="emptyRacks">{{ (8 * 4 * 6) - $incomingItems->whereNotNull('lokasi_rak_barang')->count() }}</span>
                    <div class="stat-label">Slot Kosong</div>
                </div>
            </div>

            {{-- Warehouse Grid --}}
            <div class="warehouse-monitor-grid">
                @for($rak = 1; $rak <= 8; $rak++)
                    <div class="rack-monitor">
                        <div class="rack-monitor-header">
                            Rak {{ $rak }}
                        </div>
                        <div class="rack-monitor-grid">
                            @for($row = 1; $row <= 4; $row++)
                                <div class="rack-monitor-row">
                                    @for($col = 1; $col <= 6; $col++)
                                        @php
                                            $position = "R{$rak}-{$row}-{$col}";
                                            $hasItem = $incomingItems->where('lokasi_rak_barang', $position)->first();
                                        @endphp
                                        <div class="rack-monitor-cell {{ $hasItem ? 'occupied' : 'empty' }}" 
                                             data-position="{{ $position }}"
                                             title="{{ $hasItem ? $hasItem->nama_barang : 'Kosong' }}">
                                            @if($hasItem)
                                                <div class="text-center">
                                                    <div style="font-size: 0.7rem;">{{ substr($hasItem->nama_barang, 0, 6) }}</div>
                                                    <div style="font-size: 0.6rem; opacity: 0.8;">{{ $hasItem->jumlah_barang }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            @endfor
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Legend --}}
            <div class="rack-legend">
                <div class="legend-item">
                    <span class="legend-color empty"></span>
                    <span>Slot Kosong</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color occupied"></span>
                    <span>Slot Terisi</span>
                </div>
                <div class="legend-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Auto Refresh: 30s</span>
                </div>
            </div>
        </div>
    </div>

    <div class="refresh-indicator" id="refreshIndicator">
        <i class="fas fa-sync-alt"></i>
        <span id="refreshTimer">30</span>s
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update time and date
        function updateDateTime() {
            const now = new Date();
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            };
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }

        // Auto refresh countdown
        let refreshCountdown = 30;
        function updateRefreshTimer() {
            document.getElementById('refreshTimer').textContent = refreshCountdown;
            refreshCountdown--;
            
            if (refreshCountdown < 0) {
                location.reload();
            }
        }

        // Initialize
        updateDateTime();
        setInterval(updateDateTime, 1000);
        setInterval(updateRefreshTimer, 1000);

        // Add hover effects for occupied cells
        document.querySelectorAll('.rack-monitor-cell.occupied').forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                const position = this.dataset.position;
                const title = this.getAttribute('title');
                
                // You could show a tooltip or highlight effect here
                this.style.transform = 'scale(1.2)';
                this.style.zIndex = '999';
            });
            
            cell.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.zIndex = '';
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'F5':
                case 'r':
                case 'R':
                    e.preventDefault();
                    location.reload();
                    break;
                case 'Escape':
                    window.close();
                    break;
                case 'F11':
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    } else {
                        document.documentElement.requestFullscreen();
                    }
                    break;
            }
        });

        // Add some visual enhancements
        function addVisualEffects() {
            // Random subtle animations for occupied cells
            const occupiedCells = document.querySelectorAll('.rack-monitor-cell.occupied');
            occupiedCells.forEach((cell, index) => {
                setTimeout(() => {
                    cell.style.animation = 'pulse 3s ease-in-out infinite';
                    cell.style.animationDelay = `${index * 0.1}s`;
                }, index * 100);
            });
        }

        // Initialize effects after a short delay
        setTimeout(addVisualEffects, 1000);

        // Update statistics periodically
        function updateStatistics() {
            const totalSlots = 8 * 4 * 6;
            const occupiedSlots = document.querySelectorAll('.rack-monitor-cell.occupied').length;
            const emptySlots = totalSlots - occupiedSlots;
            
            document.getElementById('totalRacks').textContent = totalSlots;
            document.getElementById('occupiedRacks').textContent = occupiedSlots;
            document.getElementById('emptyRacks').textContent = emptySlots;
        }

        updateStatistics();
    </script>
</body>
</html>