<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  tasks: Object,
  filters: Object,
});

function formatTime(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleTimeString(undefined, {
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return '—';
  }
}

function linkLabel(task) {
  const n = task.article_links?.length ?? 0;
  if (n > 1) {
    return `${n} links`;
  }
  return task.article_url;
}
</script>

<template>
  <Head title="My Daily Updates" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">My Daily Updates</h2>
        <Link
          :href="route('tasks.create')"
          class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600"
        >
          Add Daily Update
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
          <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Links</th>
                    <th class="px-4 py-3 whitespace-nowrap">In</th>
                    <th class="px-4 py-3 whitespace-nowrap">Out</th>
                    <th class="px-4 py-3 whitespace-nowrap">Break</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Auto</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white text-sm">
                  <tr
                    v-for="task in tasks.data"
                    :key="task.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap px-4 py-3">
                      {{ task.work_date }}
                    </td>
                    <td class="px-4 py-3">
                      {{ task.title }}
                    </td>
                    <td class="max-w-xs px-4 py-3">
                      <a
                        v-if="(task.article_links?.length ?? 0) <= 1"
                        :href="task.article_url"
                        target="_blank"
                        class="truncate text-indigo-600 hover:underline"
                      >
                        {{ linkLabel(task) }}
                      </a>
                      <span v-else class="text-gray-700">{{ linkLabel(task) }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                      {{ formatTime(task.clock_in_at) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                      {{ formatTime(task.clock_out_at) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">
                      <template v-if="task.break_start_at || task.break_end_at">
                        {{ formatTime(task.break_start_at) }} – {{ formatTime(task.break_end_at) }}
                      </template>
                      <span v-else>—</span>
                    </td>
                    <td class="px-4 py-3">
                      <span
                        :class="[
                          'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                          task.type === 'new'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-amber-50 text-amber-700',
                        ]"
                      >
                        {{ task.type }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                      {{ task.auto_detected_type }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
