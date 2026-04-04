<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  users: Array,
  roles: Array,
});

const createForm = useForm({
  name: '',
  email: '',
  password: '',
  role: 'employee',
});

const updateRole = (user, role) => {
  router.patch(route('admin.users.update', user.id), { role }, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Users & Permissions" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Users & Permissions
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-800">
            Add new user
          </h3>
          <form
            class="mt-4 grid gap-4 md:grid-cols-4"
            @submit.prevent="createForm.post(route('admin.users.store'))"
          >
            <div class="md:col-span-1">
              <label class="block text-xs font-medium text-gray-700">Name</label>
              <input
                v-model="createForm.name"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">
                {{ createForm.errors.name }}
              </p>
            </div>
            <div class="md:col-span-1">
              <label class="block text-xs font-medium text-gray-700">Email</label>
              <input
                v-model="createForm.email"
                type="email"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="createForm.errors.email" class="mt-1 text-xs text-red-600">
                {{ createForm.errors.email }}
              </p>
            </div>
            <div class="md:col-span-1">
              <label class="block text-xs font-medium text-gray-700">Password</label>
              <input
                v-model="createForm.password"
                type="password"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="createForm.errors.password" class="mt-1 text-xs text-red-600">
                {{ createForm.errors.password }}
              </p>
            </div>
            <div class="md:col-span-1">
              <label class="block text-xs font-medium text-gray-700">Role</label>
              <select
                v-model="createForm.role"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option
                  v-for="role in roles"
                  :key="role"
                  :value="role"
                >
                  {{ role }}
                </option>
              </select>
              <button
                type="submit"
                :disabled="createForm.processing"
                class="mt-3 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
              >
                Create user
              </button>
            </div>
          </form>
        </div>

        <div class="rounded-lg border border-gray-100 bg-white shadow-sm">
          <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
            <h3 class="text-sm font-semibold text-gray-800">Existing users</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Email</th>
                  <th class="px-4 py-3">Role</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="user in users"
                  :key="user.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-3">
                    <div class="font-medium text-gray-900">
                      {{ user.name }}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-gray-600">
                    {{ user.email }}
                  </td>
                  <td class="px-4 py-3">
                    <select
                      :value="user.role"
                      @change="updateRole(user, $event.target.value)"
                      class="block rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                      <option
                        v-for="role in roles"
                        :key="role"
                        :value="role"
                      >
                        {{ role }}
                      </option>
                    </select>
                  </td>
                </tr>
                <tr v-if="!users.length">
                  <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                    No users yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

