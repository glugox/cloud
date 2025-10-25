<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
const props = defineProps({
  posts: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <AppLayout title="Glugox Blog">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Glugox Blog</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <p class="mb-4 text-gray-600">
            Modules discovered by the Glugox orchestrator can expose their own routes and UI. This page is rendered from the Blog module.
          </p>

          <ul class="space-y-4">
            <li v-for="post in props.posts" :key="post.title" class="border rounded-lg p-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ post.title }}</h3>
              <p class="text-gray-600">{{ post.excerpt }}</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
