<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow-lg mt-6">
    <h1 class="text-2xl font-bold mb-4">ملفي الشخصي</h1>

    <!-- Navigation Tabs -->
    <div class="flex border-b mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="currentTab = tab.id"
        :class="[
          'py-2 px-4 font-medium',
          currentTab === tab.id
            ? 'border-b-2 border-emerald-500 text-emerald-600'
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab Content -->
    <div>
    <!-- Tab 1: User Info -->
    <div v-if="currentTab === 1">
    <p><strong>Name:</strong> {{ profile.user.name }}</p>
    <p><strong>Email:</strong> {{ profile.user.email }}</p>
    <p><strong>Role:</strong> {{ profile.user.role }}</p>
    <p><strong>Active:</strong> {{ profile.user.is_active ? 'Yes' : 'No' }}</p>
    <p><strong>Last Login:</strong> {{ profile.user.last_login_at }}</p>
    </div>

      <!-- Tab 2: Edit Info -->
      <div v-if="currentTab === 2" class="space-y-4">
        <div>
          <label class="block font-medium mb-1">الاسم</label>
          <input
            type="text"
            v-model="editData.name"
            class="w-full border rounded px-3 py-2"
          />
        </div>
        <div>
          <label class="block font-medium mb-1">البريد الإلكتروني</label>
          <input
            type="email"
            v-model="editData.email"
            class="w-full border rounded px-3 py-2"
          />
        </div>
        <button
          @click="updateProfile"
          class="bg-emerald-500 text-white px-4 py-2 rounded hover:bg-emerald-600"
        >
          حفظ التغييرات
        </button>
      </div>

      <!-- Tab 3: Update Password -->
      <div v-if="currentTab === 3" class="space-y-4">
       <div>
  <label class="block font-medium mb-1">Current Password</label>
  <div class="relative">
    <input
      :type="showCurrentPassword ? 'text' : 'password'"
      v-model="passwordData.current_password"
      class="w-full border rounded px-3 py-2 pr-10"
    />
    <button
      type="button"
      @click="showCurrentPassword = !showCurrentPassword"
      class="absolute inset-y-0 right-3 flex items-center text-gray-500"
    >
      👁️
    </button>
  </div>
</div>

       <div>
  <label class="block font-medium mb-1">New Password</label>
  <div class="relative">
    <input
      :type="showNewPassword ? 'text' : 'password'"
      v-model="passwordData.new_password"
      class="w-full border rounded px-3 py-2 pr-10"
    />
    <button
      type="button"
      @click="showNewPassword = !showNewPassword"
      class="absolute inset-y-0 right-3 flex items-center text-gray-500"
    >
      👁️
    </button>
  </div>
</div>

       <div>
  <label class="block font-medium mb-1">Confirm Password</label>
  <div class="relative">
    <input
      :type="showConfirmPassword ? 'text' : 'password'"
      v-model="passwordData.confirm_password"
      class="w-full border rounded px-3 py-2 pr-10"
    />
    <button
      type="button"
      @click="showConfirmPassword = !showConfirmPassword"
      class="absolute inset-y-0 right-3 flex items-center text-gray-500"
    >
      👁️
    </button>
  </div>
</div>

        <button
          @click="updatePassword"
          class="bg-emerald-500 text-white px-4 py-2 rounded hover:bg-emerald-600"
        >
          تحديث كلمة المرور
        </button>
      </div>
    </div>

    <!-- Status Message -->
    <div v-if="statusMessage" class="mt-4 p-2 rounded bg-green-100 text-green-700">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "UserProfile",
  setup() {
    const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

    const profile = ref({
      user: {
        id: "",
        name: "",
        email: "",
        role: "",
        is_active: false,
        memory_enabled: false,
        last_login_at: "",
      },
    });

    const tabs = [
      { id: 1, label: "معلومات المستخدم" },
      { id: 2, label: "تعديل البيانات" },
      { id: 3, label: "تحديث كلمة المرور" },
    ];

    const currentTab = ref(1);
    const statusMessage = ref("");

    const editData = ref({
      name: "",
      email: "",
    });

    const passwordData = ref({
      current_password: "",
      new_password: "",
      confirm_password: "",
    });

    // Fetch user profile
 const fetchProfile = async () => {
  try {
    const token = localStorage.getItem("auth_token");

    const res = await fetch("/api/v1/users/profile", {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    });

    if (!res.ok) {
      throw new Error("Unauthorized");
    }

    const data = await res.json();

    profile.value = data.data;
    editData.value.name = data.data.user.name;
    editData.value.email = data.data.user.email;

  } catch (error) {
    console.error(error);
    statusMessage.value = "غير مصرح لك";
  }
};
const updateProfile = async () => {
  try {
       const token = localStorage.getItem("auth_token"); //
    const res = await fetch("/api/v1/users/profile", {
      method: "put",
      headers: {
        Authorization: `Bearer ${token}`,

        "Content-Type": "application/json",
        "Accept": "application/json",
      },
      credentials: "include", // <--- مهم جدا
      body: JSON.stringify(editData.value),
    });

    if (!res.ok) {
      const text = await res.text();
      console.error("Server returned error:", text);
      statusMessage.value = "حدث خطأ أثناء تحديث البيانات";
      return;
    }

    const data = await res.json();
    statusMessage.value = data.message || "تم التحديث بنجاح";
    fetchProfile();
  } catch (error) {
    console.error("Fetch error:", error);
    statusMessage.value = "حدث خطأ أثناء التحديث";
  }
};

const updatePassword = async () => {
       const token = localStorage.getItem("auth_token"); //
  try {
    if (passwordData.value.new_password !== passwordData.value.confirm_password) {
      statusMessage.value = "كلمة المرور الجديدة وتأكيدها غير متطابقين";
      return;
    }

    const res = await fetch("/api/v1/users/password", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        "Accept": "application/json",
      },
      credentials: "include", // <--- مهم جدا
      body: JSON.stringify(passwordData.value),
    });

    if (!res.ok) {
      const text = await res.text();
      console.error("Server returned error:", text);
      statusMessage.value = "حدث خطأ أثناء تحديث كلمة المرور";
      return;
    }

    const data = await res.json();
    statusMessage.value = data.message || "تم تحديث كلمة المرور بنجاح";
    passwordData.value.current_password = "";
    passwordData.value.new_password = "";
    passwordData.value.confirm_password = "";
  } catch (error) {
    console.error("Fetch error:", error);
    statusMessage.value = "حدث خطأ أثناء تحديث كلمة المرور";
  }
};


    onMounted(fetchProfile);

  return {
  profile,
  tabs,
  currentTab,
  editData,
  passwordData,
  statusMessage,
  updateProfile,
  updatePassword,

  // 👁️ لازم دول
  showCurrentPassword,
  showNewPassword,
  showConfirmPassword,
};

  },
};
</script>

<style scoped>
/* Optional custom scroll for better UX */
</style>
