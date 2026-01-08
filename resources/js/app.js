import './bootstrap';
//..
import Panzoom from '@panzoom/panzoom';

window.Panzoom = Panzoom;

import { livewire_hot_reload } from 'virtual:livewire-hot-reload'

livewire_hot_reload();



document.addEventListener('livewire:init', () => {
    Livewire.directive('x-confirm', ({
        el,
        directive,
        component,
        cleanup
    }) => {
        let content = directive.expression;
        let confirmationInProgress = false;
        let onClick = e => {
            if (confirmationInProgress) {
                return;
            }
            e.preventDefault();
            e.stopImmediatePropagation();
            confirmationInProgress = true;
            // alert('x')
            const modalHtml = `
        <div class="fixed z-20 inset-0 overflow-y-auto">
            <!-- Modal content -->
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 sm:align-middle max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <!-- You can add an icon here if needed -->
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                    Confirmation
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ${content}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button id="confirmButton" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm
                        </button>
                        <button id="cancelButton" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            el.insertAdjacentHTML('afterend', modalHtml);

            const confirmButton = document.getElementById('confirmButton');
            const cancelButton = document.getElementById('cancelButton');

            confirmButton.addEventListener('click', () => {

                el.click(); // Trigger the original click action
                closeModal();
            });

            cancelButton.addEventListener('click', () => {
                closeModal();
            });

            const closeModal = () => {
                const modal = document.querySelector('.fixed');
                modal.parentNode.removeChild(modal);
                confirmationInProgress = false;
            };
        };

        el.addEventListener('click', onClick, {
            capture: true
        });

        cleanup(() => {
            el.removeEventListener('click', onClick);
        });
    });

})