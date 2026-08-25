// Peta pemilih titik koordinat cabang + reverse geocoding (alamat otomatis).
// Membutuhkan Leaflet (CSS+JS) dan elemen dengan ID:
// branch_map, latitude_input, longitude_input, address_input, address_preview
// serta input[name="radius_meter"].
(function () {
    'use strict';

    const latInput      = document.getElementById('latitude_input');
    const lngInput      = document.getElementById('longitude_input');
    const radiusInput   = document.querySelector('input[name="radius_meter"]');
    const addressInput  = document.getElementById('address_input');
    const addressPreview = document.getElementById('address_preview');
    const mapEl         = document.getElementById('branch_map');

    if (!mapEl || !latInput || !lngInput || typeof L === 'undefined') return;

    const DEFAULT_LAT = -7.7956;
    const DEFAULT_LNG = 110.3695;

    let lat = parseFloat(latInput.value);
    let lng = parseFloat(lngInput.value);
    if (isNaN(lat)) lat = DEFAULT_LAT;
    if (isNaN(lng)) lng = DEFAULT_LNG;

    const map = L.map(mapEl, { scrollWheelZoom: true }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Lingkaran radius absensi mengikuti pin & input radius.
    let radiusCircle = null;
    function drawRadius() {
        const r = parseInt(radiusInput ? radiusInput.value : 0, 10);
        if (!r || r < 10) {
            if (radiusCircle) { radiusCircle.remove(); radiusCircle = null; }
            return;
        }
        if (radiusCircle) {
            radiusCircle.setLatLng(marker.getLatLng()).setRadius(r);
        } else {
            radiusCircle = L.circle(marker.getLatLng(), {
                radius: r,
                color: '#6366f1',
                weight: 2,
                fillColor: '#6366f1',
                fillOpacity: 0.12
            }).addTo(map);
        }
    }
    drawRadius();
    if (radiusInput) radiusInput.addEventListener('input', drawRadius);

    // Reverse geocoding via Nominatim (kebijakan: maks 1 req/detik → debounce 700ms).
    let geocodeTimer = null;
    function reverseGeocode(latlng) {
        if (!addressPreview && !addressInput) return;
        clearTimeout(geocodeTimer);
        if (addressPreview) addressPreview.textContent = 'Mencari alamat lengkap…';
        geocodeTimer = setTimeout(async () => {
            try {
                const url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
                    + '&lat=' + latlng.lat + '&lon=' + latlng.lng
                    + '&zoom=18&addressdetails=1&accept-language=id';
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data && data.display_name) {
                    if (addressInput) addressInput.value = data.display_name;
                    if (addressPreview) addressPreview.textContent = data.display_name;
                } else if (addressPreview) {
                    addressPreview.textContent = 'Alamat tidak ditemukan untuk titik ini.';
                }
            } catch (e) {
                if (addressPreview) addressPreview.textContent = 'Gagal mencari alamat — periksa koneksi internet.';
            }
        }, 700);
    }

    function setPoint(latlng, pan) {
        latInput.value = latlng.lat.toFixed(8);
        lngInput.value = latlng.lng.toFixed(8);
        marker.setLatLng(latlng);
        if (pan) map.panTo(latlng);
        drawRadius();
        reverseGeocode(latlng);
    }

    // Klik peta → pindahkan pin.
    map.on('click', (e) => setPoint(e.latlng, false));

    // Selesai geser pin → perbarui input + alamat.
    marker.on('dragend', () => setPoint(marker.getLatLng(), false));

    // Edit manual lat/lng → pindahkan pin.
    [latInput, lngInput].forEach((inp) => inp.addEventListener('change', () => {
        const la = parseFloat(latInput.value);
        const ln = parseFloat(lngInput.value);
        if (!isNaN(la) && !isNaN(ln)) setPoint(L.latLng(la, ln), true);
    }));

    // Tombol "Ambil Lokasi Saya".
    window.detectCurrentLocation = function () {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung Geolocation.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const ll = L.latLng(pos.coords.latitude, pos.coords.longitude);
                map.setView(ll, 17);
                setPoint(ll, false);
            },
            (err) => alert('Gagal mendeteksi lokasi: ' + err.message),
            { enableHighAccuracy: true }
        );
    };
})();
