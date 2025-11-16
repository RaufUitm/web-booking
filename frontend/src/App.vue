<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-container">
        <h1>Booking System</h1>
        <div class="nav-links">
          <router-link to="/">Make Booking</router-link>
          <router-link to="/bookings">View Bookings</router-link>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<style>
.navbar {
  background-color: #2c3e50;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar h1 {
  color: white;
  margin: 0;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
}

.nav-links a {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  background-color: #34495e;
}
</style>
