<template>
  <main class="container">
    <section class="status-section" :class="statusClass">
      <div class="icon" aria-hidden="true">{{ statusIcon }}</div>
      <h1 class="status-title">{{ statusTitle }}</h1>
      <p class="status-message">{{ statusMessage }}</p>
    </section>
    
    <section v-if="!loading" class="actions-section">
      <button @click="goToPanel" class="btn btn-primary">Return to merchant</button>
    </section>

    <section v-if="loading" class="loading-section">
      <div class="spinner"></div>
      <p>Loading transaction data...</p>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const status = ref(null);
const loading = ref(true);
const returnUrl = ref('/');

const statusMap = {
  SUCCESS: {
    icon: '✅',
    title: 'Payment completed successfully',
    message: 'Thank you for your payment.',
    class: 'success',
  },
  PENDING: {
    icon: '⏳',
    title: 'Payment in progress',
    message: 'Your payment is currently being processed.',
    class: 'pending',
  },
  ERROR: {
    icon: '❌',
    title: 'Payment failed',
    message: 'There was a problem with your payment.',
    class: 'error',
  },
};

const uuid = route.query.transaction_uuid;

const statusIcon = computed(() => (statusMap[status.value]?.icon || '❌'));
const statusTitle = computed(() => (statusMap[status.value]?.title || 'Unknown status'));
const statusMessage = computed(() => (statusMap[status.value]?.message || 'An unexpected error occurred.'));
const statusClass = computed(() => statusMap[status.value]?.class || 'error');

function goToPanel() {
  window.location.href = returnUrl.value;
}

onMounted(async () => {
  if (!uuid) {
    status.value = 'ERROR';
    loading.value = false;
    return;
  }

  try {
    const res = await fetch(`/api/transactions/${uuid}/status`);
    if (!res.ok) throw new Error('Błąd sieci');
    const data = await res.json();
    status.value = data.status || 'ERROR';
    returnUrl.value = data.returnUrl || '/';
  } catch {
    status.value = 'ERROR';
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.container {
  max-width: 480px;
  margin: 5rem auto;
  padding: 2rem 2.5rem;
  background: #fff;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  text-align: center;
  color: #1f2937;
}

.status-section {
  padding: 2rem 1rem;
  border-radius: 10px;
  margin-bottom: 2.5rem;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.05);
  user-select: none;
}

.status-section.success {
  background-color: #dcfce7;
  color: #166534;
  border: 2px solid #22c55e;
}

.status-section.pending {
  background-color: #fef3c7;
  color: #92400e;
  border: 2px solid #facc15;
}

.status-section.error {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 2px solid #ef4444;
}

.status-section .icon {
  font-size: 5rem;
  margin-bottom: 0.8rem;
  user-select: none;
}

.status-title {
  font-size: 1.8rem;
  margin-bottom: 0.3rem;
  font-weight: 700;
}

.status-message {
  font-size: 1.1rem;
  font-weight: 500;
}

.details-section {
  text-align: left;
  margin-bottom: 2rem;
}

.details-section h2 {
  font-weight: 700;
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: #374151;
}

.details-list {
  list-style: none;
  padding: 0;
  margin: 0;
  color: #4b5563;
  font-weight: 600;
  font-size: 1rem;
}

.details-list li {
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.actions-section {
  display: flex;
  justify-content: center;
}

.btn {
  cursor: pointer;
  font-weight: 600;
  padding: 0.65rem 1.5rem;
  border-radius: 8px;
  border: none;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.loading-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #6b7280;
  font-weight: 500;
  gap: 12px;
}

.spinner {
  width: 42px;
  height: 42px;
  border: 5px solid #d1d5db;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
