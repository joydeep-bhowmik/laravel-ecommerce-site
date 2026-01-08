@persist('toast')
    <div class="p-3 fixed bottom-2 lg:right-2  md:ml-auto w-full">
        <div x-data="{
        
            title: null,
            subtitle: null,
            variant: 'default',
            show: false,
            timeout: null,
        
            reset() {
                this.show = false;
                setTimeout(() => {
                    this.title = null;
                    this.subtitle = null;
                    this.variant = 'default';
                }, 100)
        
                clearTimeout(this.timeout);
            },
            close() {
                this.reset();
        
            },
            handleToast($event) {
        
                this.reset();
        
                setTimeout(() => {
                    const { title, subtitle, variant } = $event.detail;
        
                    this.title = title;
        
                    this.subtitle = subtitle;
        
                    this.variant = variant ?? 'default';
        
                    this.show = true;
        
                    this.timeout = setTimeout(() => {
                        this.reset()
                    }, 2000)
                }, 100)
        
            }
        
        
        }" x-show="show" x-transition style="display: none"
            class="bg-white shadow-md  border rounded-lg p-3 w-full md:max-w-xs text-sm grid  gap-3 lg:ml-auto"
            x-on:toast.window="handleToast"
            :class="variant != 'default' ? `grid-cols-[20px_auto_30px]` : `grid-cols-[auto_30px]`">
            <div x-show="variant!='default'" style="display: none" class="">

                <svg x-show="variant=='info'" style="display: none" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <svg x-show="variant=='warning'" style="display: none" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-yellow-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <svg x-show="variant=='success'" style="display: none" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <svg x-show="variant=='error'" style="display: none" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                </svg>

            </div>


            <div class="">

                <h2 x-text="title" class="font-medium capitalize"></h2>

                <div x-text="subtitle" class="mt-3 text-xs"></div>
            </div>

            <div class="ml-auto flex ">
                <button type="button" class="ml-auto " @click="close">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>

                </button>
            </div>

        </div>

    </div>
@endpersist
