<template>
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-badge">RCA</div>
                    <div>
                        <h1 class="brand-title">Comparator RCA</h1>
                        <p class="brand-subtitle">Ofertare rapidă pentru persoane fizice și juridice</p>
                    </div>
                </div>

                <div class="nav">
                    <RouterLink to="/" class="nav-link">Ofertare</RouterLink>
                    <RouterLink to="/history" class="nav-link">Istoric</RouterLink>

                    <button class="theme-toggle" @click="toggleTheme">
                        {{ isDark ? 'Light' : 'Dark' }}
                    </button>
                </div>
            </div>
        </header>

        <main class="page">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'

const isDark = ref(false)

onMounted(() => {
    isDark.value = localStorage.getItem('theme') === 'dark'
    document.body.classList.toggle('dark', isDark.value)
    document.documentElement.classList.toggle('dark', isDark.value)
})

watch(isDark, (value) => {
    document.body.classList.toggle('dark', value)
    document.documentElement.classList.toggle('dark', value)
    localStorage.setItem('theme', value ? 'dark' : 'light')
})

function toggleTheme() {
    isDark.value = !isDark.value
}
</script>
