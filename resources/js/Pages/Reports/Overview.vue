<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  from: String,
  to: String,
  summary: Array,
});

const updateRange = (event) => {
  event.preventDefault();
  const form = new FormData(event.target);
  router.get(
    route('reports.tasks'),
    { from: form.get('from'), to: form.get('to') },
    { preserveState: true, replace: true },
  );
};
</script>

<template>
  <Head title="Task Reports" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Task Reports
        </h2>
        <Link
          :href="route('tasks.index')"
          class="text-sm font-medium text-gray-500 hover:text-gray-700"
        >
          Go to My Tasks
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <form
          class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm"
          @submit="updateRange"
        >
          <div class="grid gap-4 border-b border-gray-100 bg-gray-50 px-4 py-4 sm:grid-cols-3 sm:px-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                From
              </label>
              <input
                name="from"
                type="date"
                :value="from"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                To
              </label>
              <input
                name="to"
                type="date"
                :value="to"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
            </div>
            <div class="flex items-end justify-start">
              <button
                type="submit"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
              >
                Update
              </button>
            </div>
          </div>
        </form>

        <div class="grid gap-6 lg:grid-cols-3">
          <div class="space-y-3 rounded-lg border border-gray-100 bg-white p-5 shadow-sm lg:col-span-1">
            <h3 class="text-sm font-semibold text-gray-800">
              Totals (all users)
            </h3>
            <p class="text-xs text-gray-500">
              From {{ from }} to {{ to }}
            </p>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-md bg-emerald-50 p-3">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                  New articles
                </p>
                <p class="mt-1 text-2xl font-semibold text-emerald-800">
                  {{
                    summary.reduce((acc, row) => acc + row.new, 0)
                  }}
                </p>
              </div>
              <div class="rounded-md bg-amber-50 p-3">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">
                  Refurbished
                </p>
                <p class="mt-1 text-2xl font-semibold text-amber-800">
                  {{
                    summary.reduce((acc, row) => acc + row.refurbished, 0)
                  }}
                </p>
              </div>
            </div>
          </div>

          <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm">
              <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                <h3 class="text-sm font-semibold text-gray-800">
                  Per-user breakdown
                </h3>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                      <th class="px-4 py-3">User</th>
                      <th class="px-4 py-3 text-right">New</th>
                      <th class="px-4 py-3 text-right">Refurbished</th>
                      <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 bg-white">
                    <tr
                      v-for="row in summary"
                      :key="row.user.id"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">
                          {{ row.user.name }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ row.user.email }}
                        </div>
                      </td>
                      <td class="px-4 py-3 text-right text-emerald-700">
                        {{ row.new }}
                      </td>
                      <td class="px-4 py-3 text-right text-amber-700">
                        {{ row.refurbished }}
                      </td>
                      <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ row.new + row.refurbished }}
                      </td>
                    </tr>
                    <tr v-if="summary.length === 0">
                      <td
                        colspan="4"
                        class="px-4 py-6 text-center text-sm text-gray-500"
                      >
                        No tasks found in this period yet.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

