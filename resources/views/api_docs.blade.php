<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REST API Documentation & Interactive Explorer — CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="bg-[#dce9f2] text-slate-800 min-h-screen relative selection:bg-[#2c566e] selection:text-white">
    <!-- Background Decor -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#bed7e6] rounded-full filter blur-3xl opacity-60"></div>
        <div class="absolute top-1/3 -right-40 w-96 h-96 bg-[#8bb5cc] rounded-full filter blur-3xl opacity-40"></div>
    </div>

    <!-- Header Navigation -->
    <header class="relative z-10 sticky top-0 bg-white/70 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('track.index') }}" class="flex items-center gap-2 font-black text-xl tracking-tight text-[#0b1723] group">
                    <span class="w-8 h-8 rounded-xl bg-[#0c1a24] text-white flex items-center justify-center font-mono font-bold text-sm shadow-md group-hover:scale-105 transition">CT</span>
                    <span>CargoTrace<span class="text-[#2c566e]">.</span></span>
                </a>
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold tracking-widest uppercase bg-[#2c566e]/10 text-[#2c566e] rounded-full border border-[#2c566e]/20">REST API v1</span>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('api.docs.download') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl bg-white hover:bg-slate-50 border border-slate-300 shadow-sm text-slate-700 transition">
                    <svg class="w-4 h-4 text-[#ef5b25]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14.59L8.41 12 13 7.41V11h4v2h-4z"/></svg>
                    <span>Download Postman (.json)</span>
                </a>
                <a href="{{ route('track.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-[#0c1a24] hover:bg-slate-800 text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Web</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Hero Header -->
        <div class="mb-8">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#2c566e]">Enterprise API Explorer</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0b1723] tracking-tight mt-1">CargoTrace REST API Documentation</h1>
            <p class="text-sm text-slate-600 mt-2 max-w-2xl leading-relaxed">
                Katalog dokumentasi endpoint RESTful lengkap yang mengimplementasikan autentikasi <strong>Laravel Sanctum Bearer Token</strong>, validasi server-side, relasi database 3NF, perhitungan deterministik SLA delay, serta integrasi cuaca maritim Open-Meteo.
            </p>

            <div class="mt-4 flex flex-wrap gap-2 text-xs font-mono">
                <span class="px-3 py-1 rounded-lg bg-white border border-slate-200 shadow-sm text-slate-700"><strong>Base URL:</strong> http://127.0.0.1:8000/api</span>
                <span class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800"><strong>Format:</strong> JSON Envelope</span>
                <span class="px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800"><strong>Auth:</strong> Bearer Token (Sanctum)</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Endpoint Categories & List (7 cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. PUBLIC TELEMETRY -->
                <div class="glass-panel p-6 rounded-3xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <h2 class="text-base font-bold text-[#0b1723]">1. Public Telemetry & Pelacakan Kargo</h2>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-400">Akses: Publik (Tanpa Token)</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Endpoint Item -->
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 overflow-hidden">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800 truncate">/api/public/track/{resi}</code>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button onclick="testApi('/api/public/track/CT-20261118-SEAEUR', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                        Test Live
                                    </button>
                                    <a href="/api/public/track/CT-20261118-SEAEUR" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition">
                                        Buka JSON ↗
                                    </a>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Mengambil riwayat tracking, delay analisis, cuaca maritim, dan manifest kargo berdasarkan nomor resi.</p>
                        </div>
                    </div>
                </div>

                <!-- 2. AUTHENTICATION -->
                <div class="glass-panel p-6 rounded-3xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <h2 class="text-base font-bold text-[#0b1723]">2. Autentikasi Sanctum</h2>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-400">Token Management</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Login Operator -->
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-blue-100 text-blue-800">POST</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/login</code>
                                </div>
                                <button onclick="testLogin('operator@gmail.com', 'operator123')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Login (Operator) & Ambil Token
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Menghasilkan Sanctum Bearer Token untuk role Operator, Admin, atau Customer.</p>
                        </div>

                        <!-- Get Authenticated User -->
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/user</code>
                                </div>
                                <button onclick="testProtected('/api/user', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Test Profil User
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Mendapatkan identitas user yang sedang aktif menggunakan Bearer Token.</p>
                        </div>
                    </div>
                </div>

                <!-- 3. SHIPMENTS RESOURCE -->
                <div class="glass-panel p-6 rounded-3xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                            <h2 class="text-base font-bold text-[#0b1723]">3. Manajemen Pengiriman (Shipments)</h2>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-400">CRUD & Filters</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/shipments</code>
                                </div>
                                <button onclick="testProtected('/api/shipments?per_page=5', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Test List
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">List pengiriman dengan pagination, pencarian resi, dan filter status.</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/shipments/{id}</code>
                                </div>
                                <button onclick="testProtected('/api/shipments/1', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Test Detail ID 1
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Detail spesifikasi kargo, rute asal-tujuan, dan relasi customer.</p>
                        </div>
                    </div>
                </div>

                <!-- 4. TRACKING & EXCEPTIONS -->
                <div class="glass-panel p-6 rounded-3xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <h2 class="text-base font-bold text-[#0b1723]">4. Timeline & Kendala (Exceptions)</h2>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-400">Incident & Milestones</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/shipments/1/tracking</code>
                                </div>
                                <button onclick="testProtected('/api/shipments/1/tracking', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Test Milestones
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Daftar checkpoint kronologis perjalanan kargo.</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-[#2c566e] transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">GET</span>
                                    <code class="text-xs font-semibold text-slate-800">/api/shipments/1/exceptions</code>
                                </div>
                                <button onclick="testProtected('/api/shipments/1/exceptions', 'GET')" class="px-2.5 py-1 bg-[#0c1a24] hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition shadow-sm">
                                    Test Exceptions
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Daftar kendala yang terjadi (Customs hold, Weather delay, dll.).</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Live Interactive Response Console (5 cols) -->
            <div class="lg:col-span-5">
                <div class="sticky top-24 glass-panel p-6 rounded-3xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-700">Live API Console</span>
                        </div>
                        <span id="response-status" class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-200 text-slate-700">Ready</span>
                    </div>

                    <!-- Token Active Banner -->
                    <div class="mb-3 p-2.5 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 overflow-hidden">
                            <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            <span id="token-status" class="text-[11px] font-mono text-slate-600 truncate">Token: Belum ada (klik Login)</span>
                        </div>
                        <button onclick="clearToken()" class="text-[10px] text-red-500 hover:underline flex-shrink-0">Reset</button>
                    </div>

                    <!-- Console Box -->
                    <div class="relative">
                        <div class="absolute top-2 right-2">
                            <button onclick="copyResponse()" class="px-2 py-1 text-[10px] font-mono font-bold bg-slate-700 hover:bg-slate-600 text-white rounded transition">Copy JSON</button>
                        </div>
                        <pre id="json-viewer" class="w-full h-[450px] p-4 bg-[#0a141d] text-emerald-400 font-mono text-xs rounded-2xl overflow-y-auto leading-relaxed border border-slate-800 shadow-inner">// Klik tombol "Test Live" di sebelah kiri untuk melihat output respon JSON di sini...</pre>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                        <span>Latency: <strong id="response-time">-</strong></span>
                        <a href="{{ route('api.docs.download') }}" class="text-[#2c566e] font-bold hover:underline">Download Postman Collection (.json)</a>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <script>
        let currentToken = '';

        function updateTokenUI() {
            const el = document.getElementById('token-status');
            if (currentToken) {
                el.innerHTML = '<span class="text-emerald-700 font-bold">✓ Token Aktif (' + currentToken.substring(0, 10) + '...)</span>';
            } else {
                el.innerText = 'Token: Belum ada (klik Login)';
            }
        }

        function clearToken() {
            currentToken = '';
            updateTokenUI();
        }

        async function testLogin(email, password) {
            const viewer = document.getElementById('json-viewer');
            const statusBadge = document.getElementById('response-status');
            const timeBadge = document.getElementById('response-time');
            
            viewer.innerText = 'Melakukan login ke /api/login...';
            statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-800';
            statusBadge.innerText = 'Sending...';

            const start = performance.now();
            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                const elapsed = Math.round(performance.now() - start);
                timeBadge.innerText = elapsed + ' ms';

                const data = await res.json();
                statusBadge.className = res.ok ? 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800' : 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = res.status + ' ' + res.statusText;

                viewer.innerText = JSON.stringify(data, null, 2);

                if (data.data && data.data.token) {
                    currentToken = data.data.token;
                    updateTokenUI();
                }
            } catch (err) {
                viewer.innerText = 'Error: ' + err.message;
                statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = 'ERR';
            }
        }

        async function testApi(url, method = 'GET') {
            const viewer = document.getElementById('json-viewer');
            const statusBadge = document.getElementById('response-status');
            const timeBadge = document.getElementById('response-time');

            viewer.innerText = 'Mengirim request ke ' + url + '...';
            statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-800';
            statusBadge.innerText = 'Sending...';

            const start = performance.now();
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const elapsed = Math.round(performance.now() - start);
                timeBadge.innerText = elapsed + ' ms';

                const data = await res.json();
                statusBadge.className = res.ok ? 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800' : 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = res.status + ' ' + res.statusText;

                viewer.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                viewer.innerText = 'Error: ' + err.message;
                statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = 'ERR';
            }
        }

        async function testProtected(url, method = 'GET') {
            if (!currentToken) {
                await testLogin('operator@gmail.com', 'operator123');
            }

            const viewer = document.getElementById('json-viewer');
            const statusBadge = document.getElementById('response-status');
            const timeBadge = document.getElementById('response-time');

            viewer.innerText = 'Mengirim request tervalidasi Bearer Token ke ' + url + '...';
            statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-800';
            statusBadge.innerText = 'Sending...';

            const start = performance.now();
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + currentToken
                    }
                });
                const elapsed = Math.round(performance.now() - start);
                timeBadge.innerText = elapsed + ' ms';

                const data = await res.json();
                statusBadge.className = res.ok ? 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800' : 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = res.status + ' ' + res.statusText;

                viewer.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                viewer.innerText = 'Error: ' + err.message;
                statusBadge.className = 'px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-red-100 text-red-800';
                statusBadge.innerText = 'ERR';
            }
        }

        function copyResponse() {
            const viewer = document.getElementById('json-viewer');
            navigator.clipboard.writeText(viewer.innerText);
            alert('JSON berhasil disalin ke clipboard!');
        }
    </script>
</body>
</html>
