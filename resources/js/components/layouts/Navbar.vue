<template>
  <header class="bg-white shadow-lg py-3 mx-5 my-3 rounded-4xl mb-4" dir="rtl">
    <div class="container d-flex justify-content-between align-items-center position-relative">

      <!-- اللوجو -->
      <div class="d-flex align-items-center">
        <img src="/images/logo3.png" alt="Logo" class="rounded-circle" style="width:80px; height:40px; object-fit:cover;">
      </div>

      <!-- الروابط -->
      <nav class="d-none d-md-flex flex-grow-1 justify-content-center">
        <ul class="nav">
          <li v-for="link in links" :key="link.href" class="nav-item">
            <a
              :href="link.href"
              class="nav-link px-3 nav-hover"
              :class="isActive(link.active) ? 'fw-bold text-success' : 'text-dark'"
            >
              {{ link.label }}
            </a>
          </li>
        </ul>
      </nav>

      <div class="d-flex gap-2 position-relative">
        <template v-if="isLoggedIn">
                <button
        class="btn btn-success fw-bold d-flex align-items-center gap-2"
        @click="toggleDropdown"
        >
        {{ userName }}
        <i
            class="bi"
            :class="dropdownOpen ? 'bi-chevron-up' : 'bi-chevron-down'"
        ></i>
        </button>


                    <div
            v-if="dropdownOpen"
            class="dropdown-menu show shadow rounded mt-2"
            style="position: absolute; right: 0; top: 100%; margin-top: 8px;"
            >

            <a class="dropdown-item" href="/profile">الملف الشخصي</a>
            <hr class="dropdown-divider">
            <button class="dropdown-item text-danger" @click="logout">تسجيل الخروج</button>
          </div>
        </template>

        <template v-else>
          <a href="/login" class="btn btn-success fw-bold auth-btn">
            تسجيل الدخول
          </a>
        </template>
      </div>

    </div>
  </header>
</template>

<script>
export default {
  name: 'Navbar',

data() {
  return {
    isLoggedIn: false,
    dropdownOpen: false,
    userName: '',
    links: [
      { label: 'الرئيسية', href: '/', active: 'home' },
      { label: 'من نحن', href: '/who', active: 'who' },
      { label: 'اتصل بنا', href: '/contact', active: 'contact' },
    ],
  }
},
mounted() {
  const token = localStorage.getItem('auth_token');
  this.isLoggedIn = !!token;

  if (this.isLoggedIn) {
    fetch('/api/v1/users/profile', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      }
    })
      .then(res => {
        if (!res.ok) throw new Error('Unauthorized');
        return res.json();
      })
      .then(data => {
        console.log('API response:', data);
        this.userName = data.data.user.name || 'المستخدم';
      })
      .catch(err => {
        console.error('API error:', err);
        this.userName = 'المستخدم';
      });
  }

  document.addEventListener('click', this.handleClickOutside);
},


  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },

  methods: {
    isActive(routeName) {
      return document.body.dataset.route === routeName;
    },

    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },

    handleClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.dropdownOpen = false;
      }
    },

    async logout() {
      const token = localStorage.getItem('auth_token');
      try {
        await fetch('/api/v1/users/logout', {
          method: 'POST',
          headers: {
            Authorization: 'Bearer ' + token,
            Accept: 'application/json',
          },
        });
      } catch (e) {
        console.error(e);
      }
      localStorage.removeItem('auth_token');
      window.location.href = '/';
    },
  }
}
</script>

<style>
.auth-btn {
  transition: all 0.2s ease-in-out;
}

.auth-btn:hover {
  transform: scale(1.05);
  background-color: #198754;
}

/* Hover line effect للروابط */
.nav-hover {
  position: relative;
  overflow: hidden;
}

.nav-hover::after {
  content: '';
  position: absolute;
  bottom: 0;
  right: 0;
  width: 0;
  height: 2px;
  background-color: #28a745; /* الأخضر */
  transition: width 1s ease;
}

.nav-hover:hover::after {
  width: 100%;
  left: 0;
  right: auto;
}

/* Dropdown صغير */
.dropdown-menu.show {
  display: block;
}
</style>
