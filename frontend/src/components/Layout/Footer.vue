<template>
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Majlis Daerah Hulu Terengganu</h3>
          <p>Sistem Tempahan Kemudahan Awam - Berkhidmat untuk Rakyat</p>
          <p style="margin-top: 0.5rem;">📍 21700 Kuala Berang, Terengganu</p>
          <div class="social-links">
            <a href="https://www.facebook.com/MDHuluTerengganu" target="_blank" class="social-link" title="Facebook">📘</a>
            <a href="https://www.instagram.com/mdhuluterengganu" target="_blank" class="social-link" title="Instagram">📷</a>
            <a href="https://mdht.terengganu.gov.my" target="_blank" class="social-link" title="Portal Rasmi">🌐</a>
          </div>
        </div>

        <div class="footer-section">
          <h4>Pautan Pantas</h4>
          <ul>
            <li><router-link to="/">Laman Utama</router-link></li>
            <li><router-link to="/facilities">Kemudahan</router-link></li>
            <li><router-link to="/my-bookings">Tempahan Saya</router-link></li>
            <li><a href="https://mdht.terengganu.gov.my" target="_blank">Portal MDHT</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>Kemudahan</h4>
          <ul>
            <li><a href="#">Dewan & Panggung</a></li>
            <li><a href="#">Padang & Gelanggang</a></li>
            <li><a href="#">Pusat Komuniti</a></li>
            <li><a href="#">Kemudahan Sukan</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>Hubungi Kami</h4>
          <ul class="contact-info">
            <li>📧 info@mdht.terengganu.gov.my</li>
            <li>📞 09-626 1333</li>
            <li>📠 09-626 1000</li>
            <li>📍 Majlis Daerah Hulu Terengganu,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;21700 Kuala Berang, Terengganu</li>
            <li>🕐 Isnin - Jumaat: 8:00 AM - 5:00 PM</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; {{ currentYear }} Majlis Daerah Hulu Terengganu. Hak Cipta Terpelihara.</p>
        <div class="footer-links">
          <a href="#">Dasar Privasi</a>
          <span>|</span>
          <a href="#">Terma & Syarat</a>
          <span>|</span>
          <a href="https://mdht.terengganu.gov.my" target="_blank">Portal Rasmi</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({
  name: 'AppFooter'
})

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.footer {
  background-color: #1a3a1b;
  color: #ecf0f1;
  margin-top: 60px;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px 20px;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-section h3 {
  color: #FFB84D;
  margin: 0 0 15px 0;
  font-size: 24px;
}

.footer-section h4 {
  color: #ecf0f1;
  margin: 0 0 15px 0;
  font-size: 18px;
}

.footer-section p {
  margin: 0 0 15px 0;
  line-height: 1.6;
  color: #bdc3c7;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin-bottom: 10px;
}

.footer-section ul li a {
  color: #bdc3c7;
  text-decoration: none;
  transition: color 0.3s;
}

.footer-section ul li a:hover {
  color: #FFB84D;
}

.social-links {
  display: flex;
  gap: 15px;
  margin-top: 15px;
}

.social-link {
  font-size: 24px;
  text-decoration: none;
  transition: transform 0.3s;
}

.social-link:hover {
  transform: translateY(-3px);
}

.contact-info li {
  color: #bdc3c7;
}

.footer-bottom {
  padding-top: 20px;
  border-top: 1px solid #34495e;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.footer-bottom p {
  margin: 0;
  color: #bdc3c7;
}

.footer-links {
  display: flex;
  gap: 10px;
  align-items: center;
}

.footer-links a {
  color: #bdc3c7;
  text-decoration: none;
  font-size: 14px;
}

.footer-links a:hover {
  color: #FFB84D;
}

.footer-links span {
  color: #7f8c8d;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 30px;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }
}
</style>
