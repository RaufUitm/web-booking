<template>
  <div class="min-h-screen flex flex-col bg-white" style="margin: 0 !important; padding: 0 !important; width: 100vw !important; position: relative; left: 50%; right: 50%; margin-left: -50vw !important; margin-right: -50vw !important;">
    <!-- Header -->
    <header class="w-full bg-white shadow-sm border-b-4 border-yellow-400 py-6" style="width: 100% !important; margin: 0 !important; padding-left: 0 !important; padding-right: 0 !important;">
      <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; text-align: center;">T-Smart Booking</h1>
        <p style="font-size: 1.125rem; color: #4b5563; margin-bottom: 1rem; text-align: center;">Sistem Tempahan Kemudahan Negeri Terengganu</p>
        <p style="font-size: 0.875rem; color: #4b5563; max-width: 48rem; margin: 0 auto; line-height: 1.625; text-align: center;">
          T-Smart Booking adalah sistem tempahan kemudahan dalam talian yang memudahkan penduduk dan organisasi untuk menempah kemudahan awam di seluruh negeri Terengganu. Pilih daerah anda untuk melihat senarai kemudahan yang tersedia dan buat tempahan dengan mudah.
        </p>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center py-8 px-4">
      <!-- Introduction -->
      <div class="text-center mb-8 max-w-3xl">
        <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-3">Pilih Daerah Anda</h2>
        <p class="text-sm md:text-base text-gray-600">
          Pilih daerah untuk mengakses sistem tempahan kemudahan yang tersedia di kawasan anda
        </p>
      </div>

      <!-- Daerah Cards Grid (4 columns) -->
      <div class="w-full max-w-7xl px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
          <!-- Besut -->
          <div
            @click="navigateToDaerah('besut')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
          >
            <div class="h-80 bg-[#DC143C] flex items-center justify-center relative">
              <div class="text-center w-full px-4">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Besut</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDB.png" alt="Logo Majlis Daerah Besut" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Besut</p>
              </div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-gray-600 text-center mb-2">
                Sistem tempahan kemudahan di Besut
              </p>
              <div class="flex items-center justify-center text-red-600 font-medium text-sm hover:text-red-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Marang -->
          <div
            @click="navigateToDaerah('marang')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
          >
            <div class="h-80 bg-[#8B008B] flex items-center justify-center relative">
              <div class="text-center w-full px-4">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Marang</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDM.png" alt="Logo Majlis Daerah Marang" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Marang</p>
              </div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-gray-600 text-center mb-2">
                Sistem tempahan kemudahan di Marang
              </p>
              <div class="flex items-center justify-center text-purple-600 font-medium text-sm hover:text-purple-700">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Setiu -->
          <div
            @click="navigateToDaerah('setiu')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
          >
            <div class="h-80 bg-[#8B7355] flex items-center justify-center relative">
              <div class="text-center w-full px-4">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Setiu</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDS.png" alt="Logo Majlis Daerah Setiu" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Setiu</p>
              </div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-gray-600 text-center mb-2">
                Sistem tempahan kemudahan di Setiu
              </p>
              <div class="flex items-center justify-center text-amber-700 font-medium text-sm hover:text-amber-800">
                <span>Klik untuk masuk →</span>
              </div>
            </div>
          </div>

          <!-- Hulu Terengganu -->
          <div
            @click="navigateToDaerah('hulu-terengganu')"
            class="w-full bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
          >
            <div class="h-80 bg-[#FF8C00] flex items-center justify-center relative">
              <div class="text-center w-full px-4">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Hulu Terengganu</h3>
                <div class="flex justify-center mb-2">
                  <img src="/images/MDHT.png" alt="Logo Majlis Daerah Hulu Terengganu" class="w-32 h-32 object-contain">
                </div>
                <p class="text-base md:text-lg font-semibold text-white">Majlis Daerah Hulu Terengganu</p>
              </div>
            </div>
            <div class="p-6 bg-white">
              <p class="text-sm text-gray-600 text-center mb-2">
                Sistem tempahan kemudahan di Hulu Terengganu
              </p>
              <div class="flex items-center justify-center text-orange-600 font-medium text-sm hover:text-orange-700">
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

const router = useRouter()

const navigateToDaerah = (daerah) => {
  // Map district slug to actual district names for API
  const districtMap = {
    'besut': 'Besut',
    'marang': 'Marang',
    'setiu': 'Setiu',
    'hulu-terengganu': 'Hulu Terengganu'
  }

  const districtName = districtMap[daerah]

  // Navigate to the booking system with district parameter
  router.push({
    path: '/mdht',
    query: { district: districtName }
  })
}
</script>

<style scoped>

</style>
