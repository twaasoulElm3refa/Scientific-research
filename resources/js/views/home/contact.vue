<template>
  <section class="py-5">
    <div class="container">
      <!-- Title -->
      <div class="text-center mb-5">
        <h2 class="fw-bold text-success mb-3">Contact Us</h2>
        <p class="text-muted">
          We are here to answer all your questions and help you with anything you need
        </p>
      </div>

      <!-- Card -->
      <div class="card shadow-lg border-0 overflow-hidden">
        <div class="row g-0">

          <!-- Contact Info -->
          <div class="col-md-6 bg-success text-white p-4 order-md-2">
            <h4 class="fw-bold mb-4">Contact Information</h4>

            <div class="mb-3 d-flex">
              <i class="bi bi-geo-alt-fill me-3 fs-5"></i>
              <div>
                <strong>Address</strong>
                <p class="mb-0 opacity-75">Nile Street, Cairo, Egypt</p>
              </div>
            </div>

            <div class="mb-3 d-flex">
              <i class="bi bi-telephone-fill me-3 fs-5"></i>
              <div>
                <strong>Phone</strong>
                <p class="mb-0 opacity-75">+20 123 456 789</p>
              </div>
            </div>

            <div class="mb-4 d-flex">
              <i class="bi bi-envelope-fill me-3 fs-5"></i>
              <div>
                <strong>Email</strong>
                <p class="mb-0 opacity-75">info@example.com</p>
              </div>
            </div>

            <div>
              <strong class="d-block mb-2">Follow us</strong>
              <div class="d-flex gap-2">
                <a href="#" class="btn btn-light btn-sm rounded-circle">
                  <i class="bi bi-facebook text-success"></i>
                </a>
                <a href="#" class="btn btn-light btn-sm rounded-circle">
                  <i class="bi bi-twitter text-success"></i>
                </a>
                <a href="#" class="btn btn-light btn-sm rounded-circle">
                  <i class="bi bi-instagram text-success"></i>
                </a>
                <a href="#" class="btn btn-light btn-sm rounded-circle">
                  <i class="bi bi-telegram text-success"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="col-md-6 p-4 order-md-1">
            <h4 class="fw-bold mb-4">Send us a message</h4>

            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>

              <div class="mb-3">
                <label class="form-label">Subject</label>
                <input v-model="form.subject" type="text" class="form-control" required />
              </div>

              <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea v-model="form.message" rows="4" class="form-control" required></textarea>
              </div>

              <button type="submit" class="btn btn-success w-100">
                Send Message
              </button>
            </form>

            <div v-if="successMessage" class="alert alert-success mt-4">
              {{ successMessage }}
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref } from "vue";

export default {
  name: "Contact",

  setup() {
    const form = ref({
      name: "",
      email: "",
      subject: "",
      message: "",
    });

    const successMessage = ref("");

    const submitForm = () => {
      // هنا تقدر تبعت API request
      console.log(form.value);

      successMessage.value = "Your message has been sent successfully!";
      form.value = { name: "", email: "", subject: "", message: "" };
    };

    return {
      form,
      submitForm,
      successMessage,
    };
  },
};
</script>
