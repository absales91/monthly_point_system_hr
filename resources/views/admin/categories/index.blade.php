<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">
                    Product Categories
                </h2>
                <p class="text-sm text-gray-500">
                    Manage product categories used in quotations
                </p>
            </div>

            <a href="{{ route('admin.categories.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2
                      bg-red-700 text-white text-sm font-medium
                      rounded-lg hover:bg-red-800 transition">
                + Add Category
            </a>
        </div>

        <!-- Table Card -->
        <div class="bg-white shadow rounded-xl overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">
                            Category Name
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Description
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $cat->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $cat->description ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-10 text-center text-gray-500">
                                No categories found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</x-app-layout>
