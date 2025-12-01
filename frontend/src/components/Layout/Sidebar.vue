<template>
  <aside :class="['sidebar', { collapsed: isCollapsed }]">
    <button class="collapse-btn" @click="toggleCollapse">
      {{ isCollapsed ? '→' : '←' }}
    </button>

    <nav class="sidebar-nav">
      <router-link :to="prefixPath('/admin')" class="sidebar-link">
        <span class="icon">📊</span>
        <span v-if="!isCollapsed" class="text">Papan Pemuka</span>
      </router-link>

      <router-link :to="prefixPath('/admin/facilities')" class="sidebar-link">
        <span class="icon">🏢</span>
        <span v-if="!isCollapsed" class="text">Urus Kemudahan</span>
      </router-link>

      <router-link :to="prefixPath('/admin/bookings')" class="sidebar-link">
        <span class="icon">📅</span>
        <span v-if="!isCollapsed" class="text">Urus Tempahan</span>
      </router-link>

      <router-link :to="prefixPath('/admin/categories')" class="sidebar-link">
        <span class="icon">📁</span>
        <span v-if="!isCollapsed" class="text">Kategori</span>
      </router-link>

      <router-link :to="prefixPath('/admin/users')" class="sidebar-link">
        <span class="icon">👥</span>
        <span v-if="!isCollapsed" class="text">Pengguna</span>
      </router-link>

      <router-link :to="prefixPath('/admin/reports')" class="sidebar-link">
        <span class="icon">📈</span>
        <span v-if="!isCollapsed" class="text">Laporan</span>
      </router-link>

      <router-link :to="prefixPath('/admin/settings')" class="sidebar-link">
        <span class="icon">⚙️</span>
        <span v-if="!isCollapsed" class="text">Tetapan</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <router-link to="/" class="sidebar-link">
        <span class="icon">🏠</span>
        <span v-if="!isCollapsed" class="text">Kembali ke Laman</span>
      </router-link>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import useDistrictRoutes from '@/utils/districtRoutes'

defineOptions({
  name: 'AdminSidebar'
})

const isCollapsed = ref(false)

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}
const { prefixPath } = useDistrictRoutes()
</script>

<style scoped>
.sidebar {
  width: clamp(200px, 25vw, 250px);
  background-color: #2c3e50;
  color: #ecf0f1;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  transition: width 0.3s;
  display: flex;
  flex-direction: column;
  z-index: 100;
}

.sidebar.collapsed {
  width: clamp(60px, 8vw, 70px);
}

.collapse-btn {
  position: absolute;
  right: clamp(-12px, -2vw, -15px);
  top: clamp(18px, 2.5vw, 20px);
  width: clamp(28px, 4vw, 30px);
  height: clamp(28px, 4vw, 30px);
  border-radius: 50%;
  background-color: #FF8C00;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: clamp(14px, 2vw, 16px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s;
}

.collapse-btn:hover {
  transform: scale(1.1);
}

.sidebar-nav {
  flex: 1;
  padding: 80px 0 20px;
  overflow-y: auto;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: clamp(12px, 1.8vw, 15px);
  padding: clamp(12px, 1.8vw, 15px) clamp(16px, 2.5vw, 20px);
  color: #ecf0f1;
  text-decoration: none;
  transition: all 0.3s;
  white-space: nowrap;
}

.sidebar-link:hover {
  background-color: #34495e;
  border-left: 4px solid #FF8C00;
  padding-left: 16px;
}

.sidebar-link.router-link-active {
  background-color: #34495e;
  border-left: 4px solid #FF8C00;
  padding-left: 16px;
  color: #FF8C00;
}

.sidebar.collapsed .sidebar-link {
  justify-content: center;
  padding: 15px;
}

.sidebar.collapsed .sidebar-link:hover,
.sidebar.collapsed .sidebar-link.router-link-active {
  padding-left: 15px;
}

.icon {
  font-size: clamp(18px, 2.5vw, 20px);
  min-width: clamp(18px, 2.5vw, 20px);
}

.text {
  font-size: clamp(13px, 1.6vw, 14px);
  font-weight: 500;
}

.sidebar-footer {
  padding: 20px 0;
  border-top: 1px solid #34495e;
}

.sidebar-nav::-webkit-scrollbar {
  width: 6px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background-color: #34495e;
  border-radius: 3px;
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .collapse-btn {
    display: none;
  }

  .text {
    display: none;
  }

  .sidebar-link {
    justify-content: center;
    padding: 15px;
  }

  .sidebar-link:hover,
  .sidebar-link.router-link-active {
    padding-left: 15px;
  }
}
</style>
