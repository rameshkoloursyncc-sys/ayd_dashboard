<div id="edit-account-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm bg-black/10 flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 px-6 py-6 lg:px-8">
            <button type="button" class="absolute top-4 right-4 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" style="margin-top: 4px; margin-right: 4px;" onclick="closeEditAccountModal()">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div>
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Edit Bank Account</h3>
                <form id="edit-account-form" class="space-y-6" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="doctorId" id="edit-doctorId">
                    <div class="mb-4">
                        <label for="edit-accountno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Account Number</label>
                        <input type="text" name="accountno" id="edit-accountno" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-ifsc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">IFSC Code</label>
                        <input type="text" name="ifsc" id="edit-ifsc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-pan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PAN Number</label>
                        <input type="text" name="pan" id="edit-pan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-addaar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Aadhaar Number</label>
                        <input type="text" name="addaar" id="edit-addaar" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-source" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Source</label>
                        <input type="text" name="source" id="edit-source" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" readonly>
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>