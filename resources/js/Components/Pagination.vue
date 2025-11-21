<template>
    <div v-if="links.length > 3" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
        <!-- Mobile pagination -->
        <div class="flex justify-between flex-1 sm:hidden">
            <button
                :disabled="!previousPageUrl"
                @click="goToPage(previousPageUrl)"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': !previousPageUrl }"
            >
                Previous
            </button>
            <span class="px-4 py-2 text-sm text-gray-700">
                Page {{ currentPage }} of {{ lastPage }}
            </span>
            <button
                :disabled="!nextPageUrl"
                @click="goToPage(nextPageUrl)"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                :class="{ 'opacity-50 cursor-not-allowed': !nextPageUrl }"
            >
                Next
            </button>
        </div>

        <!-- Desktop pagination -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ from }}</span>
                    to
                    <span class="font-medium">{{ to }}</span>
                    of
                    <span class="font-medium">{{ total }}</span>
                    results
                </p>
            </div>
            <div>
                <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <!-- Previous Button -->
                    <button
                        :disabled="!previousPageUrl"
                        @click="goToPage(previousPageUrl)"
                        class="relative inline-flex items-center px-2 py-2 text-gray-400 rounded-l-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                        :class="{ 'opacity-50 cursor-not-allowed': !previousPageUrl }"
                    >
                        <span class="sr-only">Previous</span>
                        &laquo;
                    </button>

                    <!-- Page Numbers -->
                    <button
                        v-for="(link, index) in pageLinks"
                        :key="index"
                        :disabled="!link.url"
                        @click="goToPage(link.url)"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0"
                        :class="{
                            'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
                            'text-gray-900 hover:bg-gray-50': !link.active && link.url,
                            'text-gray-400 cursor-not-allowed': !link.url
                        }"
                        v-html="link.label"
                    ></button>

                    <!-- Next Button -->
                    <button
                        :disabled="!nextPageUrl"
                        @click="goToPage(nextPageUrl)"
                        class="relative inline-flex items-center px-2 py-2 text-gray-400 rounded-r-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                        :class="{ 'opacity-50 cursor-not-allowed': !nextPageUrl }"
                    >
                        <span class="sr-only">Next</span>
                        &raquo;
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    links: Array
})

const previousPageUrl = computed(() => props.links[0]?.url || null)
const nextPageUrl = computed(() => props.links[props.links.length - 1]?.url || null)

const currentPage = computed(() => {
    const activeLink = props.links.find(link => link.active)
    return activeLink ? parseInt(activeLink.label) : 1
})

const lastPage = computed(() => {
    if (props.links.length < 3) return 1
    const lastPageLink = props.links[props.links.length - 2]
    return lastPageLink ? parseInt(lastPageLink.label) : 1
})

const from = computed(() => {
    return props.links[0]?.url ? ((currentPage.value - 1) * 10) + 1 : 1
})

const to = computed(() => {
    return Math.min(currentPage.value * 10, props.links[0]?.total || 0)
})

const total = computed(() => {
    return props.links[0]?.total || 0
})

const pageLinks = computed(() => {
    // Return only the page number links (excluding Previous and Next)
    return props.links.slice(1, -1)
})

const goToPage = (url) => {
    if (url) {
        router.visit(url)
    }
}
</script>