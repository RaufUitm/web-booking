<template>
  <aside :class="['sidebar', { collapsed: isCollapsed }]">
    <button class="collapse-btn" @click="toggleCollapse">
      {{ isCollapsed ? '→' : '←' }}
    </button>

    <nav class="sidebar-nav">
      <router-link to="/admin" class="sidebar-link">
        <span class="icon">📊</span>
        <span v-if="!isCollapsed" class="text">Dashboard</span>
      </router-link>

      <router-link to="/admin/facilities" class="sidebar-link">
        <span class="icon">🏢</span>
        <span v-if="!isCollapsed" class="text">Manage Facilities</span>
      </router-link>

      <router-link to="/admin/bookings" class="sidebar-link">
        <span class="icon">📅</span>
        <span v-if="!isCollapsed" class="text">Manage Bookings</span>
      </router-link>

      <router-link to="/admin/categories" class="sidebar-link">
        <span class="icon">📁</span>
        <span v-if="!isCollapsed" class="text">Categories</span>
      </router-link>

      <router-link to="/admin/users" class="sidebar-link">
        <span class="icon">👥</span>
        <span v-if="!isCollapsed" class="text">Users</span>
      </router-link>

      <router-link to="/admin/reports" class="sidebar-link">
        <span class="icon">📈</span>
        <span v-if="!isCollapsed" class="text">Reports</span>
      </router-link>

      <router-link to="/admin/settings" class="sidebar-link">
        <span class="icon">⚙️</span>
        <span v-if="!isCollapsed" class="text">Settings</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <router-link to="/" class="sidebar-link">
        <span class="icon">🏠</span>
        <span v-if="!isCollapsed" class="text">Back to Site</span>
      </router-link>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({
  name: 'AdminSidebar'
})

const isCollapsed = ref(false)

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}
</script>

<style scoped>
.sidebar {
  width: 250px;
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
  width: 70px;
}

.collapse-btn {
  position: absolute;
  right: -15px;
  top: 20px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #FF8C00;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
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
  gap: 15px;
  padding: 15px 20px;
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
  font-size: 20px;
  min-width: 20px;
}

.text {
  font-size: 14px;
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
