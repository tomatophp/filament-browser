<x-filament::page>
    <x-filament::card>
        <div id="app">
            <browser :collection="{{ json_encode([
                    "folders" => $folders,
                    "files" => $files,
                    "back_path" => $back_path,
                    "back_name" => $back_name,
                    "current_path" => $current_path
                ]) }}" :url="'{{ url('admin/browser') }}'" inline-template v-cloak>
                <div>
                    <div class="flex justify-start mb-4 gap-2">
                        <button @click="goHome()"
                                class="inline-flex items-center justify-center font-medium tracking-tight transition rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4 text-white shadow focus:ring-white">
                            <x-heroicon-s-home class="w-6 h-6 p-1  item-center" />
                            {{ trans('filament-browser::messages.home') }}
                        </button>
                        <button @click="goBack()"
                                class="inline-flex items-center justify-center font-medium tracking-tight transition rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4 text-white shadow focus:ring-white">
                            <x-heroicon-s-backspace class="w-6 h-6 p-1  item-center" />
                            {{ trans('filament-browser::messages.back') }}
                        </button>
                    </div>
                    <div v-if="fileContent">
                        <codemirror ref="cmEditor" v-model="fileContent" width="100%" :options="cmOptions">
                        </codemirror>
                        <br>
                        <button @click="saveFile()"
                                class="inline-flex items-center justify-center font-medium tracking-tight transition rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4 text-white shadow focus:ring-white">
                            {{ trans('filament-browser::messages.save') }}
                        </button>
                    </div>
                    <div v-else-if="imagePath">
                        <img :src="imagePath" />
                    </div>
                    <div v-else>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg flex flex-col items-center p-4"
                                 v-for="(folder, key) in files.folders" @key="key">
                                <button @click="getFolder(folder)">
                                    <x-heroicon-s-folder class="w-20 h-20  item-center text-primary-500" />
                                    <button
                                        class="block font-medium mt-4 text-center">@{{ folder.name.substring(0,20) }}</button>
                                </button>
                            </div>
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg flex flex-col items-center p-4"
                                 v-for="(file, key) in files.files" @key="key">
                                <button @click="getFile(file)">
                                    <x-heroicon-s-photo
                                        v-if="file.ex === 'png' || file.ex === 'jpg' || file.ex === 'jpeg' || file.ex === 'svg' || file.ex === 'webp'"
                                        class="w-20 h-20  item-center text-primary-500" />
                                    <x-heroicon-o-document-text v-else class="w-20 h-20  item-center" />
                                    <button
                                        class="block font-medium mt-4 text-center">@{{ file.name.substring(0,20) }}</button>
                                </button>
                            </div>

                        </div>
                        <div v-if="!files.folders.length && !files.files.length">
                            <div class="py-4 px-4">
                                <x-heroicon-s-magnifying-glass class="w-20 h-20 my-2 mx-auto item-center text-primary-500" />
                                <h1 class="text-center text-3xl font-bold">
                                    {{ trans('filament-browser::messages.empty') }}
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </browser>
        </div>
    </x-filament::card>
</x-filament::page>


@push('scripts')
<script src="{{ asset('js/filament-browser.js') }}"></script>
@endpush
