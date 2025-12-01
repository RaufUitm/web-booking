<template>
  <div class="min-h-screen flex flex-col bg-white" style="margin: 0 !important; padding: 0 !important; width: 100vw !important; position: relative; left: 50%; right: 50%; margin-left: -50vw !important; margin-right: -50vw !important;">
    <!-- Nav is provided by App.vue to avoid duplication -->

    <!-- Header -->
    <header class="w-full landing-hero shadow-sm border-b-4 border-yellow-400 py-12" style="width: 100% !important; margin: 0 !important; padding-left: 0 !important; padding-right: 0 !important; position: relative;">
      <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; text-align: center; position: relative; z-index: 2;">
        <h1 style="font-size: clamp(1.5rem, 2.5vw, 2.5rem); font-weight: 700; color: #ffffff; margin-bottom: 0.5rem; text-align: center;">T-Smart Booking</h1>
        <p style="font-size: clamp(1rem, 1.5vw, 1.3rem); color: #ffffff; margin-bottom: 1rem; text-align: center;">Sistem Tempahan Kemudahan Awam Negeri Terengganu</p>
        <p style="font-size: clamp(0.85rem, 1vw, 1rem); color: #ffffff; max-width: clamp(80%, 60vw, 90%); margin: 0 auto; line-height: 1.2; text-align: center;">
          Terengganu Smart Booking adalah sistem tempahan kemudahan awam dalam talian yang memudahkan orang ramai dan organisasi untuk menempah kemudahan awam di seluruh negeri Terengganu. Pilih daerah anda untuk melihat senarai kemudahan yang tersedia dan buat tempahan dengan mudah.
        </p>

      </div>
    </header>

    <!-- Main Content -->
    <div class="mx-8 after-hero"><h2 class=" text-2xl md:text-3xl font-semibold text-black text-center">Daerah Pilihan Anda</h2></div>

    <main class="flex-1 flex flex-col items-center justify-center">
      <!-- Introduction -->


      <!-- Daerah Cards Grid (4 columns with centered second row) -->
      <div class="w-full max-w-7xl px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-6 md:gap-8">
          <!-- Besut -->
          <div
            @click="navigateToDaerah('besut')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-besut">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/derebesut.png" alt="Terengganu Flag" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Besut</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDB.png" alt="Logo Majlis Daerah Besut" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Besut</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Besut
              </p>
              <div class="flex items-center justify-center text-red-600 font-medium text-sm hover:text-red-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Marang -->
          <div
            @click="navigateToDaerah('marang')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-marang">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/deremarang.png" alt="Terengganu Flag" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Marang</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDM.png" alt="Logo Majlis Daerah Marang" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Marang</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Marang
              </p>
              <div class="flex items-center justify-center text-purple-600 font-medium text-sm hover:text-purple-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Setiu -->
          <div
            @click="navigateToDaerah('setiu')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-setiu">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/deresetiu.png" alt="Terengganu Flag" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Setiu</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDS.jpg" alt="Logo Majlis Daerah Setiu" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Setiu</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Setiu
              </p>
              <div class="flex items-center justify-center text-amber-700 font-medium text-sm hover:text-amber-800">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Hulu Terengganu -->
          <div
            @click="navigateToDaerah('hulu-terengganu')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-hulu-terengganu">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/derehulu.png" alt="Terengganu Flag" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Hulu Terengganu</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDHT.png" alt="Logo Majlis Daerah Hulu Terengganu" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Hulu Terengganu</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Hulu Terengganu
              </p>
              <div class="flex items-center justify-center text-orange-600 font-medium text-sm hover:text-orange-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>
          <!-- Kuala Terengganu (MBKT) -->
          <div
            @click="navigateToDaerah('kuala-terengganu')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2 lg:col-start-2 border-t-4 border-yellow-400"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-mbkt">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/derekt.png" alt="Kuala Terengganu" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Kuala Terengganu</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MBKT.png" alt="Logo MBKT" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Bandaraya Kuala Terengganu</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Kuala Terengganu
              </p>
              <div class="flex items-center justify-center text-yellow-600 font-medium text-sm hover:text-yellow-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Kemaman (MPK) -->
          <div
            @click="navigateToDaerah('kemaman')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2 border-t-4 border-blue-900"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-kemaman">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/derekemaman.png" alt="Kemaman" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Kemaman</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MPK.png" alt="Logo MPK" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Perbandaran Kemaman</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Kemaman
              </p>
              <div class="flex items-center justify-center text-blue-900 font-medium text-sm hover:text-blue-800">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Dungun (MPD) -->
          <div
            @click="navigateToDaerah('dungun')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden lg:col-span-2 border-t-4 border-cyan-600"
          >
            <div class="h-80 flex items-center justify-center relative daerah-bg daerah-bg-dungun">
              <div class="absolute top-3 left-3 z-10">
                <img src="/images/deredungun.png" alt="Dungun" class="w-10 h-7 object-contain rounded shadow" />
              </div>
              <div class="text-center w-full px-4 relative z-10">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Dungun</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MPD.png" alt="Logo MPD" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Perbandaran Dungun</p>
              </div>
              <div class="absolute inset-0 bg-black/40 z-0"></div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-white-600 text-center mb-2">
                Tempahan kemudahan awam di Dungun
              </p>
              <div class="flex items-center justify-center text-cyan-700 font-medium text-sm hover:text-cyan-800">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="mt-8 text-center">
        <p class="text-xs text-gray-500">© 2025 T-Smart Booking. Hak Cipta Terpelihara. Kerajaan Negeri Terengganu.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'
