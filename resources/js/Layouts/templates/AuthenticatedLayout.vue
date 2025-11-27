<script setup>
import { ref, watch, onMounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const isDark = ref(false);
const showingNavigationDropdown = ref(false);


const props = defineProps({
    routeName: String,
})

function toggleTheme() {
    isDark.value = !isDark.value;
    updateTheme();
}

function updateTheme() {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        isDark.value = true;
    }
    updateTheme();
});


</script>




<template>
    <div class="min-h-screen bg-gray-200 dark:bg-neutral-800 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-neutral-900 border-r border-gray-200 dark:border-gray-600 flex flex-col">
            <div class="h-16 flex items-center justify-center border-b border-gray-200  dark:border-gray-600">
                <Link :href="route('dashboard')"
                    class="text-xl font-bold text-gray-800 dark:text-white flex items-center justify-center">
                <img src="/images/logo-retangular.png" alt="" class="w-[70%] dark:hidden">
                <img src="/images/logo-retangular-dark.png" alt="" class="w-[70%] hidden dark:block">

                </Link>
            </div>

            <nav class="mt-10 flex flex-col space-y-2 px-2">
                <NavLink icon="material-symbols:dashboard-outline"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    :href="route('dashboard')">
                    Dashboard
                </NavLink>
                <NavLink icon="material-symbols:account-box-outline"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    :href="route('accountvoip.index')">
                    Contas VoIP
                </NavLink>

                <NavLink icon="material-symbols:settings-outline"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    :href="route('trunkvoip.index')">
                    Troncos VoIP
                </NavLink>
                <NavLink icon="material-symbols:call-missed-outgoing-rounded"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    :href="route('outboundroutes.index')">
                    Rotas de saída
                </NavLink>
                <NavLink icon="material-symbols:list-alt-outline"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    :href="route('clid.index')">
                    Listas de Caller Id
                </NavLink>
                <NavLink icon="material-symbols:view-timeline-outline-rounded"
                    class="border-none rounded px-4 py-2 text-primary-brazuka dark:text-gray-200 dark:hover:bg-gray-800 hover:bg-primary-hover hover:text-white"
                    href="#">
                    CDR
                </NavLink>
            </nav>
        </aside>

        <!-- Conteúdo principal -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <nav
                class="h-16 bg-white dark:bg-neutral-900 border-b border-gray-100 dark:border-gray-600 flex items-center justify-between px-4">
                <div class="flex items-center space-x-8">

                </div>

                <!-- Dropdown usuário -->
                <div class="flex items-center gap-4">

                    <button @click="toggleTheme" aria-label="Toggle theme"
                        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <!-- ícone do sol -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05L5.636 5.636m12.728 0l-1.414 1.414M7.05 16.95l-1.414 1.414M12 7a5 5 0 000 10a5 5 0 000-10z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 dark:text-gray-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <!-- ícone da lua -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
                        </svg>
                    </button>


                    <!-- Dropdown usuário -->
                    <div class="flex items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-none px-3 py-2 text-sm font-medium text-gray-500 dark:text-white hover:text-gray-700 focus:outline-none">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </nav>
            <header v-if="$props.routeName">
                <div class="p-6">
                    <h2 class="text-lg font-semibold  text-three-brazuka dark:text-gray-100">
                        {{ $props.routeName }}
                    </h2>
                </div>
            </header>
            <!-- Conteúdo da página -->
            <main class="p-6">
                <slot />
                <Extension />

            </main>
        </div>
    </div>
</template>
