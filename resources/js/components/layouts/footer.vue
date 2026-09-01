<template>
  <footer class="bg-success text-white py-4">
    <div class="container">
      <div class="row">

        <!-- Quick Links -->
        <div class="col-md-4 mb-4 mb-md-0">
          <h5 class="fw-bold mb-3">Quick Links</h5>
          <ul class="list-unstyled">
            <li v-for="link in quickLinks" :key="link.label">
              <a :href="link.href" class="text-white text-decoration-none d-block mb-2">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Support & Contact -->
        <div class="col-md-4 mb-4 mb-md-0">
          <h5 class="fw-bold mb-3">Support & Contact</h5>
          <ul class="list-unstyled">
            <li v-for="link in supportLinks" :key="link.label">
              <a :href="link.href" class="text-white text-decoration-none d-block mb-2">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Logo & Socials -->
        <div class="col-md-4 text-center text-md-end">
          <!-- Logo -->
          <div class="mb-3">
            <img src="/images/logo3.png" alt="Logo" class="rounded-circle bg-white" style="width: 100px; height: 100px; object-fit: cover;">
          </div>

          <!-- Social Icons -->
          <div class="mb-3">
            <a
              v-for="social in socials"
              :key="social.name"
              :href="social.href"
              class="btn btn-outline-light btn-sm rounded-circle me-2"
              v-html="social.icon"
              style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"
            ></a>
          </div>

          <!-- Store Badges -->
          <div class="mb-3 d-flex justify-content-center justify-content-md-end gap-2">
            <a href="#" target="_blank">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/Google_Play_Store_badge_EN.svg/512px-Google_Play_Store_badge_EN.svg.png"
                alt="Google Play" style="width: 120px;">
            </a>
            <a href="#" target="_blank">
              <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg"
                alt="App Store" style="width: 100px;">
            </a>
          </div>

          <!-- Copyright -->
          <p class="small opacity-75 mb-0">
            © 2025 مؤسسة عطاء جميع الحقوق محفوظة
          </p>
        </div>

      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer',

  data() {
    return {
      quickLinks: [
        { label: 'Home', href: '/' },
        // { label: 'All Courses', href: '#' },
        // { label: 'Certificates', href: '#' },
        { label: 'Services', href: '#' },
        // { label: 'Specialists', href: '#' },
      ],

      supportLinks: [
        { label: 'Who Are We?', href: '#' },
        { label: 'Contact Us', href: '#' },
        { label: 'Copyright', href: '#' },
        { label: 'Terms of Services', href: '#' },
        { label: 'Privacy Policy', href: '#' },
        { label: 'Help Center', href: '#' },
      ],

      socials: [
        {
          name: 'facebook',
          href: '#',
          icon: `<svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12z"/></svg>`
        },
        {
          name: 'x',
          href: '#',
          icon: `<svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.92 21.75H1.61l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`
        },
        {
          name: 'instagram',
          href: '#',
          icon: `<svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>`
        },
      ],
    }
  },
}
</script>
