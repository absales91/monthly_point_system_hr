<x-app-layout>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                Create Product Category
            </h2>
            <p class="text-sm text-gray-500">
                Add a new category to organize your products
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-xl p-6">

            <form method="POST" action="{{ url('/admin/categories') }}">
                @csrf

                <!-- Category Name -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="e.g. Outdoor LED"
                        class="w-full rounded-lg border-gray-300
                               focus:border-red-600 focus:ring-red-600"
                    >
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Optional description"
                        class="w-full rounded-lg border-gray-300
                               focus:border-red-600 focus:ring-red-600"
                    ></textarea>
                </div>

                <!-- Active Toggle -->
                <div class="mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="text-sm font-medium text-gray-700">
                            Active Status
                        </span>

                        <div class="relative">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                                class="sr-only peer"
                            >
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full
                                       peer peer-checked:bg-red-700
                                       transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-4 h-4 bg-white
                                       rounded-full transition-transform
                                       peer-checked:translate-x-5">
                            </div>
                        </div>

                        <span class="text-sm text-gray-500">
                            Enable / Disable category
                        </span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ url('/admin/categories') }}"
                       class="px-4 py-2 text-sm rounded-lg
                              border border-gray-300 text-gray-700
                              hover:bg-gray-100 transition">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="px-6 py-2 text-sm font-medium text-white
                               bg-red-700 rounded-lg
                               hover:bg-red-800 transition">
                        Save Category
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