import useDistrictRoutes from '@/utils/districtRoutes'
const router = useRouter()
const { prefixPath } = useDistrictRoutes()

import { useDistrictStore } from '@/stores/district'

const districtStore = useDistrictStore()

// auth state is handled in the navbar; landing page no longer needs auth store

// Clear previously selected district when showing landing page
onMounted(() => {
  // only clear if a district was stored — user wants to re-select
  if (districtStore.currentDistrict) {
    districtStore.clearDistrict()
  }
})

const navigateToDaerah = (daerah) => {
  const districtMap = {
    'besut': 'Besut',
    'marang': 'Marang',
    'setiu': 'Setiu',
    'hulu-terengganu': 'Hulu Terengganu',
    'kuala-terengganu': 'Kuala Terengganu',
    'kemaman': 'Kemaman',
    'dungun': 'Dungun'
  }

  const districtName = districtMap[daerah]

  // persist selection immediately so navbar can react
  districtStore.setDistrict(districtName)

  router.push({
    path: prefixPath('/'),
    query: { district: districtName }
  })
}

// no logout here; navbar in App.vue handles auth actions
</script>

<style scoped>
/* Register Button - Outline Style */
.register-btn {
  background: transparent;
  color: #FF8C00;
  font-weight: 600;
  padding: clamp(0.6rem, 0.8vw, 0.7rem) clamp(1.5rem, 2.5vw, 2rem);
  border-radius: 9999px;
  font-size: clamp(0.9rem, 1.2vw, 1rem);
  border: 2px solid #FF8C00;
  transition: all 0.3s ease;
  cursor: pointer;
  letter-spacing: 0.02em;
  display: inline-flex;
  align-items: center;
  gap: clamp(0.4rem, 0.6vw, 0.5rem);
  text-decoration: none;
}

.register-btn:hover {
  background: #FF8C00;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 140, 0, 0.2);
}

/* Login Button - Solid Style */
.login-btn {
  background: #111;
  color: #fff;
  font-weight: 600;
  padding: clamp(0.6rem, 0.8vw, 0.7rem) clamp(1.5rem, 2.5vw, 2rem);
  border-radius: 9999px;
  font-size: clamp(0.9rem, 1.2vw, 1rem);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
  letter-spacing: 0.02em;
  display: inline-flex;
  align-items: center;
  gap: clamp(0.4rem, 0.6vw, 0.5rem);
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.login-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: #222;
  transition: left 0.3s ease;
  z-index: -1;
}

.login-btn:hover::before {
  left: 0;
}

.login-btn:hover {
  background: #222;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.22);
}

.login-btn:active,
.register-btn:active {
  transform: translateY(0);
}

/* Icon Styling */
.icon {
  width: 1.25rem;
  height: 1.25rem;
  stroke-width: 2.5;
}

/* Responsive Design */
@media (max-width: 640px) {
  .register-btn,
  .login-btn {
    padding: clamp(0.5rem, 1vw, 0.6rem) clamp(1.2rem, 2vw, 1.5rem);
    font-size: clamp(0.85rem, 1.1vw, 0.9rem);
  }

  .icon {
    width: clamp(1rem, 1.2vw, 1.1rem);
    height: clamp(1rem, 1.2vw, 1.1rem);
  }
}
/* Daerah Card Backgrounds */
.daerah-bg {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
}
.daerah-bg-besut {
  background-image: url('/image/daerah/besut1.jpg');
}
.daerah-bg-marang {
  background-image: url('/image/daerah/marang1.jpg');
}
.daerah-bg-setiu {
  background-image: url('/image/daerah/setiu1.jpg');
}
.daerah-bg-hulu-terengganu {
  background-image: url('/image/daerah/hulu1.png');
}
/* New PBT backgrounds (fallback to hero if not present) */
.daerah-bg-mbkt {
  background-image: url('/image/daerah/kt1.jpg'), url('/image/d.png');
}
.daerah-bg-kemaman {
  background-image: url('/image/daerah/kemaman1.jpg'), url('/image/d.png');
}
.daerah-bg-dungun {
  background-image: url('/image/daerah/dungun1.jpg'), url('/image/d.png');
}

/* Landing page hero background */
.landing-hero {
  background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('/image/d.png');
  background-size: cover;
  background-position: center;
  height: clamp(300px, 40vh, 400px);
  position: relative;
  padding-top: clamp(1.5rem, 4vh, 3rem);
  background-repeat: no-repeat;
  background-position: center;
}

/* Use League Spartan for landing hero headings only */
.landing-hero h1,
.landing-hero p,
.landing-hero h2 {
  font-family: 'League Spartan', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}

/* Small gap below the hero before the next section */
.after-hero {
  margin-top: clamp(1rem, 3vw, 2.5rem);
}

/* Top-right auth CTAs */
/* top-right auth removed — navbar provides auth buttons */
</style>