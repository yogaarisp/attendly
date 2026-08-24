@extends('layouts.employee', ['title' => 'Absen Pulang'])

@section('content')
<div class="space-y-4">
    
    <!-- Top Header Card -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Konfirmasi Absen Pulang</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Verifikasi wajah dan lokasi untuk menyelesaikan hari kerja</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </a>
    </div>

    <!-- Main Camera Viewfinder Card -->
    <div class="bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-3xl p-4 shadow-sm dark:shadow-2xl overflow-hidden relative">
        
        <!-- Viewport Container -->
        <div class="relative w-full aspect-square bg-black rounded-2xl overflow-hidden flex items-center justify-center border border-slate-200 dark:border-slate-700 shadow-inner">
            
            <video id="camera-stream" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
            <canvas id="photo-canvas" class="hidden w-full h-full object-cover"></canvas>

            <!-- Face Overlay -->
            <div id="face-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-48 h-64 border-2 border-dashed border-amber-400/80 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.4)]"></div>
                <div class="absolute bottom-4 text-center bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full text-[11px] font-semibold text-white/90">
                    Posisikan wajah di dalam oval
                </div>
            </div>

            <!-- Loading State -->
            <div id="camera-loading" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center gap-3 text-slate-400">
                <div class="w-8 h-8 border-3 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-medium">Memulai Kamera...</span>
            </div>

            <!-- Permission Denied State -->
            <div id="camera-error" class="hidden absolute inset-0 bg-slate-900 p-6 flex flex-col items-center justify-center text-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                    <i data-lucide="camera-off" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-white">Izin Kamera Ditolak</h4>
                <p id="camera-error-msg" class="text-xs text-slate-400 leading-relaxed">
                    Camera permission diperlukan untuk melakukan absensi selfie. Silakan aktifkan izin kamera di pengaturan browser.
                </p>
                <button type="button" onclick="startCamera()" class="mt-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-xl transition-colors">
                    Coba Lagi
                </button>
            </div>

        </div>

        <!-- GPS & Location Status Bar -->
        <div class="mt-3 bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2.5">
                <div id="gps-indicator" class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></div>
                <div>
                    <p id="gps-status-text" class="font-semibold text-slate-700 dark:text-slate-300">Mendeteksi koordinat GPS...</p>
                    <p id="gps-accuracy-text" class="text-[10px] text-slate-400 dark:text-slate-500">Mencari sinyal satelit</p>
                </div>
            </div>
            <button type="button" onclick="refreshGPS()" title="Refresh Lokasi" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4">
            <div id="capture-controls" class="flex items-center justify-center">
                <button 
                    type="button" 
                    id="btn-capture" 
                    onclick="takeSnapshot()" 
                    disabled
                    class="w-16 h-16 rounded-full bg-gradient-to-tr from-amber-600 to-orange-500 border-4 border-slate-200 dark:border-slate-800 shadow-xl shadow-amber-500/40 flex items-center justify-center text-white active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer"
                >
                    <i data-lucide="camera" class="w-7 h-7"></i>
                </button>
            </div>

            <div id="confirm-controls" class="hidden grid grid-cols-2 gap-3">
                <button 
                    type="button" 
                    onclick="retakePhoto()" 
                    class="py-3 px-4 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-2xl text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
                >
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Ambil Ulang</span>
                </button>
                <button 
                    type="button" 
                    id="btn-submit"
                    onclick="submitAttendance()" 
                    class="py-3 px-4 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/30 transition-all cursor-pointer"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Konfirmasi Pulang</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Success Modal Popup -->
    <div id="modal-success" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 max-w-sm w-full text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 rounded-full bg-indigo-500/20 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mx-auto">
                <i data-lucide="check-circle-2" class="w-10 h-10"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Absensi Pulang Berhasil!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Terima kasih atas kerja keras Anda hari ini.</p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/80 rounded-2xl p-3 text-xs space-y-1.5 text-left border border-slate-200 dark:border-slate-700/60">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Jam Pulang:</span>
                    <strong id="res-time" class="text-slate-900 dark:text-white">--:--:--</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Status Akhir:</span>
                    <strong id="res-status" class="text-emerald-600 dark:text-emerald-400">Lengkap / Hadir</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Jarak ke Kantor:</span>
                    <strong id="res-distance" class="text-slate-900 dark:text-white">-- meter</strong>
                </div>
            </div>

            <a href="{{ route('employee.dashboard') }}" class="w-full block py-3 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs transition-colors">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let videoStream = null;
    let capturedPhotoData = null;
    let currentCoords = null;

    // GPS multi-sampling — kumpulkan beberapa pembacaan untuk deteksi fake GPS
    let gpsSamples = [];
    const GPS_SAMPLE_TARGET = 3;
    const GPS_SAMPLE_INTERVAL = 2000;
    let gpsSampleTimer = null;

    const videoEl = document.getElementById('camera-stream');
    const canvasEl = document.getElementById('photo-canvas');
    const loadingEl = document.getElementById('camera-loading');
    const errorEl = document.getElementById('camera-error');
    const errorMsgEl = document.getElementById('camera-error-msg');
    const faceOverlayEl = document.getElementById('face-overlay');
    const captureControls = document.getElementById('capture-controls');
    const confirmControls = document.getElementById('confirm-controls');
    const btnCapture = document.getElementById('btn-capture');
    const btnSubmit = document.getElementById('btn-submit');

    const gpsIndicator = document.getElementById('gps-indicator');
    const gpsStatusText = document.getElementById('gps-status-text');
    const gpsAccuracyText = document.getElementById('gps-accuracy-text');

    async function startCamera() {
        loadingEl.classList.remove('hidden');
        errorEl.classList.add('hidden');

        try {
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 720 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            videoStream = await navigator.mediaDevices.getUserMedia(constraints);
            videoEl.srcObject = videoStream;
            videoEl.onloadedmetadata = () => {
                loadingEl.classList.add('hidden');
                checkReadyState();
            };
        } catch (err) {
            console.error('Camera Error:', err);
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
            errorMsgEl.textContent = 'Izin akses kamera ditolak atau tidak tersedia: ' + err.message;
        }
    }

    function getGPSLocation() {
        if (!navigator.geolocation) {
            gpsStatusText.textContent = 'Geolocation tidak didukung';
            gpsIndicator.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
            return;
        }

        gpsSamples = [];
        gpsStatusText.textContent = 'Mendeteksi koordinat GPS...';
        gpsIndicator.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0';

        collectGPSSample();

        if (gpsSampleTimer) clearInterval(gpsSampleTimer);
        gpsSampleTimer = setInterval(() => {
            if (gpsSamples.length < GPS_SAMPLE_TARGET) {
                collectGPSSample();
            } else {
                clearInterval(gpsSampleTimer);
            }
        }, GPS_SAMPLE_INTERVAL);
    }

    function collectGPSSample() {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const sample = {
                    lat:       pos.coords.latitude,
                    lng:       pos.coords.longitude,
                    accuracy:  pos.coords.accuracy,
                    timestamp: pos.timestamp,
                };
                gpsSamples.push(sample);

                currentCoords = {
                    latitude:  pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy:  pos.coords.accuracy,
                };

                const sampleCount = gpsSamples.length;
                gpsIndicator.className = sampleCount >= GPS_SAMPLE_TARGET
                    ? 'w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0'
                    : 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0';

                gpsStatusText.textContent = sampleCount >= GPS_SAMPLE_TARGET
                    ? 'GPS Terkunci (Siap)'
                    : `GPS: Mengumpulkan data... (${sampleCount}/${GPS_SAMPLE_TARGET})`;
                gpsAccuracyText.textContent = `Akurasi: ±${Math.round(pos.coords.accuracy)} meter`;

                checkReadyState();
            },
            (err) => {
                console.error('GPS Error:', err);
                if (gpsSamples.length === 0) {
                    gpsIndicator.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
                    gpsStatusText.textContent = 'Gagal Mengakses GPS';
                    gpsAccuracyText.textContent = 'Aktifkan GPS perangkat Anda';
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function refreshGPS() {
        if (gpsSampleTimer) clearInterval(gpsSampleTimer);
        getGPSLocation();
    }

    function checkReadyState() {
        if (videoStream && currentCoords) {
            btnCapture.disabled = false;
        }
    }

    function takeSnapshot() {
        const width = videoEl.videoWidth || 640;
        const height = videoEl.videoHeight || 640;

        canvasEl.width = width;
        canvasEl.height = height;
        const ctx = canvasEl.getContext('2d');

        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(videoEl, 0, 0, width, height);

        capturedPhotoData = canvasEl.toDataURL('image/jpeg', 0.85);

        videoEl.classList.add('hidden');
        canvasEl.classList.remove('hidden');
        faceOverlayEl.classList.add('hidden');

        captureControls.classList.add('hidden');
        confirmControls.classList.remove('hidden');
    }

    function retakePhoto() {
        capturedPhotoData = null;
        canvasEl.classList.add('hidden');
        videoEl.classList.remove('hidden');
        faceOverlayEl.classList.remove('hidden');

        confirmControls.classList.add('hidden');
        captureControls.classList.remove('hidden');
    }

    async function submitAttendance() {
        if (!capturedPhotoData || !currentCoords) {
            alert('Data foto atau GPS belum lengkap.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>Memproses Absen...</span>
        `;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch("{{ route('employee.attendance.checkout.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    latitude:    currentCoords.latitude,
                    longitude:   currentCoords.longitude,
                    accuracy:    currentCoords.accuracy,
                    photo:       capturedPhotoData,
                    gps_samples: gpsSamples,
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                document.getElementById('res-time').textContent = result.data.check_out_at + ' WIB';
                document.getElementById('res-status').textContent = result.data.overall_status;
                document.getElementById('res-distance').textContent = result.data.distance + ' meter';
                document.getElementById('modal-success').classList.remove('hidden');
            } else {
                alert(result.message || 'Gagal melakukan absensi pulang.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Konfirmasi Pulang</span>`;
                lucide.createIcons();
            }
        } catch (err) {
            console.error('Submit error:', err);
            alert('Terjadi kendala jaringan saat mengirim absensi: ' + err.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Konfirmasi Pulang</span>`;
            lucide.createIcons();
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        startCamera();
        getGPSLocation();
    });

    window.addEventListener('beforeunload', () => {
        if (gpsSampleTimer) clearInterval(gpsSampleTimer);
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endpush
